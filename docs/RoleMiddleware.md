# RoleMiddleware Documentation

## Overview

The `RoleMiddleware` provides role-based route protection for the Creator Marketplace application. It ensures that only users with the appropriate role can access protected routes.

## Implementation

The middleware is located at `app/Http/Middleware/RoleMiddleware.php` and has been registered in `bootstrap/app.php` with the alias `role`.

## Usage

### Basic Usage

Apply the middleware to routes using the `role` alias followed by the required role:

```php
Route::middleware(['auth', 'role:admin'])->group(function () {
    Route::get('/admin/dashboard', [AdminController::class, 'index']);
});

Route::middleware(['auth', 'role:creator'])->group(function () {
    Route::get('/creator/products', [ProductController::class, 'index']);
});

Route::middleware(['auth', 'role:buyer'])->group(function () {
    Route::get('/marketplace', [MarketplaceController::class, 'index']);
});
```

### Available Roles

- `admin` - Administrative users with full system access
- `creator` - Users who can list products and manage their shop
- `buyer` - Users who can place bids and purchase products

## Behavior

### Authorized Access

When a user with the correct role accesses a protected route:
- The request proceeds normally
- The user can access the route's functionality

### Unauthorized Access

When a user without the correct role attempts to access a protected route:
- The middleware returns a 403 Forbidden response
- The error message is "Unauthorized access"
- The request is blocked before reaching the controller

### Unauthenticated Access

When an unauthenticated user attempts to access a protected route:
- The middleware returns a 403 Forbidden response
- The user should be redirected to login by the `auth` middleware (which should be applied before `role`)

## Best Practices

1. **Always combine with `auth` middleware**: The role middleware checks the authenticated user's role, so it should always be used with the `auth` middleware:
   ```php
   Route::middleware(['auth', 'role:admin'])->get('/admin', ...);
   ```

2. **Order matters**: Apply `auth` middleware before `role` middleware to ensure the user is authenticated before checking their role.

3. **Use route groups**: Group routes with the same role requirements to avoid repetition:
   ```php
   Route::middleware(['auth', 'role:creator'])->prefix('creator')->group(function () {
       Route::get('/dashboard', [CreatorController::class, 'dashboard']);
       Route::get('/products', [ProductController::class, 'index']);
       Route::post('/products', [ProductController::class, 'store']);
   });
   ```

## Testing

The middleware includes comprehensive tests in `tests/Feature/RoleMiddlewareTest.php` that verify:
- Admin users can access admin routes
- Creator users can access creator routes
- Buyer users can access buyer routes
- Users with incorrect roles are denied access (403)
- Unauthenticated users are denied access (403)

Run the tests with:
```bash
php artisan test --filter=RoleMiddlewareTest
```

## Requirements Validation

This middleware validates the following requirements:
- **Requirement 1.4**: The System SHALL restrict route access based on user role using middleware
- **Requirement 1.5**: WHEN an unauthorized user attempts to access a protected route, THE System SHALL redirect them to an appropriate page (returns 403 Forbidden)
