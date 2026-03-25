<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Button Component Test</title>
    @vite(['resources/css/app.css'])
</head>
<body class="p-8 space-y-4">
    <h1 class="text-2xl font-bold mb-6">Button Component Test</h1>
    
    <div class="space-y-4">
        <h2 class="text-lg font-semibold">Variants</h2>
        <div class="flex gap-4 flex-wrap">
            <x-ui.button>Default</x-ui.button>
            <x-ui.button variant="secondary">Secondary</x-ui.button>
            <x-ui.button variant="outline">Outline</x-ui.button>
            <x-ui.button variant="destructive">Destructive</x-ui.button>
            <x-ui.button variant="ghost">Ghost</x-ui.button>
            <x-ui.button variant="link">Link</x-ui.button>
        </div>
    </div>
    
    <div class="space-y-4">
        <h2 class="text-lg font-semibold">Sizes</h2>
        <div class="flex gap-4 items-center flex-wrap">
            <x-ui.button size="sm">Small</x-ui.button>
            <x-ui.button>Default</x-ui.button>
            <x-ui.button size="lg">Large</x-ui.button>
            <x-ui.button size="icon">🔥</x-ui.button>
        </div>
    </div>
    
    <div class="space-y-4">
        <h2 class="text-lg font-semibold">States</h2>
        <div class="flex gap-4 flex-wrap">
            <x-ui.button>Normal</x-ui.button>
            <x-ui.button disabled>Disabled</x-ui.button>
        </div>
    </div>
    
    <div class="space-y-4">
        <h2 class="text-lg font-semibold">Types</h2>
        <div class="flex gap-4 flex-wrap">
            <x-ui.button type="button">Button</x-ui.button>
            <x-ui.button type="submit">Submit</x-ui.button>
            <x-ui.button type="reset">Reset</x-ui.button>
        </div>
    </div>
    
    <div class="space-y-4">
        <h2 class="text-lg font-semibold">As Link</h2>
        <div class="flex gap-4 flex-wrap">
            <x-ui.button href="#test">Link Button</x-ui.button>
            <x-ui.button href="#test" variant="outline">Outline Link</x-ui.button>
            <x-ui.button href="#test" disabled>Disabled Link</x-ui.button>
        </div>
    </div>
</body>
</html>