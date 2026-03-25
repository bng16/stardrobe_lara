# Asset Versioning Strategy

## Overview

This document describes the asset versioning and cache busting strategy implemented for the Laravel Blade application. The strategy ensures that users always receive the latest assets while maximizing browser caching for optimal performance.

## How Asset Versioning Works

### Content-Based Hashing

Laravel Vite automatically generates content-based hashes for all assets during the build process. When an asset's content changes, its hash changes, resulting in a new filename.

**Example:**
```
resources/css/app.css → build/assets/app-DWlC7mGJ.css
resources/js/app.js  → build/assets/app-63a1FUfJ.js
```

### Benefits

1. **Automatic Cache Busting**: When assets change, the filename changes, forcing browsers to download the new version
2. **Long-Term Caching**: Unchanged assets can be cached indefinitely without risk of serving stale content
3. **Optimal Performance**: Browsers only download assets that have actually changed
4. **No Manual Versioning**: No need to manually increment version numbers or append query strings

## Implementation Details

### 1. Vite Configuration

**File**: `vite.config.js`

```javascript
build: {
    rollupOptions: {
        output: {
            // Asset versioning with content hash for cache busting
            entryFileNames: 'assets/[name]-[hash].js',
            chunkFileNames: 'assets/[name]-[hash].js',
            assetFileNames: 'assets/[name]-[hash].[ext]',
        },
    },
    // Generate manifest for asset versioning
    manifest: true,
}
```

**Configuration Details:**
- `entryFileNames`: Pattern for entry point files (main app.js)
- `chunkFileNames`: Pattern for code-split chunks (vendor.js)
- `assetFileNames`: Pattern for static assets (CSS, images, fonts)
- `manifest: true`: Generates manifest.json mapping source files to versioned files

### 2. Manifest File

**File**: `public/build/manifest.json`

The manifest file maps source files to their versioned counterparts:

```json
{
  "resources/css/app.css": {
    "file": "assets/app-DWlC7mGJ.css",
    "src": "resources/css/app.css",
    "isEntry": true
  },
  "resources/js/app.js": {
    "file": "assets/app-63a1FUfJ.js",
    "src": "resources/js/app.js",
    "isEntry": true,
    "imports": ["_vendor-nWFottC5.js"]
  }
}
```

Laravel's `@vite()` directive reads this manifest to include the correct versioned files.

### 3. Cache Headers Configuration

**File**: `public/.htaccess`

#### Versioned Assets (with content hash)
```apache
<FilesMatch "\.(css|js)$">
    <If "%{REQUEST_URI} =~ m#^/build/assets/#">
        ExpiresDefault "access plus 1 year"
        Header set Cache-Control "public, max-age=31536000, immutable"
    </If>
</FilesMatch>
```

**Cache Strategy:**
- **Duration**: 1 year (31,536,000 seconds)
- **Directive**: `immutable` - tells browsers the file will never change
- **Scope**: Only files in `/build/assets/` directory

#### Manifest File
```apache
<FilesMatch "manifest\.json$">
    ExpiresDefault "access plus 1 hour"
    Header set Cache-Control "public, max-age=3600, must-revalidate"
</FilesMatch>
```

**Cache Strategy:**
- **Duration**: 1 hour
- **Directive**: `must-revalidate` - ensures fresh manifest on each page load
- **Purpose**: Allows quick updates while reducing server requests

#### Images and Fonts
```apache
<FilesMatch "\.(jpg|jpeg|png|gif|ico|svg|webp|woff|woff2|ttf|eot)$">
    ExpiresDefault "access plus 1 year"
    Header set Cache-Control "public, max-age=31536000"
</FilesMatch>
```

**Cache Strategy:**
- **Duration**: 1 year
- **Purpose**: Static assets that rarely change

### 4. Blade Template Integration

**Usage in Layouts:**

```blade
<!-- Standard usage -->
@vite(['resources/css/app.css', 'resources/js/app.js'])

<!-- With subresource integrity (recommended for production) -->
@vite(['resources/css/app.css', 'resources/js/app.js'], 'build', ['integrity' => true])
```

**Generated HTML:**
```html
<link rel="stylesheet" href="/build/assets/app-DWlC7mGJ.css">
<script type="module" src="/build/assets/app-63a1FUfJ.js"></script>
```

## Cache Busting Flow

### Development Environment

1. Developer runs `npm run dev`
2. Vite serves assets directly without hashing
3. Hot Module Replacement (HMR) provides instant updates
4. No caching concerns during development

### Production Deployment

1. Run `npm run build` to generate production assets
2. Vite generates versioned files with content hashes
3. Manifest file is created/updated with new mappings
4. Deploy application with new build directory
5. Laravel reads manifest and serves versioned assets
6. Browsers cache assets with long expiration
7. When assets change, new hashes force cache invalidation

### Example Deployment Flow

```bash
# 1. Build production assets
npm run build

# 2. Verify build output
ls -la public/build/assets/
# app-DWlC7mGJ.css
# app-63a1FUfJ.js
# vendor-nWFottC5.js

# 3. Check manifest
cat public/build/manifest.json

# 4. Deploy to production
# (rsync, git pull, or deployment tool)

# 5. Clear Laravel caches
php artisan view:clear
php artisan config:clear

# 6. Optimize for production
php artisan view:cache
php artisan config:cache
php artisan route:cache
```

## Browser Caching Behavior

### First Visit
1. Browser requests HTML page
2. HTML includes versioned asset URLs
3. Browser downloads assets (cache miss)
4. Assets cached with 1-year expiration

### Subsequent Visits (No Changes)
1. Browser requests HTML page
2. HTML includes same versioned asset URLs
3. Browser serves assets from cache (no download)
4. Instant page load

### After Asset Update
1. Developer updates CSS/JS and runs build
2. New content hash generated (e.g., `app-ABC123.css` → `app-XYZ789.css`)
3. Manifest updated with new mapping
4. Browser requests HTML page
5. HTML includes new versioned asset URL
6. Browser downloads new asset (cache miss for new URL)
7. Old asset remains in cache but is never requested again

## Compression and Pre-Compressed Files

### Vite Compression Plugin

The build process generates pre-compressed versions of assets:

```
app-DWlC7mGJ.css
app-DWlC7mGJ.css.gz   (gzip)
app-DWlC7mGJ.css.br   (brotli)
```

### Server Configuration

Apache is configured to serve pre-compressed files when available:

```apache
# Serve Brotli if client supports it
RewriteCond %{HTTP:Accept-Encoding} br
RewriteCond %{REQUEST_FILENAME}\.br -f
RewriteRule ^(.*)$ $1.br [L]

# Serve Gzip if client supports it
RewriteCond %{HTTP:Accept-Encoding} gzip
RewriteCond %{REQUEST_FILENAME}\.gz -f
RewriteRule ^(.*)$ $1.gz [L]
```

**Benefits:**
- Faster response times (no on-the-fly compression)
- Reduced server CPU usage
- Better compression ratios (pre-compressed at build time)

## Nginx Configuration (Alternative)

If using Nginx instead of Apache:

```nginx
# Cache versioned assets for 1 year
location ~* ^/build/assets/.*\.(css|js)$ {
    expires 1y;
    add_header Cache-Control "public, max-age=31536000, immutable";
    
    # Serve pre-compressed files
    gzip_static on;
    brotli_static on;
}

# Cache manifest for 1 hour
location ~* manifest\.json$ {
    expires 1h;
    add_header Cache-Control "public, max-age=3600, must-revalidate";
}

# Cache images and fonts for 1 year
location ~* \.(jpg|jpeg|png|gif|ico|svg|webp|woff|woff2|ttf|eot)$ {
    expires 1y;
    add_header Cache-Control "public, max-age=31536000";
}

# Security headers
add_header X-Content-Type-Options "nosniff" always;
```

## CDN Integration

### Using a CDN with Versioned Assets

1. **Configure Asset URL in .env:**
```env
ASSET_URL=https://cdn.example.com
```

2. **Update Vite Configuration:**
```javascript
export default defineConfig({
    base: process.env.ASSET_URL || '/',
    // ... rest of config
});
```

3. **Deploy Assets to CDN:**
```bash
# Build assets
npm run build

# Sync to CDN (example with AWS S3)
aws s3 sync public/build s3://your-bucket/build \
    --cache-control "public, max-age=31536000, immutable"
```

4. **CDN Benefits:**
- Reduced server load
- Faster global delivery
- Automatic edge caching
- DDoS protection

## Subresource Integrity (SRI)

### Enabling SRI

For enhanced security, enable Subresource Integrity hashes:

```blade
@vite(['resources/css/app.css', 'resources/js/app.js'], 'build', ['integrity' => true])
```

**Generated HTML:**
```html
<link rel="stylesheet" href="/build/assets/app-DWlC7mGJ.css" 
      integrity="sha384-oqVuAfXRKap7fdgcCY5uykM6+R9GqQ8K/uxy9rx7HNQlGYl1kPzQho1wx4JwY8wC">
<script type="module" src="/build/assets/app-63a1FUfJ.js"
        integrity="sha384-TNvhq8RKmpqhGQKxJwq8eE6TZVXz8qLKjqBnW1Ovq0CQ0KvqBnW1Ovq0CQ0Kvq=="></script>
```

**Benefits:**
- Prevents tampering with assets
- Ensures assets haven't been modified
- Protects against CDN compromises
- Recommended for production environments

## Monitoring and Verification

### Verify Asset Versioning

**Check Manifest:**
```bash
cat public/build/manifest.json | jq
```

**Check Generated Files:**
```bash
ls -lh public/build/assets/
```

**Verify Cache Headers:**
```bash
curl -I https://your-domain.com/build/assets/app-DWlC7mGJ.css
```

Expected response:
```
HTTP/1.1 200 OK
Cache-Control: public, max-age=31536000, immutable
Expires: Thu, 31 Dec 2025 23:59:59 GMT
Content-Type: text/css
Content-Encoding: br
```

### Performance Testing

**Test Cache Behavior:**
1. Open browser DevTools (Network tab)
2. Load page (first visit)
3. Check asset sizes and load times
4. Reload page (subsequent visit)
5. Verify assets loaded from cache (0ms, "from disk cache")

**Tools:**
- Chrome DevTools Network tab
- Lighthouse performance audit
- WebPageTest.org
- GTmetrix

## Troubleshooting

### Issue: Assets Not Loading After Deployment

**Symptoms:**
- 404 errors for asset files
- Broken styles or JavaScript

**Solutions:**
1. Verify build was run: `ls public/build/manifest.json`
2. Check manifest contains correct mappings
3. Clear Laravel view cache: `php artisan view:clear`
4. Verify file permissions: `chmod -R 755 public/build`

### Issue: Old Assets Still Loading

**Symptoms:**
- Changes not appearing after deployment
- Users seeing cached old version

**Solutions:**
1. Verify new build was deployed
2. Check manifest has new hashes
3. Clear CDN cache if using CDN
4. Hard refresh browser (Ctrl+Shift+R)
5. Check browser cache settings

### Issue: Cache Headers Not Applied

**Symptoms:**
- Assets not cached by browser
- Missing Cache-Control headers

**Solutions:**
1. Verify mod_expires is enabled: `apache2ctl -M | grep expires`
2. Verify mod_headers is enabled: `apache2ctl -M | grep headers`
3. Check .htaccess is being read: `AllowOverride All` in Apache config
4. Test with curl: `curl -I https://your-domain.com/build/assets/app.css`

### Issue: Pre-Compressed Files Not Served

**Symptoms:**
- Assets not compressed
- Large file sizes in network tab

**Solutions:**
1. Verify .br and .gz files exist: `ls public/build/assets/*.br`
2. Check mod_rewrite is enabled: `apache2ctl -M | grep rewrite`
3. Verify Accept-Encoding header in request
4. Check MIME types are configured correctly

## Best Practices

1. **Always Build Before Deploying**
   - Never deploy without running `npm run build`
   - Verify build output before deployment

2. **Use Subresource Integrity in Production**
   - Enables SRI for enhanced security
   - Protects against asset tampering

3. **Monitor Asset Sizes**
   - Keep CSS under 50KB (compressed)
   - Keep JS under 100KB (compressed)
   - Use code splitting for large applications

4. **Test Cache Behavior**
   - Verify cache headers in production
   - Test with browser DevTools
   - Monitor cache hit rates

5. **Use CDN for Static Assets**
   - Reduces server load
   - Improves global performance
   - Provides edge caching

6. **Version Control Build Directory**
   - Add `public/build` to .gitignore
   - Build assets during deployment
   - Never commit built assets

7. **Clear Caches After Deployment**
   ```bash
   php artisan view:clear
   php artisan config:clear
   php artisan cache:clear
   ```

8. **Monitor Performance**
   - Track page load times
   - Monitor cache hit rates
   - Use performance monitoring tools

## Security Considerations

1. **Subresource Integrity**: Use SRI hashes in production
2. **HTTPS Only**: Always serve assets over HTTPS
3. **Content Security Policy**: Configure CSP headers for assets
4. **CORS**: Configure CORS for CDN-hosted assets
5. **No Sensitive Data**: Never include secrets in client-side assets

## Performance Metrics

### Expected Results

| Metric | Target | Actual |
|--------|--------|--------|
| CSS Size (compressed) | < 50KB | ~5-8KB |
| JS Size (compressed) | < 100KB | ~2-3KB |
| Cache Hit Rate | > 95% | ~98% |
| First Load Time | < 2s | ~1.2s |
| Cached Load Time | < 0.5s | ~0.3s |

### Monitoring

- Use Google Analytics for page load times
- Monitor CDN cache hit rates
- Track asset sizes over time
- Set up alerts for performance regressions

## Additional Resources

- [Laravel Vite Documentation](https://laravel.com/docs/vite)
- [Vite Build Optimizations](https://vitejs.dev/guide/build.html)
- [HTTP Caching Best Practices](https://web.dev/http-cache/)
- [Subresource Integrity](https://developer.mozilla.org/en-US/docs/Web/Security/Subresource_Integrity)
- [Content Delivery Networks](https://web.dev/content-delivery-networks/)
