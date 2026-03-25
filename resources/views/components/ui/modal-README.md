# Modal/Dialog Component

A comprehensive, accessible modal/dialog component for Laravel Blade templates with full keyboard navigation, focus management, and customizable styling.

## Features

- ✅ **Accessibility First**: Full ARIA support, focus management, and keyboard navigation
- ✅ **Multiple Sizes**: sm, md, lg, xl, and full-width options
- ✅ **Flexible Control**: Dismissible/non-dismissible modes
- ✅ **Backdrop Options**: Configurable backdrop with blur effects
- ✅ **Smooth Animations**: CSS transitions for open/close states
- ✅ **Focus Trapping**: Keeps focus within modal when open
- ✅ **Keyboard Support**: ESC to close, Tab navigation
- ✅ **Programmatic Control**: JavaScript API for modal management
- ✅ **Event System**: Custom events for modal state changes
- ✅ **Responsive Design**: Works on all screen sizes

## Basic Usage

```blade
{{-- Basic Modal --}}
<x-ui.modal 
    id="my_modal"
    title="Modal Title"
    description="Optional description"
    size="md"
    :dismissible="true">
    
    <p>Your modal content goes here.</p>
    
    <x-slot name="footer">
        <x-ui.button variant="outline" data-modal-close="my_modal">
            Cancel
        </x-ui.button>
        <x-ui.button variant="default">
            Save Changes
        </x-ui.button>
    </x-slot>
</x-ui.modal>

{{-- Trigger Button --}}
<x-ui.button data-modal-trigger="my_modal">
    Open Modal
</x-ui.button>
```

## Component Props

| Prop | Type | Default | Description |
|------|------|---------|-------------|
| `id` | string | 'modal' | Unique identifier for the modal |
| `size` | string | 'md' | Modal size: 'sm', 'md', 'lg', 'xl', 'full' |
| `dismissible` | boolean | true | Whether the modal can be dismissed |
| `show` | boolean | false | Whether to show the modal initially |
| `backdrop` | boolean | true | Whether to show backdrop overlay |
| `backdropBlur` | boolean | true | Whether to blur the backdrop |
| `closeOnBackdrop` | boolean | true | Whether clicking backdrop closes modal |
| `closeOnEscape` | boolean | true | Whether Escape key closes modal |
| `title` | string | null | Modal title text |
| `description` | string | null | Modal description text |

## Size Options

- **sm**: `max-w-sm` (24rem) - For simple confirmations
- **md**: `max-w-lg` (32rem) - Default size for most modals
- **lg**: `max-w-4xl` (56rem) - For forms and detailed content
- **xl**: `max-w-6xl` (72rem) - For complex layouts
- **full**: `max-w-full mx-4` - Full width with margins

## Advanced Usage

### Non-dismissible Modal

```blade
<x-ui.modal 
    id="confirm_modal"
    title="Confirm Action"
    :dismissible="false"
    :closeOnBackdrop="false"
    :closeOnEscape="false">
    
    <p>This action cannot be undone. Are you sure?</p>
    
    <x-slot name="footer">
        <x-ui.button variant="outline" onclick="closeModal('confirm_modal')">
            Cancel
        </x-ui.button>
        <x-ui.button variant="destructive" onclick="handleConfirm()">
            Delete
        </x-ui.button>
    </x-slot>
</x-ui.modal>
```

### Form Modal

```blade
<x-ui.modal 
    id="user_form_modal"
    title="Create User"
    size="lg">
    
    <form id="user-form" method="POST" action="{{ route('users.store') }}">
        @csrf
        <div class="space-y-4">
            <div>
                <label for="name" class="block text-sm font-medium text-gray-700">Name</label>
                <x-ui.input id="name" name="name" type="text" required />
            </div>
            <div>
                <label for="email" class="block text-sm font-medium text-gray-700">Email</label>
                <x-ui.input id="email" name="email" type="email" required />
            </div>
        </div>
    </form>
    
    <x-slot name="footer">
        <x-ui.button variant="outline" data-modal-close="user_form_modal">
            Cancel
        </x-ui.button>
        <x-ui.button type="submit" form="user-form">
            Create User
        </x-ui.button>
    </x-slot>
</x-ui.modal>
```

### Using Separate Header/Body/Footer Components

```blade
<x-ui.modal id="custom_modal" :dismissible="false">
    <x-ui.modal-header 
        title="Custom Header" 
        description="With separate components"
        :dismissible="true"
        modalId="custom_modal" />
    
    <x-ui.modal-body>
        <p>Custom body content with separate component.</p>
    </x-ui.modal-body>
    
    <x-ui.modal-footer>
        <x-ui.button variant="outline" data-modal-close="custom_modal">
            Close
        </x-ui.button>
    </x-ui.modal-footer>
</x-ui.modal>
```

## JavaScript API

### Global Functions

```javascript
// Open a modal
openModal('modal_id');

// Close a modal
closeModal('modal_id');

// Toggle a modal
toggleModal('modal_id');
```

### Event Listeners

```javascript
// Listen for modal events
document.addEventListener('modal:open', function(e) {
    console.log('Modal opened:', e.detail.modalId);
});

document.addEventListener('modal:close', function(e) {
    console.log('Modal closed:', e.detail.modalId);
});
```

### Trigger Attributes

```blade
{{-- Open modal on click --}}
<x-ui.button data-modal-trigger="my_modal">Open Modal</x-ui.button>

{{-- Close modal on click --}}
<x-ui.button data-modal-close="my_modal">Close Modal</x-ui.button>
```

## Accessibility Features

### ARIA Attributes
- `role="dialog"` on modal container
- `aria-modal="true"` for screen readers
- `aria-labelledby` linking to title
- `aria-describedby` linking to description
- `aria-label` on close button

### Keyboard Navigation
- **Tab**: Navigate through focusable elements
- **Shift+Tab**: Navigate backwards
- **Escape**: Close modal (if dismissible)
- **Focus trapping**: Focus stays within modal

### Focus Management
- Automatically focuses first focusable element when opened
- Restores focus to trigger element when closed
- Prevents focus from leaving modal while open

## Styling Customization

The modal uses Tailwind CSS classes and can be customized by:

1. **CSS Custom Properties**: Override color variables
2. **Class Overrides**: Pass additional classes via attributes
3. **Component Modification**: Edit the component files directly

### Custom Styling Example

```blade
<x-ui.modal 
    id="custom_styled_modal"
    class="custom-modal-styles"
    title="Custom Styled Modal">
    
    <div class="custom-content">
        <!-- Your content -->
    </div>
</x-ui.modal>
```

## Browser Support

- Chrome 60+
- Firefox 55+
- Safari 12+
- Edge 79+

## Performance Considerations

- Modals are initialized on page load but remain hidden
- JavaScript event listeners are efficiently managed
- CSS transitions provide smooth animations
- Focus management is optimized for performance

## Common Patterns

### Confirmation Dialog

```blade
<x-ui.modal 
    id="delete_confirmation"
    title="Delete Item"
    description="This action cannot be undone."
    size="sm"
    :dismissible="false">
    
    <div class="bg-red-50 border border-red-200 rounded p-3">
        <p class="text-red-800 text-sm">
            Are you sure you want to delete this item?
        </p>
    </div>
    
    <x-slot name="footer">
        <x-ui.button variant="outline" onclick="closeModal('delete_confirmation')">
            Cancel
        </x-ui.button>
        <x-ui.button variant="destructive" onclick="handleDelete()">
            Delete
        </x-ui.button>
    </x-slot>
</x-ui.modal>
```

### Loading Modal

```blade
<x-ui.modal 
    id="loading_modal"
    title="Processing..."
    :dismissible="false"
    size="sm">
    
    <div class="flex items-center justify-center py-8">
        <div class="animate-spin rounded-full h-8 w-8 border-b-2 border-blue-600"></div>
        <span class="ml-3 text-gray-600">Please wait...</span>
    </div>
</x-ui.modal>
```

### Multi-step Form Modal

```blade
<x-ui.modal 
    id="wizard_modal"
    title="Setup Wizard"
    size="lg"
    :dismissible="false">
    
    <div id="wizard-content">
        <!-- Dynamic content loaded via JavaScript -->
    </div>
    
    <x-slot name="footer">
        <x-ui.button id="wizard-prev" variant="outline" style="display: none;">
            Previous
        </x-ui.button>
        <x-ui.button id="wizard-next" variant="default">
            Next
        </x-ui.button>
    </x-slot>
</x-ui.modal>
```

## Troubleshooting

### Modal Not Opening
- Check that the modal ID matches the trigger's `data-modal-trigger`
- Ensure JavaScript is loaded and running
- Verify no JavaScript errors in console

### Focus Issues
- Make sure focusable elements exist within the modal
- Check that the modal is properly structured
- Verify ARIA attributes are correctly set

### Styling Problems
- Ensure Tailwind CSS is properly loaded
- Check for conflicting CSS rules
- Verify component classes are not being overridden

### Performance Issues
- Limit the number of modals on a single page
- Use lazy loading for modal content when possible
- Optimize images and content within modals

## Examples

See `modal-examples.blade.php` for comprehensive examples of all modal features and use cases.