# Utility Components

This directory contains utility components that provide common functionality across the application.

## Components

### Display Utilities
- `show-hide.blade.php` - Conditional content display
- `truncate.blade.php` - Text truncation with tooltip
- `format-date.blade.php` - Date formatting component
- `format-currency.blade.php` - Currency formatting component
- `format-number.blade.php` - Number formatting component

### Status Components
- `status-badge.blade.php` - Status indicator badge
- `progress-bar.blade.php` - Progress bar component
- `countdown.blade.php` - Countdown timer component
- `online-indicator.blade.php` - Online/offline status indicator

### Content Utilities
- `empty-state.blade.php` - Empty state placeholder
- `error-boundary.blade.php` - Error display component
- `debug-info.blade.php` - Debug information display (dev only)
- `meta-tags.blade.php` - SEO meta tags component

### Interactive Utilities
- `copy-to-clipboard.blade.php` - Copy text to clipboard
- `share-button.blade.php` - Social sharing button
- `print-button.blade.php` - Print page button
- `back-to-top.blade.php` - Back to top button

### Accessibility Utilities
- `screen-reader-only.blade.php` - Screen reader only content
- `skip-link.blade.php` - Skip navigation link
- `focus-trap.blade.php` - Focus management for modals

## Usage

Utility components provide common functionality with consistent styling:

```blade
<x-utility.status-badge :status="$auction->status" />

<x-utility.format-currency :amount="$bid->amount" />

<x-utility.empty-state 
    title="No auctions found"
    description="There are no auctions matching your criteria."
    action-text="Create New Auction"
    action-url="{{ route('admin.auctions.create') }}"
/>

<x-utility.truncate :text="$description" :limit="100" />
```