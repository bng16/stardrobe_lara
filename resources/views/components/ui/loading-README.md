# Loading/Spinner Component

A flexible loading component that provides visual feedback during loading states with multiple variants, sizes, and colors.

## Features

- **Multiple Variants**: Spinner, dots, and bars animations
- **Flexible Sizing**: Small (sm), medium (md), large (lg), and extra-large (xl)
- **Color Options**: Primary, secondary, white, success, warning, and danger
- **Loading Text**: Optional text display alongside the loading indicator
- **Display Modes**: Inline and block display options
- **Accessibility**: Full ARIA support and screen reader compatibility
- **Conditional Rendering**: Show/hide based on loading state

## Props

| Prop | Type | Default | Description |
|------|------|---------|-------------|
| `variant` | string | `'spinner'` | Loading animation type: `'spinner'`, `'dots'`, `'bars'` |
| `size` | string | `'md'` | Size of the loading indicator: `'sm'`, `'md'`, `'lg'`, `'xl'` |
| `color` | string | `'primary'` | Color theme: `'primary'`, `'secondary'`, `'white'`, `'success'`, `'warning'`, `'danger'` |
| `text` | string | `null` | Optional loading text to display |
| `inline` | boolean | `false` | Whether to display inline or as block element |
| `show` | boolean | `true` | Whether to show the loading indicator |

## Usage Examples

### Basic Spinner
```blade
<x-ui.loading />
```

### Spinner with Text
```blade
<x-ui.loading text="Loading data..." />
```

### Different Variants
```blade
{{-- Spinning circle --}}
<x-ui.loading variant="spinner" />

{{-- Bouncing dots --}}
<x-ui.loading variant="dots" />

{{-- Scaling bars --}}
<x-ui.loading variant="bars" />
```

### Different Sizes
```blade
<x-ui.loading size="sm" text="Small" />
<x-ui.loading size="md" text="Medium" />
<x-ui.loading size="lg" text="Large" />
<x-ui.loading size="xl" text="Extra Large" />
```

### Different Colors
```blade
<x-ui.loading color="primary" text="Primary" />
<x-ui.loading color="secondary" text="Secondary" />
<x-ui.loading color="success" text="Success" />
<x-ui.loading color="warning" text="Warning" />
<x-ui.loading color="danger" text="Danger" />
<x-ui.loading color="white" text="White" />
```

### Inline Display
```blade
<p>Please wait <x-ui.loading inline size="sm" /> while we process your request.</p>
```

### Conditional Display
```blade
<x-ui.loading :show="$isLoading" text="Processing..." />
```

### In Forms
```blade
<form method="POST" action="/submit">
    @csrf
    <div class="space-y-4">
        <x-ui.input name="email" placeholder="Email" />
        <x-ui.button type="submit" id="submit-btn">
            Submit
        </x-ui.button>
        <x-ui.loading id="form-loading" :show="false" text="Submitting..." />
    </div>
</form>

<script>
document.getElementById('submit-btn').addEventListener('click', function() {
    document.getElementById('form-loading').style.display = 'flex';
});
</script>
```

### In Cards
```blade
<x-ui.card>
    <x-ui.card-header>
        <h3>Dashboard Statistics</h3>
    </x-ui.card-header>
    <x-ui.card-content>
        @if($loading)
            <div class="py-8">
                <x-ui.loading text="Loading statistics..." />
            </div>
        @else
            {{-- Statistics content --}}
        @endif
    </x-ui.card-content>
</x-ui.card>
```

### AJAX Loading States
```blade
<div id="content-area">
    {{-- Content will be loaded here --}}
</div>

<x-ui.loading id="ajax-loader" :show="false" text="Loading content..." />

<script>
function loadContent() {
    const loader = document.getElementById('ajax-loader');
    const content = document.getElementById('content-area');
    
    // Show loader
    loader.style.display = 'flex';
    content.style.display = 'none';
    
    fetch('/api/content')
        .then(response => response.text())
        .then(html => {
            content.innerHTML = html;
            content.style.display = 'block';
            loader.style.display = 'none';
        })
        .catch(error => {
            console.error('Error:', error);
            loader.style.display = 'none';
        });
}
</script>
```

## Accessibility Features

- **ARIA Attributes**: Includes `role="status"` and `aria-live="polite"`
- **Screen Reader Support**: Hidden text for screen readers
- **Semantic Labels**: Descriptive `aria-label` attributes
- **Focus Management**: Doesn't interfere with keyboard navigation

## Animation Details

### Spinner Variant
- Uses CSS `animate-spin` class for smooth rotation
- SVG-based for crisp rendering at all sizes
- Partial opacity for visual depth

### Dots Variant
- Three dots with staggered bounce animation
- Uses CSS `animate-bounce` with different delays
- Circular dots that scale with size

### Bars Variant
- Three vertical bars with pulse animation
- Uses CSS `animate-pulse` with staggered timing
- Bars align to bottom for consistent baseline

## Styling Customization

The component uses Tailwind CSS classes and can be customized by:

1. **Adding Custom Classes**:
```blade
<x-ui.loading class="my-custom-class" />
```

2. **Overriding Colors**:
```blade
<x-ui.loading class="text-purple-600" />
```

3. **Custom Animations** (via CSS):
```css
.custom-loading .animate-spin {
    animation-duration: 2s;
}
```

## Performance Considerations

- Component only renders when `show` prop is `true`
- Uses CSS animations for optimal performance
- SVG icons are inline to avoid additional HTTP requests
- Minimal DOM footprint with semantic markup

## Browser Support

- All modern browsers (Chrome, Firefox, Safari, Edge)
- CSS animations gracefully degrade in older browsers
- SVG support required for spinner variant (IE9+)