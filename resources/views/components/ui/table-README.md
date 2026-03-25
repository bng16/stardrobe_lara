# Table Component

A comprehensive, reusable Blade table component with sorting, pagination, and accessibility features.

## Features

- ✅ Sortable column headers with visual indicators
- ✅ Laravel pagination integration
- ✅ Responsive design with horizontal scrolling
- ✅ Loading and empty states
- ✅ Custom column formatting (currency, date, datetime, number)
- ✅ Custom column alignment (left, center, right)
- ✅ Custom column rendering with closures
- ✅ Striped and hover row styles
- ✅ Compact mode for dense layouts
- ✅ Full accessibility support (ARIA attributes, keyboard navigation)
- ✅ Dot notation support for nested data
- ✅ Tailwind CSS styling with design system colors

## Basic Usage

```blade
<x-ui.table 
    :data="$users" 
    :columns="$columns"
    :sortable="true"
    current-sort="{{ request('sort') }}"
    current-direction="{{ request('direction', 'asc') }}"
/>
```

## Props

| Prop | Type | Default | Description |
|------|------|---------|-------------|
| `data` | `Collection\|LengthAwarePaginator` | `null` | The data to display in the table |
| `columns` | `array` | `[]` | Column definitions (see Column Configuration) |
| `sortable` | `boolean` | `true` | Enable/disable sorting functionality |
| `currentSort` | `string` | `null` | Currently sorted column key |
| `currentDirection` | `string` | `'asc'` | Current sort direction ('asc' or 'desc') |
| `emptyMessage` | `string` | `'No data available'` | Message shown when no data |
| `loading` | `boolean` | `false` | Show loading state |
| `striped` | `boolean` | `false` | Enable striped rows |
| `hover` | `boolean` | `true` | Enable hover effects |
| `compact` | `boolean` | `false` | Use compact padding |

## Column Configuration

Each column is defined as an array with the following options:

```php
$columns = [
    [
        'key' => 'name',           // Data key (supports dot notation)
        'label' => 'Name',         // Column header text
        'sortable' => true,        // Enable sorting (default: true)
        'width' => '200px',        // Column width (optional)
        'align' => 'left',         // Alignment: 'left', 'center', 'right'
        'format' => 'currency',    // Built-in formatting (optional)
        'render' => function($row, $value) { // Custom rendering (optional)
            return '<strong>' . e($value) . '</strong>';
        }
    ]
];
```

### Built-in Formatters

- `currency`: Formats numbers as currency ($1,234.56)
- `date`: Formats dates as "Mar 15, 2024"
- `datetime`: Formats dates as "Mar 15, 2024 2:30 PM"
- `number`: Formats numbers with commas (1,234)

### Custom Rendering

Use the `render` callback for complex column content:

```php
[
    'key' => 'status',
    'label' => 'Status',
    'render' => function($row, $value) {
        $colors = [
            'active' => 'bg-green-100 text-green-800',
            'inactive' => 'bg-red-100 text-red-800',
        ];
        $class = $colors[$value] ?? 'bg-gray-100 text-gray-800';
        return '<span class="px-2 py-1 rounded-full text-xs ' . $class . '">' . ucfirst($value) . '</span>';
    }
]
```

## Examples

### Basic User Table

```php
// Controller
$users = User::paginate(10);
$columns = [
    ['key' => 'id', 'label' => 'ID', 'width' => '80px'],
    ['key' => 'name', 'label' => 'Name', 'sortable' => true],
    ['key' => 'email', 'label' => 'Email', 'sortable' => true],
    ['key' => 'created_at', 'label' => 'Joined', 'format' => 'date'],
];

// Blade template
<x-ui.table 
    :data="$users" 
    :columns="$columns"
    current-sort="{{ request('sort') }}"
    current-direction="{{ request('direction', 'asc') }}"
    :striped="true"
/>
```

### Product Table with Custom Rendering

```php
$products = Product::with(['creator', 'images'])->paginate(10);
$columns = [
    [
        'key' => 'title',
        'label' => 'Product',
        'render' => function($product, $value) {
            $image = $product->images->where('is_primary', true)->first();
            $imageSrc = $image ? asset('storage/' . $image->image_path) : '/placeholder.jpg';
            return '
                <div class="flex items-center space-x-3">
                    <img src="' . $imageSrc . '" class="h-10 w-10 rounded object-cover">
                    <span class="font-medium">' . e($value) . '</span>
                </div>
            ';
        }
    ],
    ['key' => 'reserve_price', 'label' => 'Price', 'format' => 'currency', 'align' => 'right'],
    ['key' => 'status', 'label' => 'Status', 'align' => 'center'],
];
```

### Compact Table

```blade
<x-ui.table 
    :data="$bids" 
    :columns="$bidColumns"
    :compact="true"
    :striped="true"
    empty-message="No bids found"
/>
```

## Sorting Implementation

The table generates sort URLs automatically. In your controller, handle sorting like this:

```php
public function index(Request $request)
{
    $query = User::query();
    
    if ($request->has('sort')) {
        $direction = $request->get('direction', 'asc');
        $query->orderBy($request->get('sort'), $direction);
    }
    
    $users = $query->paginate(10);
    
    return view('users.index', compact('users'));
}
```

## Accessibility Features

- Sortable headers have `role="button"` and `tabindex="0"`
- ARIA sort indicators (`aria-sort="ascending|descending|none"`)
- Keyboard navigation support (Enter and Space keys)
- Screen reader friendly pagination
- Proper table semantics with `<thead>`, `<tbody>`, etc.

## Styling

The component uses Tailwind CSS with design system color variables:

- `bg-card` - Card background
- `text-card-foreground` - Card text
- `bg-muted` - Muted background
- `text-muted-foreground` - Muted text
- `bg-primary` - Primary background
- `text-primary-foreground` - Primary text
- `border-input` - Input border color

## JavaScript

The component includes minimal JavaScript for:
- Sort URL generation and navigation
- Keyboard event handling for sortable headers
- No external dependencies required

## Browser Support

- Modern browsers (Chrome, Firefox, Safari, Edge)
- Responsive design works on mobile devices
- Graceful degradation when JavaScript is disabled