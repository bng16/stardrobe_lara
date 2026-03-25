# Complex Components

This directory contains more sophisticated components that combine multiple UI elements or provide advanced functionality.

## Components

### Data Display Components
- `table.blade.php` - Data table component with sorting and pagination
- `data-grid.blade.php` - Advanced data grid with filtering
- `stats-card.blade.php` - Statistics display card
- `chart.blade.php` - Chart/graph display component

### Interactive Components
- `modal.blade.php` - Modal dialog component
- `dropdown.blade.php` - Advanced dropdown menu component
- `tabs.blade.php` - Tab navigation component
- `accordion.blade.php` - Collapsible accordion component
- `tooltip.blade.php` - Tooltip component

### Layout Components
- `sidebar-layout.blade.php` - Sidebar layout wrapper
- `dashboard-layout.blade.php` - Dashboard-specific layout
- `two-column.blade.php` - Two-column layout component
- `hero-section.blade.php` - Hero section component

### Specialized Components
- `auction-card.blade.php` - Auction listing card
- `creator-card.blade.php` - Creator profile card
- `bid-history.blade.php` - Bid history display
- `image-gallery.blade.php` - Image gallery with lightbox

## Usage

Complex components often accept multiple props and may include their own JavaScript:

```blade
<x-complex.table 
    :data="$auctions" 
    :columns="['title', 'status', 'bids_count', 'highest_bid']"
    sortable
    paginated
/>

<x-complex.modal id="create-creator-modal" title="Create New Creator">
    <x-slot name="content">
        <!-- Modal content -->
    </x-slot>
    <x-slot name="footer">
        <x-ui.button variant="secondary" data-dismiss="modal">Cancel</x-ui.button>
        <x-ui.button type="submit">Create</x-ui.button>
    </x-slot>
</x-complex.modal>
```