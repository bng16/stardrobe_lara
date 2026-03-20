# Laravel + shadcn/ui + Bun Setup

This Laravel project is configured with shadcn/ui components, React, TypeScript, and Bun.

## What's Included

- **Laravel 12** - PHP framework
- **React 19** - Frontend library with TypeScript
- **Tailwind CSS v4** - Utility-first CSS framework
- **shadcn/ui** - Beautiful, accessible components
- **Bun** - Fast JavaScript runtime and package manager
- **Vite** - Build tool with HMR

## Project Structure

```
resources/
├── css/
│   └── app.css              # Tailwind CSS with shadcn/ui variables
├── js/
│   ├── components/
│   │   └── ui/              # shadcn/ui components
│   │       ├── button.tsx
│   │       └── card.tsx
│   ├── lib/
│   │   └── utils.ts         # Utility functions (cn helper)
│   ├── app.tsx              # Main React application
│   └── bootstrap.js         # Laravel bootstrap
└── views/
    └── welcome.blade.php    # Laravel view with React mount point
```

## Development

### Start Development Server
```bash
# Terminal 1: Laravel development server
php artisan serve

# Terminal 2: Vite development server
bun run dev
```

### Build for Production
```bash
bun run build
```

## Adding shadcn/ui Components

### Method 1: Manual Installation
1. Visit [shadcn/ui components](https://ui.shadcn.com/docs/components)
2. Choose a component (e.g., Input, Dialog, etc.)
3. Copy the component code
4. Create `resources/js/components/ui/[component-name].tsx`
5. Install any required dependencies: `bun add [package-name]`
6. Import and use in your React components

### Method 2: Helper Script
```bash
node add-shadcn-component.js input
```

## Configuration Files

- `components.json` - shadcn/ui configuration
- `tailwind.config.js` - Tailwind CSS configuration
- `tsconfig.json` - TypeScript configuration
- `vite.config.js` - Vite build configuration
- `postcss.config.js` - PostCSS configuration

## Available Components

Currently included:
- Button (with all variants)
- Card (with header, content, footer)

## CSS Variables

The setup includes CSS variables for theming:
- Light and dark mode support
- Customizable color scheme
- Consistent spacing and typography

## TypeScript Support

- Full TypeScript support for React components
- Type-safe shadcn/ui components
- Path aliases configured (`@/` points to `resources/js/`)

## Next Steps

1. **Add more components** as needed from shadcn/ui
2. **Create Laravel routes** and controllers for your API
3. **Build React pages** and components for your application
4. **Customize the theme** by modifying CSS variables
5. **Deploy** your application

## Useful Commands

```bash
# Install new dependencies
bun add [package-name]

# Install dev dependencies
bun add -D [package-name]

# Run Laravel commands
php artisan [command]

# Generate Laravel resources
php artisan make:controller [ControllerName]
php artisan make:model [ModelName]
php artisan make:migration [migration_name]
```

## Resources

- [shadcn/ui Documentation](https://ui.shadcn.com/)
- [Laravel Documentation](https://laravel.com/docs)
- [React Documentation](https://react.dev/)
- [Tailwind CSS Documentation](https://tailwindcss.com/)
- [Bun Documentation](https://bun.sh/docs)