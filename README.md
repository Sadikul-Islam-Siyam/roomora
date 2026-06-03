# Roomora — Hotel Booking & Comparison Platform
## Complete Setup & Developer Guide

---

## 1. System Requirements

| Tool      | Version        |
|-----------|----------------|
| PHP       | 8.2+           |
| Laravel   | 12.x           |
| MySQL     | 8.0+           |
| Composer  | 2.x            |
| Node.js   | 18+ (optional) |

---

## 2. Installation — Step by Step

### Step 1: Create a Laravel 12 project

```bash
composer create-project laravel/laravel roomora
cd roomora
```

### Step 2: Copy all project files

Copy every file from this guide into the corresponding path inside your Laravel project.

### Step 3: Configure .env

```env
APP_NAME=Roomora
APP_ENV=local
APP_URL=http://localhost:8000

DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=roomora
DB_USERNAME=root
DB_PASSWORD=

SESSION_DRIVER=file

Make sure `DB_DATABASE` is set to your actual local database name. The app now throws a helpful error if it is still left as the default `laravel` value.

MAIL_MAILER=smtp
MAIL_HOST=smtp.mailtrap.io
MAIL_PORT=2525
MAIL_USERNAME=your_mailtrap_user
MAIL_PASSWORD=your_mailtrap_pass
MAIL_FROM_ADDRESS=noreply@roomora.com
MAIL_FROM_NAME="Roomora"

FILESYSTEM_DISK=public
```

### Step 4: Create the MySQL database

```sql
CREATE DATABASE roomora CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
```

### Step 5: Install PHP packages

```bash
# PDF generation (for invoices)
composer require barryvdh/laravel-dompdf

# Image intervention (optional, for resizing)
composer require intervention/image
```

### Step 6: Register the Admin Middleware

In `bootstrap/app.php`, add the middleware alias:

```php
->withMiddleware(function (Middleware $middleware) {
    $middleware->alias([
        'admin' => \App\Http\Middleware\AdminMiddleware::class,
    ]);
})
```

### Step 7: Register Policies in AuthServiceProvider

In `app/Providers/AppServiceProvider.php`:

```php
use App\Models\Booking;
use App\Models\Review;
use App\Policies\BookingPolicy;
use App\Policies\ReviewPolicy;
use Illuminate\Support\Facades\Gate;

public function boot(): void
{
    Gate::policy(Booking::class, BookingPolicy::class);
    Gate::policy(Review::class, ReviewPolicy::class);
}
```

### Step 8: Run migrations and seed

```bash
php artisan migrate
php artisan db:seed
```

### Step 9: Create storage link

```bash
php artisan storage:link
```

If this step is skipped, uploaded hotel and room images will fall back to the placeholder image instead of rendering from the public storage path.

### Step 10: Start the server

```bash
php artisan serve
```

Visit: **http://localhost:8000**

---

## 3. Login Credentials (after seeding)

| Role  | Email                  | Password   |
|-------|------------------------|------------|
| Admin | admin@roomora.com      | password   |
| User  | user1@example.com      | password   |
| User  | user2@example.com      | password   |

---

## 4. Directory Structure

```
app/
├── Http/
│   ├── Controllers/
│   │   ├── Auth/
│   │   │   └── AuthController.php         # Login, Register, Logout
│   │   ├── Admin/
│   │   │   ├── DashboardController.php    # Admin stats + charts
│   │   │   ├── HotelController.php        # Hotel CRUD
│   │   │   ├── RoomController.php         # Room CRUD
│   │   │   ├── UserController.php         # User management
│   │   │   └── BookingController.php      # Booking mgmt + reports + CSV export
│   │   ├── HotelController.php            # Public hotel browsing + AJAX search
│   │   ├── BookingController.php          # Book, cancel, invoice download
│   │   ├── ReviewController.php           # Create, edit, delete reviews
│   │   ├── ComparisonController.php       # Add/remove/view comparisons
│   │   ├── FavoriteController.php         # Wishlist toggle
│   │   └── ProfileController.php         # Profile + booking history
│   ├── Middleware/
│   │   └── AdminMiddleware.php            # Protects /admin routes
│   └── Requests/                          # (add FormRequest classes here later)
├── Mail/
│   └── BookingConfirmation.php            # Booking email mailable
├── Models/
│   ├── User.php
│   ├── Hotel.php
│   ├── Room.php
│   ├── Booking.php
│   ├── Review.php
│   ├── Comparison.php
│   └── Favorite.php
└── Policies/
    ├── BookingPolicy.php
    └── ReviewPolicy.php

database/
├── migrations/
│   ├── ..._create_users_table.php
│   ├── ..._create_hotels_table.php
│   ├── ..._create_rooms_table.php
│   ├── ..._create_bookings_table.php
│   └── ..._create_reviews_comparisons_favorites_tables.php
└── seeders/
    └── DatabaseSeeder.php

resources/views/
├── layouts/
│   ├── app.blade.php                      # Public layout + navbar
│   └── admin.blade.php                    # Admin sidebar layout
├── auth/
│   ├── login.blade.php
│   └── register.blade.php
├── hotels/
│   ├── index.blade.php                    # Browse/search/filter
│   ├── show.blade.php                     # Hotel detail + rooms + reviews
│   └── partials/
│       └── hotel-cards.blade.php          # Reusable card partial (AJAX)
├── bookings/
│   ├── create.blade.php                   # Booking form with live price
│   ├── show.blade.php                     # Booking detail + cancel
│   └── invoice.blade.php                  # PDF invoice template
├── comparisons/
│   └── index.blade.php                    # Side-by-side comparison table
├── admin/
│   └── dashboard.blade.php                # Chart.js stats dashboard
└── emails/
    └── booking-confirmation.blade.php     # HTML email template

routes/
└── web.php                                # All routes
```

---

## 5. Key Features Implemented

### Security
- CSRF tokens on all POST/PUT/DELETE forms
- Laravel `auth` middleware on all user routes
- Custom `admin` middleware on all admin routes
- `Authorization Policies` for Booking and Review (users can only manage their own)
- `strip_tags()` on all user text input to prevent XSS
- `Password::min(8)->letters()->numbers()` validation rule
- `SoftDeletes` on users, hotels, rooms, bookings, reviews
- File upload validation: `mimes:jpg,jpeg,png,webp|max:5120`
- Eloquent ORM (no raw SQL, preventing SQL injection)
- One review per user per hotel (unique constraint + app-level check)
- Review only allowed after confirmed stay (checked_out status)

### AJAX Features
- Live search suggestions (hotel name, city) in navbar
- Hotel filter form auto-submits on change
- Compare/Favorite toggle without page reload
- Live room availability check with price calculation
- Dashboard stat refresh button

### Performance
- Database indexes on frequently queried columns
- Eager loading (`with()`) to avoid N+1 queries
- `withCount()` and `withAvg()` for aggregated data
- `paginate()` on all listing pages
- `scopeActive()`, `scopeSearch()` etc. to keep controllers clean

### Email
- `BookingConfirmation` Mailable sent after booking
- HTML email template with booking details and reference number
- Try/catch so email failure doesn't break the booking

### PDF Invoice
- Generated with `barryvdh/laravel-dompdf`
- Includes booking reference, hotel details, price breakdown
- Downloads as `invoice-RMR-2024-XXXXXX.pdf`

### CSV Export
- Admin can export filtered bookings as CSV
- Uses `response()->stream()` for memory-efficient export

---

## 6. Adding New Features

### Add a new filter to hotel search
1. Add a filter input in `hotels/index.blade.php`
2. Handle it in `HotelController::index()` using a scope

### Add a new admin stat to the dashboard
1. Add the DB query in `Admin/DashboardController::index()`
2. Pass to view and add a new `<canvas>` in `admin/dashboard.blade.php`
3. Initialize a new Chart.js chart in the `@push('scripts')` section

### Add a new room facility
Add to the array in `Room::TYPES` or create a `FACILITIES` constant.

### Add email notifications
Create a new Mailable: `php artisan make:mail BookingCancelled`
Dispatch it in `BookingController::cancel()`.

---

## 7. Remaining Views to Build (Next Modules)

These follow the exact same patterns established above:

| View File                           | Purpose                          |
|-------------------------------------|----------------------------------|
| `profile/show.blade.php`            | User profile page                |
| `profile/edit.blade.php`            | Edit profile + avatar upload     |
| `profile/bookings.blade.php`        | Booking history list             |
| `favorites/index.blade.php`         | Wishlist page                    |
| `admin/hotels/index.blade.php`      | Hotel management table           |
| `admin/hotels/create.blade.php`     | Hotel create form                |
| `admin/hotels/edit.blade.php`       | Hotel edit form                  |
| `admin/rooms/index.blade.php`       | Room management table            |
| `admin/rooms/create.blade.php`      | Room create form                 |
| `admin/users/index.blade.php`       | User management table            |
| `admin/bookings/index.blade.php`    | Booking management table         |
| `admin/bookings/show.blade.php`     | Single booking admin view        |
| `admin/reports.blade.php`           | Revenue reports + Chart.js       |

---

## 8. Testing

### Manual Test Checklist

**Auth Module**
- [ ] Register with valid data → redirects to hotels
- [ ] Register with duplicate email → shows error
- [ ] Login with wrong password → shows error
- [ ] Admin login → redirects to /admin
- [ ] Logout → clears session

**Hotel Module**
- [ ] Search by name, city → results update
- [ ] Filter by star rating, price → results filter
- [ ] Sort options work correctly
- [ ] Hotel detail page loads with rooms and reviews

**Booking Module**
- [ ] Select dates → price recalculates live
- [ ] Book unavailable dates → shows error
- [ ] Book > capacity guests → shows error
- [ ] Successful booking → sends email + shows reference
- [ ] Cancel booking → status changes to cancelled
- [ ] Download invoice → PDF downloads

**Comparison Module**
- [ ] Add up to 4 hotels → bar appears at bottom
- [ ] Adding 5th hotel → shows max error
- [ ] Comparison table → amenities show yes/no correctly

**Review Module**
- [ ] Submit review → appears on hotel page
- [ ] Try to review without a completed stay → shows error
- [ ] Delete own review → removed
- [ ] Cannot delete others' reviews

**Admin Module**
- [ ] Dashboard loads with charts
- [ ] Stats refresh button → updates numbers
- [ ] Hotel CRUD all work
- [ ] Booking status update works
- [ ] CSV export downloads correctly

---

## 9. Deployment Checklist

```bash
# Set production environment
APP_ENV=production
APP_DEBUG=false

# Optimize
php artisan config:cache
php artisan route:cache
php artisan view:cache
php artisan optimize

# Run migrations
php artisan migrate --force

# Ensure storage is linked
php artisan storage:link
```

---

## 10. Package.json (optional frontend build)

If you want to compile assets with Vite:

```bash
npm install
npm run dev    # development
npm run build  # production
```

Otherwise all CSS/JS is loaded from CDN (Bootstrap 5, Chart.js, Bootstrap Icons) — no build step required.
