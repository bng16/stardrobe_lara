# Form Components

This directory contains form-related components for building consistent forms throughout the application.

## Components

### Input Components
- `input.blade.php` - Text input component with validation states
- `textarea.blade.php` - Textarea component with validation states
- `select.blade.php` - Select dropdown component
- `checkbox.blade.php` - Checkbox input component
- `radio.blade.php` - Radio button component
- `file-input.blade.php` - File upload input component

### Form Structure Components
- `form.blade.php` - Form wrapper with CSRF protection
- `form-group.blade.php` - Form field group with label and error handling
- `label.blade.php` - Form label component
- `error.blade.php` - Form error message component
- `help-text.blade.php` - Form help text component

### Complex Form Components
- `multi-select.blade.php` - Multi-select dropdown component
- `date-picker.blade.php` - Date picker input component
- `image-upload.blade.php` - Image upload component with preview
- `form-wizard.blade.php` - Multi-step form wizard component

## Usage

Form components are designed to work together to create consistent, accessible forms:

```blade
<x-forms.form action="{{ route('admin.creators.store') }}" method="POST">
    <x-forms.form-group>
        <x-forms.label for="name">Creator Name</x-forms.label>
        <x-forms.input 
            name="name" 
            id="name" 
            value="{{ old('name') }}"
            :error="$errors->has('name')"
        />
        <x-forms.error name="name" />
    </x-forms.form-group>
    
    <x-ui.button type="submit">Create Creator</x-ui.button>
</x-forms.form>
```