# Roomora — Round 2 Audit (UI, Invoice PDF, Layout Errors + Upgrade Opportunities)

All 15 items from the first audit are confirmed fixed in this build (verified via diff — new commands, status-badge component, audit test, etc. are all present). This round focuses on the three symptoms you reported plus a forward-looking upgrade pass.

---

## 🔴 Invoice PDF download — confirmed root causes

### 1. Taka symbol (৳) likely renders as a missing-glyph box in the PDF
`resources/views/bookings/invoice.blade.php` uses `font-family: DejaVu Sans` (dompdf's bundled default) and prints `৳` six times. DejaVu Sans does not include Bengali/Indic glyphs. Since there's no custom dompdf font config anywhere in the project (`config/dompdf.php` doesn't exist, and `BookingController::downloadInvoice()` sets no font options), every price line in the invoice PDF is very likely showing a blank box or missing character instead of the currency symbol — while it displays correctly everywhere else in the app because browsers fall back to system fonts that dompdf can't use.

**Prompt:**
> Publish the dompdf config with `php artisan vendor:publish --tag=dompdf-config`, then in the resulting `config/dompdf.php`, add a font directory entry and register a font that supports the Bengali Taka glyph (e.g. Noto Sans Bengali or Noto Sans — download the .ttf and place it in `storage/fonts/` or `resources/fonts/`, then register it via dompdf's `Font_Metrics::get_font()` / autoload in a service provider, or simpler: replace `font-family: DejaVu Sans` in `resources/views/bookings/invoice.blade.php` with a font stack that includes a Noto-based font with Bengali coverage, and load that .ttf via `@font-face` with a local file path dompdf can resolve). As a faster interim fix, replace every `৳` in `invoice.blade.php` with the literal text `BDT ` so the invoice is correct immediately while the font fix is implemented properly.

### 2. Flexbox layouts will likely render broken/overlapping in the PDF
`invoice.blade.php` uses `display:flex` in `.header`, `.info-grid`, `.stay-grid`, and two inline styles. dompdf's flexbox support is partial and inconsistent across versions — common symptoms are columns collapsing to one stacked column, overlapping boxes, or ignored gaps.

**Prompt:**
> In `resources/views/bookings/invoice.blade.php`, replace all `display:flex` layouts (`.header`, `.info-grid`, `.stay-grid`, and the two inline `style="display:flex..."` blocks for Billing/Payment) with table-based or `display:inline-block` + `width` layouts, since dompdf has reliable support for both but only partial, version-dependent support for flexbox. Keep the same visual columns/spacing using `<table>` with fixed-width `<td>` cells or floated `div`s with explicit `width` percentages instead.

### 3. "Paid" status on the invoice ignores the actual payment flag
The invoice shows `Paid: {{ $booking->status === 'confirmed' ? 'Yes' : 'No' }}` instead of reading `$booking->is_paid` directly, even though that column exists specifically for this. If a booking is ever `confirmed` without payment (possible via the admin status-update endpoint), the invoice will incorrectly claim it was paid.

**Prompt:**
> In `resources/views/bookings/invoice.blade.php`, change the Payment summary box to display `{{ $booking->is_paid ? 'Yes' : 'No' }}` instead of deriving "Paid" from the booking status, and also show the actual `paid_at` timestamp underneath when `is_paid` is true.

### 4. Missing CSS class for `pending`/`checked_in` status badges
The invoice's `.status-badge` only has `.status-confirmed`, `.status-cancelled`, `.status-checked_out` defined — no `.status-pending` or `.status-checked_in`. Low impact today since download is gated to paid/confirmed bookings, but will render with no background color if that gate is ever loosened.

**Prompt:**
> In `resources/views/bookings/invoice.blade.php`, add CSS rules for `.status-pending` and `.status-checked_in` alongside the existing status badge classes, using colors consistent with `Booking::STATUS_COLORS` (e.g. amber for pending, blue for checked_in) so the badge never renders unstyled.

---

## 🟠 Master layout (layouts/admin.blade.php) — confirmed issues

### 5. Live, uncached query embedded directly in the shared admin layout
```php
@php $pending = \App\Models\Booking::where('status','pending')->count() @endphp
```
This runs a full count query **on every single admin page load** (dashboard, hotels, rooms, users, bookings, reports — anywhere the admin layout is used), since it's baked into the layout itself rather than passed down once from a controller or middleware. It also has no trailing semicolon before `@endphp`, which is fragile even if it currently parses.

**Prompt:**
> Remove the inline `@php $pending = \App\Models\Booking::where('status','pending')->count() @endphp` query from `resources/views/layouts/admin.blade.php`. Instead, create a small view composer (e.g. `app/View/Composers/AdminSidebarComposer.php`) that binds a `$pendingBookingsCount` variable to the `layouts.admin` view, computed once per request and ideally cached for ~60 seconds with `Cache::remember('admin.pending_bookings_count', 60, fn () => \App\Models\Booking::where('status', 'pending')->count())`. Register the composer in `AppServiceProvider::boot()`. Update the sidebar badge in `layouts/admin.blade.php` to use the new `$pendingBookingsCount` variable.

---

## 🟠 UI errors — silent validation failures across the app

This is the most likely cause of "UI errors" you're seeing: **every admin form, plus the profile edit page, shows zero feedback when validation fails.** A submit that fails just reloads the page with nothing visibly different — it looks broken even though the backend is working correctly.

### 6. Zero `@error` / `$errors` display anywhere in the admin panel
Confirmed missing in: `admin/hotels/create.blade.php`, `admin/hotels/edit.blade.php`, `admin/rooms/create.blade.php`, `admin/rooms/edit.blade.php`, `admin/users/edit.blade.php`.

**Prompt:**
> Go through `resources/views/admin/hotels/create.blade.php`, `admin/hotels/edit.blade.php`, `admin/rooms/create.blade.php`, `admin/rooms/edit.blade.php`, and `admin/users/edit.blade.php`. For every form input, add an `@error('field_name')<div class="invalid-feedback d-block">{{ $message }}</div>@enderror` directly below the input, and add the `is-invalid` class conditionally with `@error('field_name') is-invalid @enderror` on the input itself. Also add a general validation summary alert at the top of each form (inside an `@if($errors->any())` block) listing all errors, in case any don't map cleanly to a single field (e.g. image upload failures).

### 7. Zero error display on the profile edit page (both forms)
`resources/views/profile/edit.blade.php` has two forms — profile details (name/phone/address/avatar) and password change — neither shows any validation feedback. A wrong current password or an oversized avatar upload will silently appear to do nothing.

**Prompt:**
> In `resources/views/profile/edit.blade.php`, add `@error()` feedback under each input in both forms (name, phone, address, avatar in the first form; current_password, password, password_confirmation in the second), following the same pattern used in `resources/views/auth/register.blade.php`. Also wrap each form's fields with `is-invalid` classes triggered by `@error()` so Bootstrap renders the red border correctly.

---

## 🟢 Upgrade opportunities (no current error, but worth doing)

### 8. Cron/scheduler dependency for new automation
`bookings:expire-pending` and `bookings:auto-transition-status` are correctly registered in `routes/console.php`, but Laravel's scheduler only fires if your server has a cron entry running `php artisan schedule:run` every minute. If this isn't configured on your host, these commands will never run automatically.

**Prompt:**
> Add a section to `README.md` documenting the required production cron entry: `* * * * * cd /path-to-roomora && php artisan schedule:run >> /dev/null 2>&1`. If deploying on a platform without traditional cron access (e.g. some serverless hosts), also document the alternative of running `php artisan schedule:work` as a persistent background process via Supervisor or a process manager.

### 9. Hardcoded Bootstrap/CDN assets with no Vite build pipeline for them
The app pulls Bootstrap and Bootstrap Icons from `cdn.jsdelivr.net` directly in `<head>` rather than bundling them, even though the project already has a Vite setup (`resources/js/app.js`, `vite.config.js`). This means the site is fully dependent on a third-party CDN being reachable, and there's no Subresource Integrity (`integrity` attribute) on those `<script>`/`<link>` tags.

**Prompt:**
> Install `bootstrap` and `bootstrap-icons` as npm dependencies, import them in `resources/css/app.css` and `resources/js/app.js`, and remove the CDN `<link>`/`<script>` tags from `resources/views/layouts/app.blade.php` and `resources/views/layouts/admin.blade.php` in favor of `@vite(['resources/css/app.css', 'resources/js/app.js'])`. This removes the external CDN dependency and lets Vite fingerprint/cache-bust the assets properly.

### 10. No image lazy-loading on hotel/room cards
Hotel listing cards and galleries load all images eagerly, which will slow down pages with many hotel cards or large galleries.

**Prompt:**
> Add `loading="lazy"` to all `<img>` tags in `resources/views/hotels/partials/hotel-cards.blade.php`, `resources/views/components/hotel-gallery.blade.php`, and `resources/views/favorites/index.blade.php`, except for the first above-the-fold image on each page (e.g. the hero/first carousel image), which should keep eager loading for better perceived performance.

### 11. No API/JSON error handling for AJAX failures in the master layout
The favorite/compare toggle handlers in `layouts/app.blade.php` call `response.json()` with no try/catch and no check of `response.ok` — if the request fails for any reason (network error, 500, session expiry mid-session), the user gets a silent JS exception in the console with zero on-page feedback.

**Prompt:**
> In `resources/views/layouts/app.blade.php`, wrap the favorite/compare toggle `fetch()` calls in try/catch blocks. Check `response.ok` before calling `.json()`, and on any failure (non-OK response or thrown error), show a small Bootstrap toast or inline alert telling the user the action failed and to try again, instead of failing silently.

---

---

## 🔵 Booking-site benchmark: what Roomora is missing vs. Booking.com / Agoda / Expedia

I checked your actual search flow end-to-end. Right now: the navbar search box only takes a text query, the hotel listing page (`hotels/index.blade.php`) filters by city/stars/price/amenities but **never by dates**, and dates only enter the picture once a user clicks into one specific room on one specific hotel. Every major OTA makes dates + guest count the *primary*, mandatory search input from the very first screen — not an afterthought. This is the single highest-leverage UI/functionality gap relative to the sites you're benchmarking against.

### 12. No date/guest picker on the search bar or listing page
On Booking.com, Agoda, and Expedia, you cannot search at all without picking check-in/check-out dates and guest count first — it's the anchor of the whole experience, and every hotel card shown afterward reflects real availability and a real total price for those dates.

**Prompt:**
> Add `check_in`, `check_out`, and `guests` as query parameters to the hotel listing search. In `resources/views/hotels/index.blade.php`, add a prominent date-range picker (two date inputs or a single range picker, e.g. using a lightweight library like Litepicker or flatpickr loaded via CDN) and a guest-count stepper input at the top of the filter sidebar, defaulting to tomorrow/day-after and 2 guests if not set, mirroring them via `old()`/`request()` like the existing filters. Also add a compact version of the same date/guest picker to the navbar search form in `resources/views/layouts/app.blade.php` so date-aware search is reachable from every page, not just the listing.

### 13. Hotel listing never filters or prices by actual date availability
`HotelController::index()` filters on `rooms.price` and `rooms.is_available`, but never checks `Room::isAvailableForDates()` against the dates the user searched for. A hotel with zero free rooms on the requested dates can still appear in results, and the "from ৳X/night" price shown isn't tied to those dates at all.

**Prompt:**
> In `app/Http/Controllers/HotelController.php::index()`, when `check_in` and `check_out` request parameters are present, modify the `Hotel::active()` query to only include hotels with at least one room where `Room::isAvailableForDates($checkIn, $checkOut)` is true (you'll likely need a new Eloquent scope on `Room`, e.g. `scopeAvailableForDates($query, $checkIn, $checkOut)` using a `whereDoesntHave('bookings', ...)` subquery equivalent to the logic already in `isAvailableForDates()`, since that method currently only works on a single hydrated model instance and can't be used directly in a query builder chain). Pass `check_in`/`check_out` through to the hotel card price display so "From ৳X/night" reflects rates for the searched dates, and forward them into the `hotels.show` route links so the user's dates persist when they click into a hotel.

### 14. Hotel detail page doesn't carry search dates forward
Right now, going from the listing page into a specific hotel loses any date context — the user has to re-enter dates again on the room booking page. Booking.com/Agoda persist your dates through the entire funnel: search → hotel page → room selection → checkout, with the dates visible and editable at every step.

**Prompt:**
> In `resources/views/hotels/show.blade.php`, read `check_in`, `check_out`, and `guests` from the query string (falling back to sensible defaults if absent) and display them in a small editable date/guest bar near the top of the page, similar to the filter sidebar's picker. Pass these values through to each room's "Book Now" link as query parameters, and update `app/Http/Controllers/BookingController.php::create()` to prefer these over its current hardcoded `today()->addDay()` defaults when present. Also use these dates to show real per-room availability and pricing directly in the room list on the hotel page (reuse the existing `checkAvailability` AJAX endpoint or compute server-side), instead of only showing static room prices with no date context until the user clicks through.

### 15. No "free cancellation" / cancellation policy badge on listings
Every major OTA prominently surfaces cancellation flexibility (e.g. "Free cancellation," "Reserve now, pay at hotel") directly on the search results card, because it's one of the top factors influencing click-through. Roomora's `Booking::canBeCancelled()` logic already exists (cancellable while `pending`/`confirmed` and check-in is in the future) but this policy is never surfaced on the listing or hotel page — only mentioned in passing on the booking creation page.

**Prompt:**
> Add a small "Free cancellation before check-in" badge (using a check-circle icon, matching the existing badge style) to each hotel card in `resources/views/hotels/partials/hotel-cards.blade.php` and to the room cards in `resources/views/hotels/show.blade.php`, consistent with the cancellation policy already described in `Booking::canBeCancelled()`. This is purely a display addition — no backend logic change needed since the policy already exists.

### 16. No "X rooms left" urgency signal
Booking.com and Agoda are well known for urgency UI ("Only 2 rooms left at this price"). Roomora already has the data to support a *legitimate* version of this — `Room::quantity` and active bookings.

**Prompt:**
> In `resources/views/hotels/show.blade.php`, for each room with `is_available = true`, compute and display the real remaining quantity (`$room->quantity - $room->activeBookings()->count()`) as a badge when it's 3 or fewer, e.g. "Only 2 rooms left." Only show this when genuinely low — never fabricate urgency — and add this as a computed accessor `getRemainingQuantityAttribute()` on the `Room` model rather than inline logic in the view, so it's reusable and testable.

### 17. No map view for hotel results
Agoda, Booking.com, and Expedia all default to or prominently offer a map alongside list results, since location relative to landmarks/city center is often the deciding factor. Roomora's `hotels` table already has `latitude`/`longitude` columns that are currently unused anywhere in the UI.

**Prompt:**
> Add a "Map View" toggle button to `resources/views/hotels/index.blade.php` that renders a Leaflet.js map (free, no API key needed, unlike Google Maps) showing a pin for each hotel in the current filtered results, using the existing `latitude`/`longitude` columns on the `Hotel` model. Each pin's popup should show the hotel name, star rating, and "from ৳X/night" price with a link to the hotel page. Load Leaflet via CDN in `@push('styles')`/`@push('scripts')` blocks scoped to this page only.

### 18. No "recently viewed hotels" tracking
A staple of every major OTA homepage/listing experience, and easy to add given the existing `search_logs` table pattern already in the schema.

**Prompt:**
> Add a `recently_viewed_hotels` cookie (JSON array of hotel IDs, capped at 10, most-recent-first) that gets updated in `HotelController::show()` whenever an authenticated or guest user views a hotel. Add a "Recently Viewed" section to `resources/views/hotels/index.blade.php` (shown above or beside the results grid) that queries those hotel IDs and displays them as a horizontal scrollable row of small cards, similar to the main hotel-cards partial but more compact.

---

## Suggested order of work
1. Invoice PDF fixes (1–4) — directly affects a deliverable customers rely on (their receipt).
2. Admin layout query fix (5) — quick win, meaningful performance/architecture improvement.
3. Validation feedback gaps (6–7) — this is almost certainly what's reading as "UI errors"; admins and users are submitting forms that fail silently.
4. Date-aware search (12–14) — this is the biggest functional gap vs. real booking sites and affects the core user journey; tackle as one connected piece of work since they build on each other.
5. Trust/urgency/discovery polish (15–18) — high visual impact, lower technical risk, good for a follow-up pass once the core search flow is fixed.
6. Upgrade items (8–11) — do these once the above are stable; none are urgent, but #8 (cron) matters before you rely on the new automation in production.
