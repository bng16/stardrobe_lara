#!/usr/bin/env node

/**
 * Simple script to help add shadcn/ui components to Laravel + Bun setup
 * Usage: node add-shadcn-component.js <component-name>
 * 
 * This is a basic helper - for full functionality, you can manually copy
 * components from https://ui.shadcn.com/docs/components
 */

const componentName = process.argv[2];

if (!componentName) {
  console.log('Usage: node add-shadcn-component.js <component-name>');
  console.log('Example: node add-shadcn-component.js input');
  console.log('');
  console.log('Available components at: https://ui.shadcn.com/docs/components');
  console.log('');
  console.log('Manual installation:');
  console.log('1. Visit https://ui.shadcn.com/docs/components/' + (componentName || 'button'));
  console.log('2. Copy the component code');
  console.log('3. Create resources/js/components/ui/' + (componentName || 'component') + '.tsx');
  console.log('4. Install any required dependencies with: bun add <package-name>');
  process.exit(1);
}

console.log(`To add the ${componentName} component:`);
console.log('');
console.log(`1. Visit: https://ui.shadcn.com/docs/components/${componentName}`);
console.log(`2. Copy the component code`);
console.log(`3. Create: resources/js/components/ui/${componentName}.tsx`);
console.log(`4. Install any required dependencies with: bun add <package-name>`);
console.log(`5. Import and use in your React components`);
console.log('');
console.log('Example import:');
console.log(`import { ${componentName.charAt(0).toUpperCase() + componentName.slice(1)} } from '@/components/ui/${componentName}';`);