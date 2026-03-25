# Navigation Components

This directory contains navigation-related components for consistent navigation throughout the application.

## Available Components

### Breadcrumb Component
**File:** `breadcrumb.blade.php`
**Usage:** `<x-navigation.breadcrumb :items="$breadcrumbItems" />`

Creates a breadcrumb navigation trail.

**Props:**
- `items` (array): Array of breadcrumb items with 'title' and optional 'url' keys

**Example:**
```php
$breadcrumbItems = [
    ['title' => 'Home', 'url' => route('home')],
    ['title' => 'Admin', 'url' => route('admin.dashboard')],
    ['title' => 'Users'] // Current page (no URL)
];
```

### Mobile Menu Button
**File:** `mobile-menu-button.blade.php`
**Usage:** `<x-navigation.mobile-menu-button target="mobileMenuOpen" />`

Creates a hamburger menu button for mobile navigation.

**Props:**
- `target` (string): Alpine.js variable name to toggle

### Sidebar Link
**File:** `sidebar-link.blade.php`
**Usage:** `<x-navigation.sidebar-link :href="route('admin.dashboard')" :active="request()->routeIs('admin.dashboard')" :icon="$dashboardIcon">Dashboard</x-navigation.sidebar-link>`

Creates a sidebar navigation link with icon support.

**Props:**
- `href` (string): Link URL
- `active` (boolean): Whether the link is currently active
- `icon` (string, optional): SVG icon HTML

## Main Navigation Components

The main navigation system consists of several key partials:

### Main Site Navigation
- `layouts/partials/navigation.blade.php` - Main site navigation with user dropdown and responsive menu
- Includes marketplace links, user authentication state, and mobile menu support
- Uses Alpine.js for interactive dropdown and mobile menu functionality

### Admin Navigation
- `layouts/partials/admin-navigation.blade.php` - Admin navigation bar with user dropdown
- `layouts/partials/admin-sidebar.blade.php` - Admin sidebar with menu items and responsive behavior
- Includes dashboard, creators, auctions, bids, reports, and settings sections

### Footer Navigation
- `layouts/partials/footer.blade.php` - Site footer with company info, quick links, and legal pages
- Includes social media links and comprehensive site navigation

## Integration with Alpine.js

All navigation components are designed to work with Alpine.js for interactive functionality:

- Dropdown menus use Alpine.js for show/hide behavior
- Mobile menus use Alpine.js for responsive toggling
- Sidebar components support Alpine.js state management
- Event-based communication between navigation and sidebar components

## Accessibility Features

- Proper ARIA labels and roles
- Keyboard navigation support
- Screen reader friendly markup
- Focus management for interactive elements
- Skip to main content links

## Styling

All components use Tailwind CSS classes and follow the application's design system:

- Consistent color scheme (gray-based with blue accents)
- Smooth transitions and hover effects
- Responsive design patterns
- Proper focus states and interactive feedback

## Authentication State Handling

Navigation components properly handle different authentication states:

- Guest users see login/register links
- Authenticated users see profile dropdown with logout
- Role-based navigation (admin, creator, regular user)
- Proper route checking for active states