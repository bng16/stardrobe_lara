# Layout Components

This directory contains layout-specific components that help structure pages and sections.

## Components

### Page Layouts
- `page-header.blade.php` - Standard page header with title and actions
- `page-content.blade.php` - Main page content wrapper
- `page-footer.blade.php` - Page footer component
- `container.blade.php` - Content container with responsive padding

### Section Layouts
- `section.blade.php` - Generic section wrapper
- `grid.blade.php` - CSS Grid layout component
- `flex.blade.php` - Flexbox layout component
- `columns.blade.php` - Multi-column layout component

### Admin Layouts
- `admin-page.blade.php` - Admin page wrapper with sidebar
- `admin-content.blade.php` - Admin content area
- `admin-header.blade.php` - Admin page header
- `dashboard-grid.blade.php` - Dashboard grid layout

### Responsive Layouts
- `mobile-layout.blade.php` - Mobile-specific layout
- `tablet-layout.blade.php` - Tablet-specific layout
- `desktop-layout.blade.php` - Desktop-specific layout

## Usage

Layout components provide structure and consistent spacing:

```blade
<x-layout.page-header title="Admin Dashboard">
    <x-slot name="actions">
        <x-ui.button href="{{ route('admin.export') }}">Export Data</x-ui.button>
    </x-slot>
</x-layout.page-header>

<x-layout.container>
    <x-layout.grid cols="4" gap="6">
        <x-ui.card>Statistics Card 1</x-ui.card>
        <x-ui.card>Statistics Card 2</x-ui.card>
        <x-ui.card>Statistics Card 3</x-ui.card>
        <x-ui.card>Statistics Card 4</x-ui.card>
    </x-layout.grid>
</x-layout.container>
```