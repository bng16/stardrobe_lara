# Blade Components

This directory contains all reusable Blade components for the application, organized by functionality and complexity.

## Directory Structure

```
components/
├── ui/                 # Basic UI components (buttons, cards, alerts)
├── forms/              # Form-related components (inputs, validation)
├── navigation/         # Navigation components (menus, breadcrumbs)
├── complex/            # Complex components (tables, modals, charts)
├── layout/             # Layout and structure components
├── utility/            # Utility and helper components
├── application-logo.blade.php
├── dropdown-link.blade.php
├── dropdown.blade.php
├── nav-link.blade.php
└── responsive-nav-link.blade.php
```

## Component Categories

### UI Components (`ui/`)
Basic building blocks that replace shadcn/ui components:
- Buttons, Cards, Alerts, Badges
- Loading states, Skeletons
- Typography components

### Form Components (`forms/`)
Form-related components for consistent form handling:
- Input fields, Textareas, Selects
- Form groups, Labels, Error messages
- Complex form components (multi-select, date picker)

### Navigation Components (`navigation/`)
Navigation and menu components:
- Navbar, Sidebar, Breadcrumbs
- Navigation items and dropdowns
- Admin-specific navigation

### Complex Components (`complex/`)
Advanced components combining multiple elements:
- Data tables, Modals, Tabs
- Charts, Statistics cards
- Auction and creator specific components

### Layout Components (`layout/`)
Page structure and layout components:
- Page headers, containers, grids
- Admin layouts, responsive layouts
- Section wrappers and content areas

### Utility Components (`utility/`)
Helper components for common functionality:
- Date/currency formatting
- Status indicators, Progress bars
- Empty states, Error boundaries

## Usage Conventions

### Component Naming
- Use kebab-case for component names
- Prefix with category: `<x-ui.button>`, `<x-forms.input>`
- Use descriptive names: `<x-complex.auction-card>`

### Props and Attributes
- Use camelCase for PHP props: `{{ $backgroundColor }}`
- Use kebab-case for HTML attributes: `data-test-id`
- Support both props and attributes: `{{ $attributes }}`

### Slots
- Use named slots for complex content: `<x-slot name="header">`
- Default slot for main content: `{{ $slot }}`
- Document available slots in component comments

### Styling
- Use Tailwind CSS classes consistently
- Support class merging with `{{ $attributes->merge(['class' => 'default-classes']) }}`
- Allow class overrides through attributes

## Example Usage

```blade
{{-- Basic UI component --}}
<x-ui.button variant="primary" size="lg" class="mb-4">
    Save Changes
</x-ui.button>

{{-- Form component with validation --}}
<x-forms.form action="{{ route('creators.store') }}">
    <x-forms.form-group>
        <x-forms.label for="name">Creator Name</x-forms.label>
        <x-forms.input name="name" :error="$errors->has('name')" />
        <x-forms.error name="name" />
    </x-forms.form-group>
</x-forms.form>

{{-- Complex component with slots --}}
<x-complex.modal id="confirm-delete">
    <x-slot name="title">Confirm Deletion</x-slot>
    <x-slot name="content">
        Are you sure you want to delete this item?
    </x-slot>
    <x-slot name="footer">
        <x-ui.button variant="secondary" data-dismiss="modal">Cancel</x-ui.button>
        <x-ui.button variant="destructive">Delete</x-ui.button>
    </x-slot>
</x-complex.modal>
```

## Development Guidelines

### Creating New Components
1. Choose appropriate directory based on component complexity
2. Create component file with descriptive name
3. Add props documentation in component comments
4. Include usage examples in README
5. Test component with various prop combinations

### Component Structure
```blade
{{-- Component: resources/views/components/ui/button.blade.php --}}
{{-- 
    Button component with multiple variants and sizes
    
    Props:
    - variant: string (default, primary, secondary, outline, destructive, ghost, link)
    - size: string (sm, default, lg)
    - type: string (button, submit, reset)
    - disabled: boolean
    - href: string (converts to link)
    
    Usage:
    <x-ui.button variant="primary" size="lg">Click me</x-ui.button>
--}}

@props([
    'variant' => 'default',
    'size' => 'default',
    'type' => 'button',
    'disabled' => false,
    'href' => null
])

{{-- Component implementation --}}
```

### Testing Components
- Test with various prop combinations
- Test with and without slots
- Test accessibility features
- Test responsive behavior
- Validate HTML output

## Migration from React Components

This component system replaces the React/shadcn components:

| React Component | Blade Component |
|----------------|----------------|
| `Button` | `<x-ui.button>` |
| `Card` | `<x-ui.card>` |
| `Input` | `<x-forms.input>` |
| `Table` | `<x-complex.table>` |
| `Modal` | `<x-complex.modal>` |

Each Blade component maintains the same visual design and functionality as its React counterpart while following Laravel Blade conventions.