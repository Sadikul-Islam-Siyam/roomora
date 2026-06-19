# Roomora — Production Readiness Audit

**Stack confirmed:** Laravel 12 / PHP 8.2 / MySQL (not raw PHP+MySQL — good news, this means Eloquent already protects you from most raw SQL injection risk).

Overall: the codebase is well above average — proper use of policies, `lockForUpdate()` for booking concurrency, consistent CSRF headers on every AJAX call, consistent `{{ }}` escaping of user input, decent indexing, and DB-level constraints/triggers. The issues below are real but mostly fixable in a focused session, not a rewrite.

Findings are grouped by severity. Each has a fix-it prompt you can paste into your coding tool (Claude Code, Cursor, etc.) against this exact repo.

---

## 🔴 CRITICAL — fix before any production deploy

### 1. Broken route: adding a room to a hotel crashes
`Admin\RoomController::store()` redirects to `admin.hotels.show`, but `Admin\HotelController` has **no `show()` method**, and no `admin/hotels/show.blade.php` view exists. The admin "add room" workflow currently errors out right after a successful save.

**Prompt:**
> In `app/Http/Controllers/Admin/HotelController.php`, add a `show(Hotel $hotel)` method that loads the hotel with its `rooms` (with booking counts) and recent bookings, and returns a new `admin.hotels.show` view. Create `resources/views/admin/hotels/show.blade.php` following the styling conventions of `resources/views/admin/hotels/edit.blade.php`, showing hotel details, a rooms table with edit/delete actions and a link to add a new room, and basic stats (total bookings, active rooms). Make sure `Route::resource('hotels', AdminHotelController::class)` in `routes/web.php` now resolves correctly for the `show` route.

---

### 2. Default admin credentials in the seeder
`DatabaseSeeder` creates `admin@roomora.com` / `password` unconditionally. If this seeder is ever run against staging/production, or the seeder file is exposed in a public repo, this is a free admin login for anyone.

**Prompt:**
> In `database/seeders/DatabaseSeeder.php`, change the admin user creation so that: (1) it never runs if `app()->environment('production')`, throwing a clear exception instead; (2) in non-production environments, the admin password is read from an `ADMIN_SEED_PASSWORD` env variable with a fallback to a random `Str::random(16)` value that gets printed to the console via `$this->command->warn()` so it's never silently `password`. Update `.env.example` to document the new `ADMIN_SEED_PASSWORD` variable.

---

### 3. Pending bookings never expire, permanently blocking inventory
`Room::isAvailableForDates()` treats `pending` bookings as occupying inventory indefinitely. There is no scheduled job to cancel unpaid `pending` bookings, so a user who starts checkout and never pays can block a room for those dates forever.

**Prompt:**
> Add a new Artisan command `bookings:expire-pending` in `app/Console/Commands` that finds all `Booking` records with `status = 'pending'` and `created_at` older than 30 minutes (make the threshold a config value `config('booking.pending_expiry_minutes')` defaulting to 30), and updates them to `status = 'cancelled'` with `cancellation_reason = 'Payment not completed in time'`. Register this command to run every 5 minutes in `routes/console.php` using the `Schedule` facade. Add a config file `config/booking.php` for the new setting.

---

### 4. `pay()` has no idempotency guard
`BookingController::pay()` can be called repeatedly on the same booking — even one that's already `confirmed`, `cancelled`, or paid — creating duplicate `Payment` rows and re-sending confirmation emails each time.

**Prompt:**
> In `app/Http/Controllers/BookingController.php`, at the start of the `pay()` method, add a guard that aborts with a 422 and a flash error message if `$booking->is_paid` is already `true` or `$booking->status` is `cancelled`. Wrap the payment-recording and booking-update logic in a `DB::transaction()` with `Booking::whereKey($booking->id)->lockForUpdate()->first()` to prevent a race where two simultaneous requests both pass the guard.

---

## 🟠 HIGH — real bugs affecting correctness/security

### 5. Admin user search silently ignores the role filter
In `Admin\UserController::index()`:
```php
$query->where('name', 'like', "%{$search}%")
      ->orWhere('email', 'like', "%{$search}%");
```
This `orWhere` is not grouped, so combined with a `role` filter the SQL becomes `WHERE role = ? AND name LIKE ? OR email LIKE ?` — any user whose email matches leaks into results regardless of role filter. The sibling `Admin\HotelController::index()` does this correctly with a grouped closure — use that as the reference pattern.

**Prompt:**
> In `app/Http/Controllers/Admin/UserController.php::index()`, wrap the `name`/`email` OR conditions in a grouped closure exactly like the pattern used in `app/Http/Controllers/Admin/HotelController.php::index()`, so the search and role filters combine with AND, not OR.

### 6. Same un-grouped `orWhere` bug in admin booking search
`Admin\BookingController::index()` has the identical issue: `where('booking_reference', ...)->orWhereHas('user', ...)` breaks the `status`/`from`/`to` filter grouping.

**Prompt:**
> In `app/Http/Controllers/Admin/BookingController.php::index()`, wrap the `booking_reference` and `orWhereHas('user', ...)` search conditions in a single grouped `where(function ($q) ...)` closure so they combine correctly with the existing `status`, `from`, and `to` filters using AND.

### 7. No rate limiting on login, register, or password change
Only the booking-creation route has a throttle. Login/register/password endpoints are open to brute-force/credential-stuffing.

**Prompt:**
> In `app/Providers/AppServiceProvider.php`, register a new named rate limiter `'login'` allowing 5 attempts per minute keyed by `email` + IP (e.g. `Limit::perMinute(5)->by($request->input('email').'|'.$request->ip())`). Apply `->middleware('throttle:login')` to the `POST /login` and `POST /register` routes in `routes/web.php`, and apply Laravel's built-in `throttle:6,1` to the `PUT /profile/password` route.

### 8. Admin can lock themselves out via the deactivate-toggle endpoint
`Admin\UserController::destroy()` correctly blocks self-deletion, but `toggle()` (used for deactivate/restore) has no equivalent check — an admin can deactivate their own account, including the only admin account, with no recovery path through the UI.

**Prompt:**
> In `app/Http/Controllers/Admin/UserController.php::toggle()`, add the same self-protection check used in `destroy()`: if `$user->id === auth()->id()`, return a 422 JSON response with an error message instead of toggling.

### 9. Status-change audit log misattributes admin actions to the customer
The `trg_bookings_status_after_update` MySQL trigger (in `database/migrations/2024_01_01_000006_create_booking_logs_table.php`) inserts `changed_by = NEW.user_id`, so every status change gets logged as if the *booking owner* made it — even when an admin changes status via `Admin\BookingController::updateStatus()`. This makes the `booking_logs` audit trail unreliable for accountability.

**Prompt:**
> Replace the database trigger approach for `booking_logs` with explicit application-level logging: remove the `trg_bookings_status_after_update` trigger (add a new migration to drop it), and instead, in `app/Http/Controllers/Admin/BookingController.php::updateStatus()`, manually insert a `BookingLog` record with `changed_by = auth()->id()` after updating the booking's status — but only if the status actually changed. Create a `BookingLog` Eloquent model for this if one doesn't exist (`app/Models/BookingLog.php`), with `booking()` and `changedBy()` relationships.

---

## 🟡 MEDIUM — functionality/UX gaps

### 10. Filtering/sorting/pagination on the hotel listing never updates the URL
`resources/views/hotels/index.blade.php` intercepts the search form, filter inputs, sort dropdown, and pagination links and loads results via `fetch()`, but never calls `history.pushState`/`replaceState`. Refreshing the page, using browser back/forward, or sharing the URL all silently discard the user's filters and return to page 1.

**Prompt:**
> In `resources/views/hotels/index.blade.php`, update the `loadHotels(url)` JS function to call `history.pushState({}, '', url)` after a successful fetch (skip this on the very first automatic load if any). Add a `window.addEventListener('popstate', ...)` handler that re-runs `loadHotels(window.location.href)` when the user navigates back/forward, so browser history and shareable URLs work correctly with the AJAX filtering.

### 11. No automatic check-in/check-out status transitions
Bookings only move from `confirmed` → `checked_in` → `checked_out` if an admin manually updates them. There's no logic tied to the actual `check_in`/`check_out` dates.

**Prompt:**
> Add a new Artisan command `bookings:auto-transition-status` that: (1) updates `confirmed` bookings to `checked_in` once `check_in` date has arrived; (2) updates `checked_in` bookings to `checked_out` once `check_out` date has passed. Use `DB::transaction()` and chunked queries for safety. Register it to run daily (or hourly, your call) in `routes/console.php`.

### 12. Admin status update allows any-to-any transitions with no validation
`Admin\BookingController::updateStatus()` accepts any status from the fixed enum list regardless of the booking's current status — e.g. `cancelled → confirmed` or `pending → checked_out` are both currently allowed.

**Prompt:**
> In `app/Http/Controllers/Admin/BookingController.php::updateStatus()`, add a validation step using a transition map (e.g. `pending` can only go to `confirmed` or `cancelled`; `confirmed` can only go to `checked_in` or `cancelled`; `checked_in` can only go to `checked_out`; `checked_out` and `cancelled` are terminal). Reject invalid transitions with a 422 and a clear error message. Put the transition map as a constant on the `Booking` model so it can be reused/tested.

---

## 🟢 LOW — config hygiene & polish (quick wins)

### 13. `.env` is set up for local dev, not production
`APP_DEBUG=true` (leaks stack traces/env details to any visitor on error), `APP_NAME=Laravel` (never renamed), `MAIL_MAILER=log` (no real emails will send), `LOG_LEVEL=debug`.

**Prompt:**
> Create a `.env.production.example` file (don't touch the real `.env`) documenting the production values: `APP_NAME=Roomora`, `APP_ENV=production`, `APP_DEBUG=false`, `LOG_LEVEL=error`, and a real `MAIL_MAILER` (smtp/ses/etc.) configuration block with placeholder values and comments explaining each.

### 14. Dead file: `resources/views/welcome.blade.php`
Unused — `/` redirects straight to `hotels.index` and nothing references `welcome`.

**Prompt:**
> Delete `resources/views/welcome.blade.php` since it's unreferenced dead code (confirm no route or redirect uses it first).

### 15. `getStatusBadgeAttribute()` builds raw HTML inside the model
Not a security bug today (input is a constrained enum), but it's a fragile pattern — any future change that lets `status` carry freer text would turn this into an XSS vector, and it mixes presentation into the model layer.

**Prompt:**
> Replace `Booking::getStatusBadgeAttribute()` (which returns raw HTML) with a small Blade component `<x-status-badge :status="$booking->status" />` in `resources/views/components/status-badge.blade.php` that renders the badge markup using `{{ }}` escaping, reading colors from `Booking::STATUS_COLORS`. Update the 7 views currently using `{!! $booking->status_badge !!}` to use the new component instead, and remove the accessor from the model.

---

## Suggested order of work
1. Items 1–4 (critical) — these affect money, security, and a broken core feature.
2. Items 5–9 (high) — quick, surgical fixes, each isolated to one method.
3. Item 10 — visible UX bug, likely what prompted this audit.
4. Items 11–12 — operational completeness.
5. Items 13–15 — cleanup before going live.

Each prompt above is scoped to specific files so you can run them one at a time and review the diff before moving to the next.
