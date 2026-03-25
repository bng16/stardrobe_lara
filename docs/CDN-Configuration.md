# CDN Configuration Guide

## Overview

This guide explains how to configure a Content Delivery Network (CDN) for serving static assets (CSS, JavaScript, images, fonts) in the Laravel Blade application. Using a CDN improves global performance, reduces server load, and provides better scalability.

## Benefits of Using a CDN

- **Faster Global Delivery**: Assets served from edge locations closer to users
- **Reduced Server Load**: Static assets offloaded from application server
- **Better Scalability**: CDN handles traffic spikes automatically
- **Improved Reliability**: Built-in redundancy and failover
- **DDoS Protection**: Most CDNs include DDoS mitigation
- **Automatic Compression**: CDNs handle gzip/brotli compression
- **SSL/TLS Termination**: Secure asset delivery with minimal overhead

## How CDN Integration Works

### Asset Flow

```
1. Build Process:
   npm run build → Generates versioned assets in public/build/

2. CDN Deployment:
   Upload public/build/ to CDN → Assets available at CDN URL

3. Application Configuration:
   Set ASSET_URL=https://cdn.example.com

4. Asset Loading:
   @vite() directive → Generates URLs with CDN domain
   
5. Browser Request:
   <link href="https://cdn.example.com/build/assets/app-ABC123.css">
   → CDN serves asset from edge location
```

### Architecture Diagram

```
┌─────────────┐
│   Browser   │
└──────┬──────┘
       │ 1. Request HTML
       ▼
┌─────────────────┐
│  Laravel App    │
│  (Origin)       │
└──────┬──────────┘
       │ 2. Returns HTML with CDN URLs
       │    <link href="https://cdn.example.com/...">
       │
       ▼
┌─────────────────┐
│   CDN Edge      │ ◄── 3. Browser requests assets
│   Locations     │
└──────┬──────────┘
       │ 4. Serves cached assets
       │    (or fetches from origin if not cached)
       ▼
┌─────────────┐
│   Browser   │
└─────────────┘
```

## Configuration Steps

### 1. Environment Configuration

Add the CDN URL to your `.env` file:

```env
# CDN Configuration
ASSET_URL=https://cdn.example.com
```

**Important Notes:**
- Use HTTPS for security
- Do not include trailing slash
- Ensure CDN domain is configured and accessible
- Test CDN URL before deploying to production

### 2. Vite Configuration

The Vite configuration is already set up to support CDN URLs. It reads the `ASSET_URL` environment variable and uses it as the base URL for all assets.

**File**: `vite.config.js`

```javascript
export default defineConfig(({ mode }) => {
    const env = loadEnv(mode, process.cwd(), '');
    
    return {
        // CDN base URL configuration
        base: env.ASSET_URL || '/',
        // ... rest of configuration
    };
});
```

### 3. Build Assets

Build production assets with CDN URL:

```bash
# Set ASSET_URL for build process
export ASSET_URL=https://cdn.example.com

# Build assets
npm run build

# Verify manifest includes CDN URLs
cat public/build/manifest.json
```

**Expected Output:**
```json
{
  "resources/css/app.css": {
    "file": "assets/app-DWlC7mGJ.css",
    "src": "resources/css/app.css",
    "isEntry": true
  }
}
```

### 4. Deploy Assets to CDN

Upload the `public/build/` directory to your CDN:

```bash
# Example: Upload to CDN storage
# (Specific commands depend on your CDN provider)

# AWS S3/CloudFront
aws s3 sync public/build s3://your-bucket/build \
    --cache-control "public, max-age=31536000, immutable" \
    --exclude "manifest.json"

# Upload manifest separately with shorter cache
aws s3 cp public/build/manifest.json s3://your-bucket/build/manifest.json \
    --cache-control "public, max-age=3600, must-revalidate"
```

### 5. Deploy Application

Deploy your application with the updated `.env` configuration:

```bash
# On production server
echo "ASSET_URL=https://cdn.example.com" >> .env

# Clear caches
php artisan config:clear
php artisan view:clear
php artisan cache:clear

# Optimize for production
php artisan config:cache
php artisan view:cache
php artisan route:cache
```

## CDN Provider Setup

### CloudFlare CDN

CloudFlare is a popular choice for its ease of use, free tier, and built-in security features.

#### Setup Steps

1. **Add Your Domain to CloudFlare**
   - Sign up at https://cloudflare.com
   - Add your domain and update nameservers
   - Wait for DNS propagation (usually 5-10 minutes)

2. **Configure DNS for CDN Subdomain**
   ```
   Type: CNAME
   Name: cdn
   Target: yourdomain.com
   Proxy: Enabled (orange cloud)
   ```

3. **Configure Cache Rules**
   - Go to Rules → Page Rules
   - Create rule for `cdn.yourdomain.com/build/*`
   - Settings:
     - Cache Level: Cache Everything
     - Edge Cache TTL: 1 year
     - Browser Cache TTL: 1 year

4. **Configure CORS (if needed)**
   - Go to Rules → Transform Rules → HTTP Response Headers
   - Add header: `Access-Control-Allow-Origin: *`
   - Match: `cdn.yourdomain.com/build/*`

5. **Deploy Assets**
   ```bash
   # Build with CloudFlare CDN URL
   export ASSET_URL=https://cdn.yourdomain.com
   npm run build
   
   # Upload to your origin server
   rsync -avz public/build/ user@server:/path/to/public/build/
   ```

6. **Update Environment**
   ```env
   ASSET_URL=https://cdn.yourdomain.com
   ```

#### CloudFlare Configuration Example

```javascript
// CloudFlare Worker (optional, for advanced caching)
addEventListener('fetch', event => {
  event.respondWith(handleRequest(event.request))
})

async function handleRequest(request) {
  const url = new URL(request.url)
  
  // Cache assets for 1 year
  if (url.pathname.startsWith('/build/assets/')) {
    const cache = caches.default
    let response = await cache.match(request)
    
    if (!response) {
      response = await fetch(request)
      const headers = new Headers(response.headers)
      headers.set('Cache-Control', 'public, max-age=31536000, immutable')
      headers.set('Access-Control-Allow-Origin', '*')
      
      response = new Response(response.body, {
        status: response.status,
        statusText: response.statusText,
        headers: headers
      })
      
      event.waitUntil(cache.put(request, response.clone()))
    }
    
    return response
  }
  
  return fetch(request)
}
```

### AWS CloudFront

AWS CloudFront is ideal for applications already using AWS infrastructure.

#### Setup Steps

1. **Create S3 Bucket for Assets**
   ```bash
   # Create bucket
   aws s3 mb s3://your-app-assets
   
   # Configure bucket for static website hosting
   aws s3 website s3://your-app-assets \
       --index-document index.html
   ```

2. **Create CloudFront Distribution**
   ```bash
   # Create distribution configuration
   cat > cloudfront-config.json <<EOF
   {
     "CallerReference": "$(date +%s)",
     "Comment": "CDN for Laravel assets",
     "Enabled": true,
     "Origins": {
       "Quantity": 1,
       "Items": [
         {
           "Id": "S3-your-app-assets",
           "DomainName": "your-app-assets.s3.amazonaws.com",
           "S3OriginConfig": {
             "OriginAccessIdentity": ""
           }
         }
       ]
     },
     "DefaultCacheBehavior": {
       "TargetOriginId": "S3-your-app-assets",
       "ViewerProtocolPolicy": "redirect-to-https",
       "AllowedMethods": {
         "Quantity": 2,
         "Items": ["GET", "HEAD"]
       },
       "Compress": true,
       "MinTTL": 31536000,
       "DefaultTTL": 31536000,
       "MaxTTL": 31536000
     }
   }
   EOF
   
   # Create distribution
   aws cloudfront create-distribution \
       --distribution-config file://cloudfront-config.json
   ```

3. **Configure CORS on S3 Bucket**
   ```bash
   cat > cors-config.json <<EOF
   {
     "CORSRules": [
       {
         "AllowedOrigins": ["*"],
         "AllowedMethods": ["GET", "HEAD"],
         "AllowedHeaders": ["*"],
         "MaxAgeSeconds": 3600
       }
     ]
   }
   EOF
   
   aws s3api put-bucket-cors \
       --bucket your-app-assets \
       --cors-configuration file://cors-config.json
   ```

4. **Deploy Assets to S3**
   ```bash
   # Build assets
   export ASSET_URL=https://d111111abcdef8.cloudfront.net
   npm run build
   
   # Sync to S3
   aws s3 sync public/build s3://your-app-assets/build \
       --cache-control "public, max-age=31536000, immutable" \
       --exclude "manifest.json"
   
   # Upload manifest with shorter cache
   aws s3 cp public/build/manifest.json \
       s3://your-app-assets/build/manifest.json \
       --cache-control "public, max-age=3600, must-revalidate"
   ```

5. **Update Environment**
   ```env
   ASSET_URL=https://d111111abcdef8.cloudfront.net
   ```

6. **Invalidate CloudFront Cache (when needed)**
   ```bash
   # Invalidate all assets (use sparingly, costs apply)
   aws cloudfront create-invalidation \
       --distribution-id YOUR_DISTRIBUTION_ID \
       --paths "/build/*"
   ```

#### CloudFront Cache Policy

Create a custom cache policy for optimal performance:

```json
{
  "Name": "LaravelAssetsCachePolicy",
  "MinTTL": 31536000,
  "MaxTTL": 31536000,
  "DefaultTTL": 31536000,
  "ParametersInCacheKeyAndForwardedToOrigin": {
    "EnableAcceptEncodingGzip": true,
    "EnableAcceptEncodingBrotli": true,
    "HeadersConfig": {
      "HeaderBehavior": "none"
    },
    "CookiesConfig": {
      "CookieBehavior": "none"
    },
    "QueryStringsConfig": {
      "QueryStringBehavior": "none"
    }
  }
}
```

### Cloudflare R2 (S3-Compatible)

Cloudflare R2 is a cost-effective alternative to AWS S3 with no egress fees.

#### Setup Steps

1. **Create R2 Bucket**
   - Go to Cloudflare Dashboard → R2
   - Create new bucket: `your-app-assets`
   - Enable public access

2. **Configure Custom Domain**
   - Go to bucket settings → Custom Domains
   - Add domain: `cdn.yourdomain.com`
   - CloudFlare automatically configures DNS

3. **Get API Credentials**
   - Go to R2 → Manage R2 API Tokens
   - Create API token with read/write permissions
   - Save Access Key ID and Secret Access Key

4. **Configure AWS CLI for R2**
   ```bash
   # Add R2 profile to ~/.aws/credentials
   [r2]
   aws_access_key_id = YOUR_R2_ACCESS_KEY_ID
   aws_secret_access_key = YOUR_R2_SECRET_ACCESS_KEY
   
   # Add R2 config to ~/.aws/config
   [profile r2]
   region = auto
   endpoint_url = https://YOUR_ACCOUNT_ID.r2.cloudflarestorage.com
   ```

5. **Deploy Assets**
   ```bash
   # Build assets
   export ASSET_URL=https://cdn.yourdomain.com
   npm run build
   
   # Sync to R2
   aws s3 sync public/build s3://your-app-assets/build \
       --profile r2 \
       --endpoint-url https://YOUR_ACCOUNT_ID.r2.cloudflarestorage.com \
       --cache-control "public, max-age=31536000, immutable" \
       --exclude "manifest.json"
   ```

6. **Update Environment**
   ```env
   ASSET_URL=https://cdn.yourdomain.com
   ```

### BunnyCDN

BunnyCDN is a cost-effective CDN with excellent performance and simple setup.

#### Setup Steps

1. **Create Storage Zone**
   - Sign up at https://bunny.net
   - Go to Storage → Add Storage Zone
   - Name: `your-app-assets`
   - Region: Choose closest to your users

2. **Create Pull Zone (CDN)**
   - Go to CDN → Add Pull Zone
   - Name: `your-app-cdn`
   - Origin URL: Your storage zone URL
   - Enable: Optimizer, Cache, Compression

3. **Configure Custom Domain**
   - Go to Pull Zone → Hostnames
   - Add hostname: `cdn.yourdomain.com`
   - Add CNAME record in your DNS:
     ```
     cdn.yourdomain.com → your-app-cdn.b-cdn.net
     ```

4. **Deploy Assets via FTP**
   ```bash
   # Build assets
   export ASSET_URL=https://cdn.yourdomain.com
   npm run build
   
   # Upload via FTP (or use BunnyCDN API)
   # FTP credentials available in Storage Zone settings
   lftp -u your-storage-zone,your-password \
        storage.bunnycdn.com \
        -e "mirror -R public/build /build; quit"
   ```

5. **Update Environment**
   ```env
   ASSET_URL=https://cdn.yourdomain.com
   ```

### DigitalOcean Spaces CDN

DigitalOcean Spaces provides S3-compatible storage with built-in CDN.

#### Setup Steps

1. **Create Space**
   - Go to DigitalOcean → Spaces
   - Create new Space: `your-app-assets`
   - Enable CDN
   - Choose region

2. **Configure CORS**
   ```bash
   # Install s3cmd
   pip install s3cmd
   
   # Configure s3cmd for Spaces
   s3cmd --configure
   # Enter Spaces access key and secret
   # Host: nyc3.digitaloceanspaces.com (adjust for your region)
   
   # Create CORS configuration
   cat > cors.xml <<EOF
   <CORSConfiguration>
     <CORSRule>
       <AllowedOrigin>*</AllowedOrigin>
       <AllowedMethod>GET</AllowedMethod>
       <AllowedMethod>HEAD</AllowedMethod>
       <AllowedHeader>*</AllowedHeader>
       <MaxAgeSeconds>3600</MaxAgeSeconds>
     </CORSRule>
   </CORSConfiguration>
   EOF
   
   # Apply CORS
   s3cmd setcors cors.xml s3://your-app-assets
   ```

3. **Deploy Assets**
   ```bash
   # Build assets
   export ASSET_URL=https://your-app-assets.nyc3.cdn.digitaloceanspaces.com
   npm run build
   
   # Sync to Spaces
   s3cmd sync public/build/ s3://your-app-assets/build/ \
       --add-header="Cache-Control: public, max-age=31536000, immutable" \
       --exclude="manifest.json"
   ```

4. **Update Environment**
   ```env
   ASSET_URL=https://your-app-assets.nyc3.cdn.digitaloceanspaces.com
   ```

## Cache Headers Configuration

### Recommended Cache Headers

Different asset types should have different cache policies:

#### Versioned Assets (with content hash)
```
Cache-Control: public, max-age=31536000, immutable
```
- **Duration**: 1 year (31,536,000 seconds)
- **immutable**: Tells browsers file will never change
- **Applies to**: `/build/assets/*.css`, `/build/assets/*.js`

#### Manifest File
```
Cache-Control: public, max-age=3600, must-revalidate
```
- **Duration**: 1 hour
- **must-revalidate**: Ensures fresh manifest
- **Applies to**: `/build/manifest.json`

#### Images and Fonts
```
Cache-Control: public, max-age=31536000
```
- **Duration**: 1 year
- **Applies to**: `*.jpg`, `*.png`, `*.woff2`, etc.

### Origin Server Configuration

Even when using a CDN, configure proper cache headers on your origin server:

#### Apache (.htaccess)
```apache
<IfModule mod_expires.c>
    ExpiresActive On
    
    # Versioned assets - 1 year
    <FilesMatch "\.(css|js)$">
        <If "%{REQUEST_URI} =~ m#^/build/assets/#">
            ExpiresDefault "access plus 1 year"
            Header set Cache-Control "public, max-age=31536000, immutable"
        </If>
    </FilesMatch>
    
    # Manifest - 1 hour
    <FilesMatch "manifest\.json$">
        ExpiresDefault "access plus 1 hour"
        Header set Cache-Control "public, max-age=3600, must-revalidate"
    </FilesMatch>
    
    # Images and fonts - 1 year
    <FilesMatch "\.(jpg|jpeg|png|gif|ico|svg|webp|woff|woff2|ttf|eot)$">
        ExpiresDefault "access plus 1 year"
        Header set Cache-Control "public, max-age=31536000"
    </FilesMatch>
</IfModule>
```

#### Nginx
```nginx
# Versioned assets - 1 year
location ~* ^/build/assets/.*\.(css|js)$ {
    expires 1y;
    add_header Cache-Control "public, max-age=31536000, immutable";
    add_header Access-Control-Allow-Origin "*";
}

# Manifest - 1 hour
location ~* manifest\.json$ {
    expires 1h;
    add_header Cache-Control "public, max-age=3600, must-revalidate";
}

# Images and fonts - 1 year
location ~* \.(jpg|jpeg|png|gif|ico|svg|webp|woff|woff2|ttf|eot)$ {
    expires 1y;
    add_header Cache-Control "public, max-age=31536000";
    add_header Access-Control-Allow-Origin "*";
}
```

## CORS Configuration

### Why CORS is Needed

When assets are served from a different domain (CDN), browsers enforce Cross-Origin Resource Sharing (CORS) policies. You need to configure CORS headers to allow your application to load assets from the CDN.

### Required CORS Headers

```
Access-Control-Allow-Origin: *
Access-Control-Allow-Methods: GET, HEAD
Access-Control-Allow-Headers: *
Access-Control-Max-Age: 3600
```

### CDN-Specific CORS Setup

#### CloudFlare
- Go to Rules → Transform Rules → HTTP Response Headers
- Add rule for `cdn.yourdomain.com/build/*`
- Add header: `Access-Control-Allow-Origin: *`

#### AWS CloudFront
- Configure in S3 bucket CORS settings (shown in AWS section above)
- CloudFront automatically forwards CORS headers

#### Cloudflare R2
- CORS configured automatically when using custom domain
- No additional configuration needed

#### BunnyCDN
- Go to Pull Zone → Edge Rules
- Add rule: Set header `Access-Control-Allow-Origin: *`

## Deployment Automation

### Automated Deployment Script

Create a deployment script to automate the CDN deployment process:

```bash
#!/bin/bash
# deploy-cdn.sh

set -e

# Configuration
CDN_URL="https://cdn.example.com"
CDN_BUCKET="your-app-assets"
CDN_PROFILE="default"  # AWS profile or R2 profile

echo "Building assets with CDN URL: $CDN_URL"

# Build assets
export ASSET_URL=$CDN_URL
npm run build

echo "Deploying assets to CDN..."

# Sync assets (excluding manifest)
aws s3 sync public/build s3://$CDN_BUCKET/build \
    --profile $CDN_PROFILE \
    --cache-control "public, max-age=31536000, immutable" \
    --exclude "manifest.json" \
    --delete

# Upload manifest with shorter cache
aws s3 cp public/build/manifest.json \
    s3://$CDN_BUCKET/build/manifest.json \
    --profile $CDN_PROFILE \
    --cache-control "public, max-age=3600, must-revalidate"

echo "Assets deployed successfully!"
echo "Don't forget to update ASSET_URL in production .env"
```

Make it executable:
```bash
chmod +x deploy-cdn.sh
```

### GitHub Actions Workflow

Automate CDN deployment with GitHub Actions:

```yaml
# .github/workflows/deploy-cdn.yml
name: Deploy Assets to CDN

on:
  push:
    branches: [main]
    paths:
      - 'resources/css/**'
      - 'resources/js/**'
      - 'vite.config.js'
      - 'package.json'

jobs:
  deploy:
    runs-on: ubuntu-latest
    
    steps:
      - uses: actions/checkout@v3
      
      - name: Setup Node.js
        uses: actions/setup-node@v3
        with:
          node-version: '20'
          cache: 'npm'
      
      - name: Install dependencies
        run: npm ci
      
      - name: Build assets
        env:
          ASSET_URL: ${{ secrets.CDN_URL }}
        run: npm run build
      
      - name: Configure AWS credentials
        uses: aws-actions/configure-aws-credentials@v2
        with:
          aws-access-key-id: ${{ secrets.AWS_ACCESS_KEY_ID }}
          aws-secret-access-key: ${{ secrets.AWS_SECRET_ACCESS_KEY }}
          aws-region: us-east-1
      
      - name: Deploy to S3/CloudFront
        run: |
          aws s3 sync public/build s3://${{ secrets.CDN_BUCKET }}/build \
            --cache-control "public, max-age=31536000, immutable" \
            --exclude "manifest.json" \
            --delete
          
          aws s3 cp public/build/manifest.json \
            s3://${{ secrets.CDN_BUCKET }}/build/manifest.json \
            --cache-control "public, max-age=3600, must-revalidate"
      
      - name: Invalidate CloudFront cache
        if: github.ref == 'refs/heads/main'
        run: |
          aws cloudfront create-invalidation \
            --distribution-id ${{ secrets.CLOUDFRONT_DISTRIBUTION_ID }} \
            --paths "/build/manifest.json"
```

## Testing CDN Configuration

### 1. Verify Asset URLs

Check that assets are loading from CDN:

```bash
# View page source
curl https://yourdomain.com | grep -o 'https://cdn.example.com[^"]*'

# Expected output:
# https://cdn.example.com/build/assets/app-ABC123.css
# https://cdn.example.com/build/assets/app-XYZ789.js
```

### 2. Test Cache Headers

Verify cache headers are correct:

```bash
# Test versioned asset
curl -I https://cdn.example.com/build/assets/app-ABC123.css

# Expected headers:
# Cache-Control: public, max-age=31536000, immutable
# Access-Control-Allow-Origin: *
# Content-Encoding: br (or gzip)
```

### 3. Test CORS

Verify CORS headers allow cross-origin requests:

```bash
curl -I -H "Origin: https://yourdomain.com" \
     https://cdn.example.com/build/assets/app-ABC123.css

# Expected header:
# Access-Control-Allow-Origin: *
```

### 4. Browser Testing

1. Open browser DevTools (Network tab)
2. Load your application
3. Check asset URLs start with CDN domain
4. Verify assets load successfully (200 status)
5. Check cache headers in response
6. Reload page - assets should load from cache

### 5. Performance Testing

Use online tools to verify CDN performance:

- **WebPageTest**: https://www.webpagetest.org
- **GTmetrix**: https://gtmetrix.com
- **Pingdom**: https://tools.pingdom.com

Expected improvements:
- Reduced Time to First Byte (TTFB)
- Faster asset loading from global locations
- Better cache hit rates

## Troubleshooting

### Issue: Assets Not Loading from CDN

**Symptoms:**
- Assets still loading from origin server
- URLs don't include CDN domain

**Solutions:**
1. Verify `ASSET_URL` is set in `.env`
2. Clear Laravel config cache: `php artisan config:clear`
3. Clear view cache: `php artisan view:clear`
4. Rebuild assets: `npm run build`
5. Check manifest.json for correct URLs

### Issue: CORS Errors

**Symptoms:**
- Console errors: "blocked by CORS policy"
- Assets fail to load in browser

**Solutions:**
1. Verify CORS headers on CDN
2. Check `Access-Control-Allow-Origin` header
3. Ensure CDN allows GET/HEAD methods
4. Test with curl (shown above)

### Issue: Assets Return 404

**Symptoms:**
- 404 errors for asset files
- Assets not found on CDN

**Solutions:**
1. Verify assets were uploaded to CDN
2. Check CDN bucket/storage configuration
3. Verify CDN URL is correct
4. Check file permissions on CDN storage
5. Ensure manifest.json was uploaded

### Issue: Old Assets Still Loading

**Symptoms:**
- Changes not appearing after deployment
- Users seeing cached old version

**Solutions:**
1. Verify new build was deployed to CDN
2. Check manifest has new hashes
3. Invalidate CDN cache (if supported)
4. Clear browser cache (Ctrl+Shift+R)
5. Check CDN cache TTL settings

### Issue: Slow Asset Loading

**Symptoms:**
- Assets loading slowly from CDN
- Poor performance metrics

**Solutions:**
1. Verify CDN is enabled and active
2. Check CDN edge location coverage
3. Ensure compression is enabled (gzip/brotli)
4. Verify cache headers are correct
5. Test from different geographic locations
6. Consider using a different CDN provider

## Best Practices

1. **Always Use HTTPS**
   - Never serve assets over HTTP
   - Ensures security and enables HTTP/2

2. **Enable Compression**
   - Use Brotli for modern browsers
   - Fallback to Gzip for older browsers
   - Pre-compress assets during build

3. **Set Long Cache Times**
   - Use 1 year for versioned assets
   - Content hash ensures cache invalidation
   - Reduces CDN bandwidth costs

4. **Use Subresource Integrity**
   ```blade
   @vite(['resources/css/app.css'], 'build', ['integrity' => true])
   ```
   - Protects against CDN compromises
   - Ensures asset integrity

5. **Monitor CDN Performance**
   - Track cache hit rates
   - Monitor bandwidth usage
   - Set up alerts for issues
   - Review CDN analytics regularly

6. **Automate Deployments**
   - Use CI/CD for asset deployment
   - Automate cache invalidation
   - Test before production deployment

7. **Keep Manifest on Origin**
   - Manifest should have short cache (1 hour)
   - Allows quick updates without CDN invalidation
   - Consider serving manifest from origin server

8. **Test Thoroughly**
   - Test from different locations
   - Verify CORS configuration
   - Check cache behavior
   - Test fallback scenarios

## Cost Considerations

### CloudFlare
- **Free Tier**: Unlimited bandwidth, basic CDN
- **Pro**: $20/month, advanced features
- **Best for**: Small to medium applications

### AWS CloudFront
- **Pricing**: Pay per GB transferred
- **First 1TB**: $0.085/GB
- **Invalidations**: First 1,000/month free
- **Best for**: AWS-based applications

### Cloudflare R2
- **Storage**: $0.015/GB/month
- **Egress**: Free (no bandwidth charges)
- **Operations**: $0.36 per million requests
- **Best for**: Cost-conscious applications

### BunnyCDN
- **Pricing**: $0.01-0.03/GB depending on region
- **Storage**: $0.01/GB/month
- **Best for**: Budget-friendly option

### DigitalOcean Spaces
- **Pricing**: $5/month for 250GB storage + 1TB transfer
- **Overage**: $0.01/GB storage, $0.01/GB transfer
- **Best for**: DigitalOcean users

## Security Considerations

1. **Use HTTPS Only**: Never serve assets over HTTP
2. **Enable SRI**: Use Subresource Integrity hashes
3. **Configure CSP**: Set Content Security Policy headers
4. **Restrict CORS**: Use specific origins if possible
5. **Monitor Access**: Review CDN access logs
6. **Rotate Credentials**: Regularly update API keys
7. **Use IAM Roles**: For AWS, use IAM roles instead of keys

## Monitoring and Analytics

### Key Metrics to Track

- **Cache Hit Rate**: Should be >95%
- **Bandwidth Usage**: Monitor for unexpected spikes
- **Response Times**: Track P50, P95, P99
- **Error Rates**: Monitor 4xx and 5xx errors
- **Geographic Distribution**: Verify edge coverage

### Monitoring Tools

- CDN provider dashboards
- Google Analytics (page load times)
- New Relic / Datadog (APM)
- CloudWatch (for AWS)
- Custom monitoring scripts

## Additional Resources

- [Laravel Vite Documentation](https://laravel.com/docs/vite)
- [CloudFlare CDN Documentation](https://developers.cloudflare.com/cache/)
- [AWS CloudFront Documentation](https://docs.aws.amazon.com/cloudfront/)
- [Cloudflare R2 Documentation](https://developers.cloudflare.com/r2/)
- [BunnyCDN Documentation](https://docs.bunny.net/)
- [HTTP Caching Best Practices](https://web.dev/http-cache/)
- [CORS Documentation](https://developer.mozilla.org/en-US/docs/Web/HTTP/CORS)
