# Blade Layout Structure

This directory contains the base Blade layout structure for the application conversion from Inertia.js/React to traditional Blade templates.

## Layout Files

### Main Layouts

- **`app.blade.php`** - Main application layout for public pages
  - Includes navigation, flash messages, and footer
  - Uses `@yield('content')` and `{{ $slot }}` for content
  - Supports page-specific title, body class, styles, and scripts

- **`admin.blade.php`** - Admin panel layout with sidebar navigation
  - Fixed sidebar with admin navigation menu
  - Top navigation bar with user dropdown
  - Content area with proper spacing and flash messages
  - Designed for admin dashboard and management pages

- **`auth.blade.php`** - Authentication pages layout
  - Centered card design for login/register forms
  - Includes application logo and footer links
  - Clean, minimal design focused on authentication

### Partials

Located in `partials/` subdirectory:

- **`navigation.blade.php`** - Main site navigation with user dropdown
- **`admin-navigation.blade.php`** - Admin top navigation bar
- **`admin-sidebar.blade.php`** - Admin sidebar with menu items
- **`flash-messages.blade.php`** - Flash message display (success, error, warning, info)
- **`footer.blade.php`** - Site footer with links and company info

## Component Structure

### Base Components

- **`application-logo.blade.php`** - SVG application logo component
- **`nav-link.blade.php`** - Navigation link with active state styling
- **`responsive-nav-link.blade.php`** - Mobile navigation link component
- **`dropdown.blade.php`** - Dropdown menu component (requires Alpine.js)
- **`dropdown-link.blade.php`** - Dropdown menu item component

### Component Directories

- **`components/ui/`** - UI components (buttons, cards, etc.) - to be created
- **`components/forms/`** - Form components (inputs, textareas, etc.) - to be created
- **`components/navigation/`** - Additional navigation components - to be created

## Usage Examples

### Using the Main App Layout

```blade
@extends('layouts.app')

@section('title', 'Page Title')

@section('content')
    <div class="container mx-auto">
        <h1>Page Content</h1>
    </div>
@endsection

@push('scripts')
    <script>
        // Page-specific JavaScript
    </script>
@endpush
```

### Using the Admin Layout

```blade
@extends('layouts.admin')

@section('title', 'Admin Dashboard')

@section('content')
    <div class="space-y-6">
        <h1 class="text-2xl font-bold">Dashboard</h1>
        <!-- Admin content -->
    </div>
@endsection
```

### Using the Auth Layout

```blade
@extends('layouts.auth')

@section('title', 'Login')

@section('content')
    <form method="POST" action="{{ route('login') }}">
        @csrf
        <!-- Login form fields -->
    </form>
@endsection
```

## Features

- **Responsive Design**: All layouts are mobile-friendly
- **Flash Messages**: Automatic display of session flash messages
- **Navigation States**: Active link highlighting
- **Asset Management**: Support for Vite asset compilation
- **Extensible**: Easy to add new layouts and components
- **Accessible**: Semantic HTML and ARIA attributes
- **Security**: CSRF token included in all layouts

## Next Steps

1. Create UI components (buttons, cards, forms)
2. Convert existing React pages to use these layouts
3. Update controllers to return `view()` instead of `Inertia::render()`
4. Test layouts with actual content and data