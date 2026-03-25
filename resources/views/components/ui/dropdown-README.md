# Dropdown Component

A flexible and accessible dropdown/menu component for Laravel Blade templates. This component provides a trigger element and a floating menu with support for different positioning, alignment, keyboard navigation, and accessibility features.

## Components

- `dropdown.blade.php` - Main dropdown container component
- `dropdown-item.blade.php` - Individual menu item component
- `dropdown-separator.blade.php` - Visual separator between menu items
- `dropdown-label.blade.php` - Section label for grouping menu items

## Features

- **Multiple Trigger Types**: Click or hover activation
- **Flexible Positioning**: Top, bottom, left, right positioning
- **Alignment Options**: Left, center, right alignment
- **Keyboard Navigation**: Full arrow key navigation, Enter/Space activation, Escape to close
- **Accessibility**: ARIA attributes, focus management, screen reader support
- **Click Outside to Close**: Automatic closing when clicking outside
- **Customizable Styling**: Tailwind CSS classes with variant support
- **Icon Support**: Icons in menu items with proper spacing
- **Keyboard Shortcuts**: Display keyboard shortcuts in menu items
- **Disabled States**: Support for disabled dropdowns and menu items
- **Smooth Animations**: CSS transitions for open/close states

## Basic Usage

### Simple Dropdown

```blade
<x-ui.dropdown id="my-dropdown">
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
```

### Dropdown with Icons

```blade
<x-ui.dropdown id="icon-dropdown">
    <x-slot name="trigger">
        <x-ui.button variant="outline">Account</x-ui.button>
    </x-slot>
    
    <x-ui.dropdown-item href="/profile">
        <x-slot name="icon">
            <svg fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
            </svg>
        </x-slot>
        My Profile
    </x-ui.dropdown-item>
    
    <x-ui.dropdown-item href="/settings">
        <x-slot name="icon">
            <svg fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z" />
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
            </svg>
        </x-slot>
        Settings
    </x-ui.dropdown-item>
</x-ui.dropdown>
```

### Dropdown with Keyboard Shortcuts

```blade
<x-ui.dropdown id="shortcut-dropdown">
    <x-slot name="trigger">
        <x-ui.button variant="outline">Actions</x-ui.button>
    </x-slot>
    
    <x-ui.dropdown-item href="/new" shortcut="⌘N">
        <x-slot name="icon">
            <svg fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6" />
            </svg>
        </x-slot>
        New Item
    </x-ui.dropdown-item>
    
    <x-ui.dropdown-item href="/copy" shortcut="⌘C">Copy</x-ui.dropdown-item>
    <x-ui.dropdown-item href="/paste" shortcut="⌘V">Paste</x-ui.dropdown-item>
</x-ui.dropdown>
```

### Dropdown with Labels and Separators

```blade
<x-ui.dropdown id="labeled-dropdown">
    <x-slot name="trigger">
        <x-ui.button variant="outline">More Options</x-ui.button>
    </x-slot>
    
    <x-ui.dropdown-label>Account</x-ui.dropdown-label>
    <x-ui.dropdown-item href="/profile">Profile</x-ui.dropdown-item>
    <x-ui.dropdown-item href="/billing">Billing</x-ui.dropdown-item>
    
    <x-ui.dropdown-separator />
    
    <x-ui.dropdown-label>Support</x-ui.dropdown-label>
    <x-ui.dropdown-item href="/docs">Documentation</x-ui.dropdown-item>
    <x-ui.dropdown-item href="/support">Contact Support</x-ui.dropdown-item>
    
    <x-ui.dropdown-separator />
    
    <x-ui.dropdown-item href="/logout" destructive>Sign Out</x-ui.dropdown-item>
</x-ui.dropdown>
```

## Component Props

### Dropdown Component

| Prop | Type | Default | Description |
|------|------|---------|-------------|
| `id` | string | 'dropdown' | Unique identifier for the dropdown |
| `align` | string | 'left' | Menu alignment: 'left', 'right', 'center' |
| `position` | string | 'bottom' | Menu position: 'top', 'bottom', 'left', 'right' |
| `trigger` | string | 'click' | Trigger type: 'click', 'hover' |
| `width` | string | 'auto' | Menu width: 'auto', 'sm', 'md', 'lg', 'xl', 'full' |
| `offset` | number | 8 | Distance between trigger and menu (in Tailwind spacing units) |
| `closeOnClick` | boolean | true | Whether to close dropdown when menu item is clicked |
| `disabled` | boolean | false | Whether the dropdown is disabled |

### Dropdown Item Component

| Prop | Type | Default | Description |
|------|------|---------|-------------|
| `href` | string | null | URL to navigate to (renders as link if provided) |
| `disabled` | boolean | false | Whether the item is disabled |
| `destructive` | boolean | false | Whether to style as destructive action (red color) |
| `icon` | slot | null | Icon to display before the text |
| `shortcut` | string | null | Keyboard shortcut to display |
| `noClose` | boolean | false | Prevent dropdown from closing when this item is clicked |

### Dropdown Separator Component

| Prop | Type | Default | Description |
|------|------|---------|-------------|
| `class` | string | '' | Additional CSS classes |

### Dropdown Label Component

| Prop | Type | Default | Description |
|------|------|---------|-------------|
| `class` | string | '' | Additional CSS classes |

## Positioning and Alignment

### Position Options

- `bottom` (default): Menu appears below the trigger
- `top`: Menu appears above the trigger
- `left`: Menu appears to the left of the trigger
- `right`: Menu appears to the right of the trigger

### Alignment Options

- `left` (default): Menu aligns to the left edge of the trigger
- `right`: Menu aligns to the right edge of the trigger
- `center`: Menu centers relative to the trigger

### Width Options

- `auto` (default): Menu width adjusts to content with minimum width
- `sm`: 192px (w-48)
- `md`: 224px (w-56)
- `lg`: 256px (w-64)
- `xl`: 288px (w-72)
- `full`: Full width with margin

## Examples

### Right-Aligned Dropdown

```blade
<x-ui.dropdown id="right-dropdown" align="right">
    <x-slot name="trigger">
        <x-ui.button variant="outline">Right Aligned</x-ui.button>
    </x-slot>
    <x-ui.dropdown-item href="#item1">Item 1</x-ui.dropdown-item>
    <x-ui.dropdown-item href="#item2">Item 2</x-ui.dropdown-item>
</x-ui.dropdown>
```

### Top-Positioned Dropdown

```blade
<x-ui.dropdown id="top-dropdown" position="top">
    <x-slot name="trigger">
        <x-ui.button variant="outline">Top Position</x-ui.button>
    </x-slot>
    <x-ui.dropdown-item href="#item1">Item 1</x-ui.dropdown-item>
    <x-ui.dropdown-item href="#item2">Item 2</x-ui.dropdown-item>
</x-ui.dropdown>
```

### Hover-Triggered Dropdown

```blade
<x-ui.dropdown id="hover-dropdown" trigger="hover">
    <x-slot name="trigger">
        <x-ui.button variant="outline">Hover Me</x-ui.button>
    </x-slot>
    <x-ui.dropdown-item href="#item1">Item 1</x-ui.dropdown-item>
    <x-ui.dropdown-item href="#item2">Item 2</x-ui.dropdown-item>
</x-ui.dropdown>
```

### Large Width Dropdown

```blade
<x-ui.dropdown id="large-dropdown" width="lg">
    <x-slot name="trigger">
        <x-ui.button variant="outline">Large Menu</x-ui.button>
    </x-slot>
    <x-ui.dropdown-item href="#item1">Item with longer text content</x-ui.dropdown-item>
    <x-ui.dropdown-item href="#item2">Another item with more text</x-ui.dropdown-item>
</x-ui.dropdown>
```

### Disabled Dropdown

```blade
<x-ui.dropdown id="disabled-dropdown" disabled>
    <x-slot name="trigger">
        <x-ui.button variant="outline" disabled>Disabled</x-ui.button>
    </x-slot>
    <x-ui.dropdown-item href="#item1">Item 1</x-ui.dropdown-item>
    <x-ui.dropdown-item href="#item2">Item 2</x-ui.dropdown-item>
</x-ui.dropdown>
```

### Dropdown with Disabled Items

```blade
<x-ui.dropdown id="mixed-dropdown">
    <x-slot name="trigger">
        <x-ui.button variant="outline">Mixed States</x-ui.button>
    </x-slot>
    <x-ui.dropdown-item href="#available">Available Item</x-ui.dropdown-item>
    <x-ui.dropdown-item disabled>Disabled Item</x-ui.dropdown-item>
    <x-ui.dropdown-item href="#another">Another Available Item</x-ui.dropdown-item>
</x-ui.dropdown>
```

## Keyboard Navigation

The dropdown component supports full keyboard navigation:

- **Tab**: Focus the trigger button
- **Enter/Space**: Open/close the dropdown
- **Arrow Down**: Open dropdown and focus first item, or move to next item
- **Arrow Up**: Open dropdown and focus last item, or move to previous item
- **Home**: Focus first menu item
- **End**: Focus last menu item
- **Escape**: Close dropdown and return focus to trigger
- **Enter/Space**: Activate focused menu item
- **Tab**: Close dropdown and move focus to next element

## Accessibility Features

- **ARIA Attributes**: Proper `role`, `aria-haspopup`, `aria-expanded`, and `aria-labelledby` attributes
- **Focus Management**: Focus is properly managed when opening/closing
- **Keyboard Navigation**: Full keyboard support for all interactions
- **Screen Reader Support**: Proper semantic markup and labels
- **Focus Trap**: Focus stays within the dropdown when navigating with keyboard
- **High Contrast**: Works with high contrast mode and custom themes

## JavaScript API

The dropdown component provides global JavaScript functions for programmatic control:

```javascript
// Open a specific dropdown
window.openDropdown('my-dropdown-id');

// Close a specific dropdown
window.closeDropdown('my-dropdown-id');

// Toggle a specific dropdown
window.toggleDropdown('my-dropdown-id');
```

## Events

The dropdown component dispatches custom events:

```javascript
// Listen for dropdown open event
document.addEventListener('dropdown:open', function(e) {
    console.log('Dropdown opened:', e.detail.dropdownId);
});

// Listen for dropdown close event
document.addEventListener('dropdown:close', function(e) {
    console.log('Dropdown closed:', e.detail.dropdownId);
});
```

## Styling

The dropdown component uses Tailwind CSS classes and can be customized by:

1. **Adding custom classes**: Pass additional classes through the `class` attribute
2. **Modifying component files**: Edit the component files directly for global changes
3. **CSS overrides**: Use CSS to override specific styles

### Custom Styling Example

```blade
<x-ui.dropdown id="custom-dropdown" class="my-custom-dropdown">
    <x-slot name="trigger">
        <button class="custom-trigger-button">Custom Trigger</button>
    </x-slot>
    <x-ui.dropdown-item href="#item1" class="custom-menu-item">Custom Item</x-ui.dropdown-item>
</x-ui.dropdown>
```

## Browser Support

The dropdown component works in all modern browsers:

- Chrome 60+
- Firefox 55+
- Safari 12+
- Edge 79+

## Performance Considerations

- **Lazy Initialization**: Dropdowns are only initialized when the page loads
- **Event Delegation**: Uses efficient event delegation for better performance
- **CSS Transitions**: Uses CSS transitions instead of JavaScript animations
- **Memory Management**: Properly cleans up event listeners and observers

## Common Patterns

### User Account Menu

```blade
<x-ui.dropdown id="user-menu" align="right">
    <x-slot name="trigger">
        <button class="flex items-center space-x-2 text-sm rounded-full focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
            <img class="h-8 w-8 rounded-full" src="{{ auth()->user()->avatar }}" alt="{{ auth()->user()->name }}">
            <span>{{ auth()->user()->name }}</span>
            <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
            </svg>
        </button>
    </x-slot>
    
    <x-ui.dropdown-item href="{{ route('profile') }}">
        <x-slot name="icon">
            <svg fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
            </svg>
        </x-slot>
        Your Profile
    </x-ui.dropdown-item>
    
    <x-ui.dropdown-item href="{{ route('settings') }}">
        <x-slot name="icon">
            <svg fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z" />
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
            </svg>
        </x-slot>
        Settings
    </x-ui.dropdown-item>
    
    <x-ui.dropdown-separator />
    
    <form method="POST" action="{{ route('logout') }}">
        @csrf
        <x-ui.dropdown-item onclick="this.closest('form').submit(); return false;" destructive>
            <x-slot name="icon">
                <svg fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1" />
                </svg>
            </x-slot>
            Sign out
        </x-ui.dropdown-item>
    </form>
</x-ui.dropdown>
```

### Action Menu

```blade
<x-ui.dropdown id="action-menu">
    <x-slot name="trigger">
        <x-ui.button variant="ghost" size="icon">
            <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 5v.01M12 12v.01M12 19v.01M12 6a1 1 0 110-2 1 1 0 010 2zm0 7a1 1 0 110-2 1 1 0 010 2zm0 7a1 1 0 110-2 1 1 0 010 2z" />
            </svg>
        </x-ui.button>
    </x-slot>
    
    <x-ui.dropdown-item href="{{ route('items.edit', $item) }}">Edit</x-ui.dropdown-item>
    <x-ui.dropdown-item href="{{ route('items.duplicate', $item) }}">Duplicate</x-ui.dropdown-item>
    <x-ui.dropdown-separator />
    <x-ui.dropdown-item href="{{ route('items.archive', $item) }}">Archive</x-ui.dropdown-item>
    <x-ui.dropdown-item 
        onclick="if(confirm('Are you sure?')) { document.getElementById('delete-form-{{ $item->id }}').submit(); }" 
        destructive>
        Delete
    </x-ui.dropdown-item>
</x-ui.dropdown>
```

This dropdown component provides a complete, accessible, and flexible solution for dropdown menus in Laravel Blade applications, following modern web standards and best practices.