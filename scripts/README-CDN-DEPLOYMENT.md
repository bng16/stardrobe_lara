# CDN Deployment Script

## Overview

The `deploy-cdn.sh` script automates the process of building and deploying static assets to a Content Delivery Network (CDN). It supports multiple CDN providers including AWS S3, CloudFront, Cloudflare R2, DigitalOcean Spaces, and BunnyCDN.

## Prerequisites

- Node.js and npm installed
- AWS CLI installed (for S3, R2, CloudFront, Spaces)
- Appropriate CDN credentials configured
- Built assets in `public/build/` directory

## Supported CDN Providers

- **s3**: AWS S3
- **r2**: Cloudflare R2
- **spaces**: DigitalOcean Spaces
- **cloudfront**: AWS CloudFront (includes S3 deployment + cache invalidation)
- **bunny**: BunnyCDN (manual upload via FTP/API)

## Configuration

Set the following environment variables before running the script:

### Required Variables

```bash
# CDN URL (where assets will be served from)
export ASSET_URL=https://cdn.example.com

# CDN bucket/storage name
export CDN_BUCKET=your-app-assets

# CDN provider (s3, r2, spaces, cloudfront)
export CDN_PROVIDER=s3
```

### Optional Variables

```bash
# AWS profile (default: default)
export CDN_PROFILE=production

# AWS region (default: us-east-1)
export CDN_REGION=us-east-1

# CDN endpoint (required for R2 and Spaces)
export CDN_ENDPOINT=https://YOUR_ACCOUNT_ID.r2.cloudflarestorage.com

# CloudFront distribution ID (for cache invalidation)
export CLOUDFRONT_DISTRIBUTION_ID=E1234567890ABC
```

## Usage

### Basic Usage

```bash
# Make script executable
chmod +x scripts/deploy-cdn.sh

# Run deployment
./scripts/deploy-cdn.sh
```

### AWS S3 Example

```bash
export ASSET_URL=https://cdn.example.com
export CDN_BUCKET=my-app-assets
export CDN_PROVIDER=s3
export CDN_PROFILE=production
export CDN_REGION=us-east-1

./scripts/deploy-cdn.sh
```

### Cloudflare R2 Example

```bash
export ASSET_URL=https://cdn.example.com
export CDN_BUCKET=my-app-assets
export CDN_PROVIDER=r2
export CDN_PROFILE=r2
export CDN_ENDPOINT=https://abc123.r2.cloudflarestorage.com

./scripts/deploy-cdn.sh
```

### AWS CloudFront Example

```bash
export ASSET_URL=https://d111111abcdef8.cloudfront.net
export CDN_BUCKET=my-app-assets
export CDN_PROVIDER=cloudfront
export CDN_PROFILE=production
export CDN_REGION=us-east-1
export CLOUDFRONT_DISTRIBUTION_ID=E1234567890ABC

./scripts/deploy-cdn.sh
```

### DigitalOcean Spaces Example

```bash
export ASSET_URL=https://my-app-assets.nyc3.cdn.digitaloceanspaces.com
export CDN_BUCKET=my-app-assets
export CDN_PROVIDER=spaces
export CDN_PROFILE=spaces
export CDN_ENDPOINT=https://nyc3.digitaloceanspaces.com

./scripts/deploy-cdn.sh
```

## What the Script Does

1. **Checks Requirements**: Verifies Node.js, npm, and AWS CLI are installed
2. **Validates Configuration**: Ensures all required environment variables are set
3. **Builds Assets**: Runs `npm run build` with the CDN URL
4. **Deploys Assets**: Uploads assets to the specified CDN provider
5. **Sets Cache Headers**: Configures proper cache headers for optimal performance
6. **Invalidates Cache**: (CloudFront only) Invalidates the manifest file

## Cache Headers

The script sets the following cache headers:

### Versioned Assets (CSS, JS)
```
Cache-Control: public, max-age=31536000, immutable
```
- 1 year cache duration
- `immutable` flag tells browsers the file will never change

### Manifest File
```
Cache-Control: public, max-age=3600, must-revalidate
```
- 1 hour cache duration
- `must-revalidate` ensures fresh manifest on updates

## AWS CLI Configuration

### For AWS S3/CloudFront

Create or update `~/.aws/credentials`:

```ini
[production]
aws_access_key_id = YOUR_ACCESS_KEY_ID
aws_secret_access_key = YOUR_SECRET_ACCESS_KEY
```

Create or update `~/.aws/config`:

```ini
[profile production]
region = us-east-1
output = json
```

### For Cloudflare R2

Create or update `~/.aws/credentials`:

```ini
[r2]
aws_access_key_id = YOUR_R2_ACCESS_KEY_ID
aws_secret_access_key = YOUR_R2_SECRET_ACCESS_KEY
```

Create or update `~/.aws/config`:

```ini
[profile r2]
region = auto
endpoint_url = https://YOUR_ACCOUNT_ID.r2.cloudflarestorage.com
```

### For DigitalOcean Spaces

Use `s3cmd` or AWS CLI with Spaces endpoint:

```bash
# Using s3cmd
s3cmd --configure

# Or using AWS CLI
aws configure --profile spaces
# Enter Spaces access key and secret
# Set region to your Spaces region (e.g., nyc3)
```

## Troubleshooting

### Error: "CDN_URL or ASSET_URL environment variable is required"

**Solution**: Set the ASSET_URL environment variable:
```bash
export ASSET_URL=https://cdn.example.com
```

### Error: "CDN_BUCKET environment variable is required"

**Solution**: Set the CDN_BUCKET environment variable:
```bash
export CDN_BUCKET=your-app-assets
```

### Error: "AWS CLI is not installed"

**Solution**: Install AWS CLI:
```bash
# macOS
brew install awscli

# Linux
pip install awscli

# Windows
# Download from https://aws.amazon.com/cli/
```

### Error: "Build failed: manifest.json not found"

**Solution**: Ensure npm build completes successfully:
```bash
npm run build
ls -la public/build/manifest.json
```

### Error: "Unable to locate credentials"

**Solution**: Configure AWS credentials:
```bash
aws configure --profile production
```

### Error: "Access Denied" when uploading to S3

**Solution**: Verify IAM permissions include:
- `s3:PutObject`
- `s3:PutObjectAcl`
- `s3:ListBucket`

## CI/CD Integration

### GitHub Actions

```yaml
name: Deploy CDN Assets

on:
  push:
    branches: [main]
    paths:
      - 'resources/css/**'
      - 'resources/js/**'

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
      
      - name: Deploy to CDN
        env:
          ASSET_URL: ${{ secrets.CDN_URL }}
          CDN_BUCKET: ${{ secrets.CDN_BUCKET }}
          CDN_PROVIDER: s3
          AWS_ACCESS_KEY_ID: ${{ secrets.AWS_ACCESS_KEY_ID }}
          AWS_SECRET_ACCESS_KEY: ${{ secrets.AWS_SECRET_ACCESS_KEY }}
        run: ./scripts/deploy-cdn.sh
```

### GitLab CI

```yaml
deploy-cdn:
  stage: deploy
  image: node:20
  before_script:
    - apt-get update && apt-get install -y awscli
  script:
    - export ASSET_URL=$CDN_URL
    - export CDN_BUCKET=$CDN_BUCKET
    - export CDN_PROVIDER=s3
    - ./scripts/deploy-cdn.sh
  only:
    - main
  variables:
    AWS_ACCESS_KEY_ID: $AWS_ACCESS_KEY_ID
    AWS_SECRET_ACCESS_KEY: $AWS_SECRET_ACCESS_KEY
```

## Post-Deployment Steps

After running the deployment script:

1. **Update Production Environment**
   ```bash
   # On production server
   echo "ASSET_URL=https://cdn.example.com" >> .env
   ```

2. **Clear Laravel Caches**
   ```bash
   php artisan config:clear
   php artisan view:clear
   php artisan cache:clear
   ```

3. **Verify Asset Loading**
   ```bash
   # Test CDN URL
   curl -I https://cdn.example.com/build/manifest.json
   
   # Should return 200 OK with proper cache headers
   ```

4. **Test Application**
   - Load application in browser
   - Check DevTools Network tab
   - Verify assets load from CDN
   - Check cache headers

## Best Practices

1. **Always test in staging first**: Deploy to staging CDN before production
2. **Monitor deployment**: Watch for errors during upload
3. **Verify cache headers**: Ensure proper cache configuration
4. **Keep credentials secure**: Never commit credentials to version control
5. **Use CI/CD**: Automate deployments for consistency
6. **Monitor CDN performance**: Track cache hit rates and bandwidth
7. **Set up alerts**: Get notified of deployment failures

## Security Considerations

1. **Use IAM roles**: Prefer IAM roles over access keys when possible
2. **Limit permissions**: Grant only necessary S3/CDN permissions
3. **Rotate credentials**: Regularly update access keys
4. **Use HTTPS**: Always serve assets over HTTPS
5. **Enable versioning**: Keep backup of previous asset versions
6. **Monitor access logs**: Review CDN access logs regularly

## Performance Tips

1. **Pre-compress assets**: Script generates gzip and brotli versions
2. **Set long cache times**: 1 year for versioned assets
3. **Use CDN edge locations**: Choose CDN with good global coverage
4. **Monitor cache hit rates**: Aim for >95% cache hit rate
5. **Minimize asset sizes**: Keep CSS <50KB, JS <100KB (compressed)

## Additional Resources

- [CDN Configuration Guide](../docs/CDN-Configuration.md)
- [Asset Versioning Strategy](../docs/Asset-Versioning-Strategy.md)
- [Asset Optimization Guide](../docs/Asset-Optimization.md)
- [AWS S3 Documentation](https://docs.aws.amazon.com/s3/)
- [Cloudflare R2 Documentation](https://developers.cloudflare.com/r2/)
- [DigitalOcean Spaces Documentation](https://docs.digitalocean.com/products/spaces/)
