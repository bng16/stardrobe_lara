# UI Components

This directory contains basic UI components that replace the shadcn/ui components from the React implementation.

## Components

### Basic Components
- `button.blade.php` - Button component with variants (default, secondary, outline, destructive, ghost, link)
- `card.blade.php` - Card container component with rounded corners, border, and shadow
- `card-header.blade.php` - Card header section with flex layout and padding
- `card-content.blade.php` - Card content section with appropriate padding

### Form Components
- `input.blade.php` - Text input component with error states and form repopulation
- `textarea.blade.php` - Textarea component with configurable rows and error states
- `select.blade.php` - Select dropdown component with options and option groups

### Complex Components
- `table.blade.php` - Data table component with sorting, pagination, and filtering
- `modal.blade.php` - Modal dialog component with backdrop and focus management
- `dropdown.blade.php` - Dropdown/menu component with keyboard navigation and accessibility
- `dropdown-item.blade.php` - Individual dropdown menu item
- `dropdown-separator.blade.php` - Visual separator for dropdown menus
- `dropdown-label.blade.php` - Section label for dropdown menus
- `loading.blade.php` - Loading/spinner component with multiple variants and accessibility support

## Usage

All components follow Laravel Blade component conventions and can be used with the `<x-ui.component-name>` syntax.

### Button Examples
```blade
<x-ui.button variant="primary" size="lg">
    Click me
</x-ui.button>

<x-ui.button href="/dashboard" variant="outline">
    Go to Dashboard
</x-ui.button>
```

### Card Examples
```blade
<x-ui.card>
    <x-ui.card-header>
        <h3>Card Title</h3>
    </x-ui.card-header>
    <x-ui.card-content>
        <p>Card content goes here</p>
    </x-ui.card-content>
</x-ui.card>
```

### Dropdown Examples
```blade
{{-- Basic Dropdown --}}
<x-ui.dropdown id="user-menu">
    <x-slot name="trigger">
        <x-ui.button variant="outline">
            Options
            <svg class="ml-2 h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
            </svg>
        </x-ui.button>
    </x-slot>
    
    <x-ui.dropdown-item href="/profile">View Profile</x-ui.dropdown-item>
    <x-ui.dropdown-item href="/settings">Settings</x-ui.dropdown-item>
    <x-ui.dropdown-separator />
    <x-ui.dropdown-item href="/logout" destructive>Sign Out</x-ui.dropdown-item>
</x-ui.dropdown>

{{-- Dropdown with Icons and Shortcuts --}}
<x-ui.dropdown id="actions-menu" align="right">
    <x-slot name="trigger">
        <x-ui.button variant="outline">Actions</x-ui.button>
    </x-slot>
    
    <x-ui.dropdown-label>File</x-ui.dropdown-label>
    <x-ui.dropdown-item href="/new" shortcut="⌘N">
        <x-slot name="icon">
            <svg fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6" />
            </svg>
        </x-slot>
        New Item
    </x-ui.dropdown-item>
    
    <x-ui.dropdown-separator />
    
    <x-ui.dropdown-item href="/delete" destructive shortcut="⌫">Delete</x-ui.dropdown-item>
</x-ui.dropdown>
```
```blade
{{-- Input Component --}}
<x-ui.input 
    name="email" 
    type="email" 
    placeholder="Enter your email" 
    required 
    :error="$errors->has('email')" />

{{-- Textarea Component --}}
<x-ui.textarea 
    name="description" 
    placeholder="Enter description" 
    rows="4"
    :error="$errors->has('description')" />

{{-- Select Component with Options Array --}}
<x-ui.select 
    name="country" 
    placeholder="Select country"
    :options="[
        'us' => 'United States',
        'ca' => 'Canada',
        'uk' => 'United Kingdom'
    ]"
    :error="$errors->has('country')" />

{{-- Select Component with Option Groups --}}
<x-ui.select 
    name="category"
    :options="[
        'Electronics' => [
            'phones' => 'Phones',
            'laptops' => 'Laptops'
        ],
        'Clothing' => [
            'shirts' => 'Shirts',
            'pants' => 'Pants'
        ]
    ]" />

{{-- Select Component with Custom Options --}}
<x-ui.select name="status" value="active">
    <option value="active">Active</option>
    <option value="inactive">Inactive</option>
    <option value="pending">Pending</option>
</x-ui.select>
```

## Form Component Features

### Input Component
- **Props**: `type`, `name`, `id`, `value`, `placeholder`, `disabled`, `required`, `readonly`, `error`
- **Features**: 
  - Automatic form repopulation with `old()` helper
  - Error state styling with red border
  - Support for all HTML input types
  - Accessibility attributes support

### Textarea Component
- **Props**: `name`, `id`, `value`, `placeholder`, `disabled`, `required`, `readonly`, `rows`, `error`
- **Features**:
  - Automatic form repopulation with `old()` helper
  - Error state styling with red border
  - Configurable number of rows
  - Accessibility attributes support

### Select Component
- **Props**: `name`, `id`, `value`, `disabled`, `required`, `placeholder`, `options`, `error`
- **Features**:
  - Automatic form repopulation with `old()` helper
  - Error state styling with red border
  - Support for option arrays and option groups
  - Custom options via slot content
  - Placeholder option support

## Error Handling

All form components support error states by passing the `error` prop:

```blade
<x-ui.input 
    name="email" 
    :error="$errors->has('email')" />

@error('email')
    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
@enderror
```

## Accessibility

All form components include proper accessibility features:
- Automatic ID generation from name attribute
- Support for `aria-*` attributes
- Proper focus states and keyboard navigation
- Screen reader friendly markup

### Loading Examples
```blade
{{-- Basic loading spinner --}}
<x-ui.loading />

{{-- Loading with text --}}
<x-ui.loading text="Loading data..." />

{{-- Different variants --}}
<x-ui.loading variant="spinner" />
<x-ui.loading variant="dots" />
<x-ui.loading variant="bars" />

{{-- Different sizes --}}
<x-ui.loading size="sm" />
<x-ui.loading size="md" />
<x-ui.loading size="lg" />
<x-ui.loading size="xl" />

{{-- Different colors --}}
<x-ui.loading color="primary" />
<x-ui.loading color="secondary" />
<x-ui.loading color="success" />
<x-ui.loading color="warning" />
<x-ui.loading color="danger" />
<x-ui.loading color="white" />

{{-- Inline usage --}}
<p>Please wait <x-ui.loading inline size="sm" /> while processing...</p>

{{-- Conditional display --}}
<x-ui.loading :show="$isLoading" text="Processing..." />
```

## Dropdown Component

The dropdown component provides a flexible menu system with full keyboard navigation and accessibility support. See `dropdown-README.md` for complete documentation.

### Basic Dropdown Usage
```blade
<x-ui.dropdown id="user-menu">
    <x-slot name="trigger">
        <x-ui.button variant="outline">Options</x-ui.button>
    </x-slot>
    
    <x-ui.dropdown-item href="/profile">View Profile</x-ui.dropdown-item>
    <x-ui.dropdown-item href="/settings">Settings</x-ui.dropdown-item>
    <x-ui.dropdown-separator />
    <x-ui.dropdown-item href="/logout" destructive>Sign Out</x-ui.dropdown-item>
</x-ui.dropdown>
```

### Dropdown Features
- **Multiple Trigger Types**: Click or hover activation
- **Flexible Positioning**: Top, bottom, left, right positioning  
- **Alignment Options**: Left, center, right alignment
- **Keyboard Navigation**: Full arrow key navigation and shortcuts
- **Accessibility**: ARIA attributes and focus management
- **Icon Support**: Icons in menu items with proper spacing
- **Disabled States**: Support for disabled dropdowns and items