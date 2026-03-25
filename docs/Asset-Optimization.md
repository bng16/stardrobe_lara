# Asset Optimization Guide

## Overview

This document describes the asset optimization strategy implemented for the Laravel Blade application. The optimization focuses on minimizing CSS and JavaScript bundle sizes, removing unused styles, and configuring proper production builds.

## Implemented Optimizations

### 1. Vite Build Configuration

**File**: `vite.config.js`

#### Minification
- **Minifier**: Terser (more aggressive than esbuild)
- **Console removal**: All `console.log`, `console.info`, and `console.debug` statements removed in production
- **Debugger removal**: All debugger statements removed

#### Code Splitting
- **Vendor chunks**: Separate chunk for third-party libraries (axios)
- **CSS code splitting**: Enabled for better caching
- **Manual chunks**: Configured for optimal cache invalidation

#### Asset Optimization
- **Inline limit**: Assets smaller than 4KB are inlined as base64
- **Sourcemaps**: Disabled in production builds
- **Chunk size warning**: Set to 500KB threshold

#### Compression
- **Gzip compression**: Enabled for all assets
- **Brotli compression**: Enabled for modern browsers
- Both compression formats are generated during build

### 2. Tailwind CSS Optimization

**File**: `tailwind.config.js`

#### Content Scanning
The following paths are scanned for class usage:
- `./resources/**/*.blade.php` - All Blade templates
- `./resources/**/*.js` - JavaScript files
- `./app/View/Components/**/*.php` - Blade component classes
- `./storage/framework/views/*.php` - Compiled views

#### Safelist
Critical dynamic classes are safelisted to prevent purging:
- Alert variants (blue, green, yellow, red)
- Button variants (primary, secondary, destructive)
- Hover states for interactive elements

#### Purging Strategy
- Unused CSS classes are automatically removed
- Only classes found in scanned files are included
- Reduces CSS bundle size by 80-95% typically

### 3. CSS Optimization

**File**: `resources/css/app.css`

#### Source Paths
Configured to scan:
- Laravel pagination views
- Compiled Blade views
- All Blade templates
- JavaScript files
- View component classes

#### Custom Properties
- CSS variables for theming (light/dark mode)
- Optimized for production with minimal overhead

#### Utility Classes
- Only essential utility classes included
- Custom utilities for text balancing

### 4. JavaScript Optimization

**File**: `resources/js/app.js`

#### Minimal Bundle
- Replaced React (100KB+) with vanilla JavaScript
- Essential interactivity only:
  - Auto-dismiss flash messages
  - Form loading states
  - Confirmation dialogs
  - Toggle visibility
  - AJAX helper utility

#### Bundle Size Reduction
- **Before**: ~150KB (React + dependencies)
- **After**: ~5KB (vanilla JS)
- **Reduction**: ~97% smaller

### 5. PostCSS Configuration

**File**: `postcss.config.js`

- Tailwind CSS processing
- Autoprefixer for browser compatibility
- Optimized for production builds

## Build Commands

### Development Build
```bash
npm run dev
```
- No minification
- Source maps enabled
- Hot module replacement
- Fast rebuild times

### Production Build
```bash
npm run build
```
- Full minification
- No source maps
- Asset compression (gzip + brotli)
- Optimized chunks
- CSS purging

### Production Build (Explicit)
```bash
npm run build:production
```
- Same as `npm run build` but explicitly sets production mode
- Ensures all production optimizations are applied

### Preview Production Build
```bash
npm run preview
```
- Preview the production build locally
- Test compressed assets
- Verify optimization results

## Asset Loading in Blade Templates

### Standard Loading
```blade
@vite(['resources/css/app.css', 'resources/js/app.js'])
```

### With Integrity Hashes (Recommended for Production)
```blade
@vite(['resources/css/app.css', 'resources/js/app.js'], 'build', ['integrity' => true])
```

## Performance Metrics

### Expected Bundle Sizes (Production)

| Asset | Uncompressed | Gzip | Brotli |
|-------|-------------|------|--------|
| app.css | ~15-25KB | ~5-8KB | ~4-6KB |
| app.js | ~5-8KB | ~2-3KB | ~1-2KB |
| vendor.js | ~10-15KB | ~4-6KB | ~3-5KB |

### Loading Performance
- **First Contentful Paint (FCP)**: < 1.5s
- **Largest Contentful Paint (LCP)**: < 2.5s
- **Time to Interactive (TTI)**: < 3.0s

## Deployment Checklist

### Pre-Deployment
- [ ] Run `npm run build` to generate production assets
- [ ] Verify `public/build` directory contains optimized assets
- [ ] Check asset file sizes are within expected ranges
- [ ] Test compressed assets locally with `npm run preview`

### Production Server Configuration

#### Nginx
```nginx
# Enable gzip compression
gzip on;
gzip_vary on;
gzip_types text/css application/javascript application/json;
gzip_min_length 1024;

# Enable brotli if available
brotli on;
brotli_types text/css application/javascript application/json;

# Cache static assets
location ~* \.(css|js|jpg|jpeg|png|gif|ico|svg|woff|woff2)$ {
    expires 1y;
    add_header Cache-Control "public, immutable";
}

# Serve pre-compressed files
location ~* \.(css|js)$ {
    gzip_static on;
    brotli_static on;
}
```

#### Apache
```apache
# Enable compression
<IfModule mod_deflate.c>
    AddOutputFilterByType DEFLATE text/css application/javascript application/json
</IfModule>

# Cache static assets
<IfModule mod_expires.c>
    ExpiresActive On
    ExpiresByType text/css "access plus 1 year"
    ExpiresByType application/javascript "access plus 1 year"
</IfModule>

# Serve pre-compressed files
<IfModule mod_rewrite.c>
    RewriteCond %{HTTP:Accept-Encoding} br
    RewriteCond %{REQUEST_FILENAME}.br -f
    RewriteRule ^(.*)$ $1.br [L]
</IfModule>
```

### Laravel Configuration

#### Enable View Caching
```bash
php artisan view:cache
```

#### Enable Config Caching
```bash
php artisan config:cache
```

#### Enable Route Caching
```bash
php artisan route:cache
```

#### Optimize Autoloader
```bash
composer install --optimize-autoloader --no-dev
```

## Monitoring and Maintenance

### Performance Monitoring
- Monitor bundle sizes after each deployment
- Track page load times with tools like:
  - Google PageSpeed Insights
  - WebPageTest
  - Lighthouse CI

### Bundle Analysis
To analyze bundle composition:
```bash
npm run build -- --mode production --report
```

### Maintenance Tasks
- **Monthly**: Review and update safelisted classes
- **Quarterly**: Audit unused dependencies
- **Per release**: Verify bundle size hasn't increased significantly

## Troubleshooting

### Issue: CSS Classes Not Working
**Cause**: Class was purged by Tailwind
**Solution**: Add class to safelist in `tailwind.config.js`

### Issue: Large Bundle Size
**Cause**: Unused dependencies or improper code splitting
**Solution**: 
1. Run bundle analysis
2. Remove unused dependencies
3. Review manual chunks configuration

### Issue: Slow Build Times
**Cause**: Too many files being scanned
**Solution**:
1. Optimize content paths in Tailwind config
2. Exclude unnecessary directories
3. Use more specific glob patterns

### Issue: Assets Not Loading
**Cause**: Vite manifest not found or incorrect paths
**Solution**:
1. Ensure `npm run build` completed successfully
2. Verify `public/build/manifest.json` exists
3. Check file permissions on build directory

## Best Practices

1. **Always build before deploying**: Never deploy without running production build
2. **Test compressed assets**: Use `npm run preview` to test locally
3. **Monitor bundle sizes**: Set up alerts for bundle size increases
4. **Use dynamic imports**: For large features, consider code splitting
5. **Optimize images**: Use appropriate formats and compression
6. **Leverage browser caching**: Configure proper cache headers
7. **Use CDN**: Consider serving static assets from CDN
8. **Regular audits**: Periodically review and optimize asset loading

## Additional Resources

- [Vite Build Optimizations](https://vitejs.dev/guide/build.html)
- [Tailwind CSS Optimization](https://tailwindcss.com/docs/optimizing-for-production)
- [Laravel Vite Plugin](https://laravel.com/docs/vite)
- [Web Performance Best Practices](https://web.dev/fast/)
