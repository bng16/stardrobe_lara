#!/bin/bash
# CDN Asset Deployment Script
# This script builds and deploys static assets to a CDN

set -e

# Colors for output
RED='\033[0;31m'
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
NC='\033[0m' # No Color

# Configuration
CDN_URL="${ASSET_URL:-}"
CDN_PROVIDER="${CDN_PROVIDER:-s3}"  # s3, r2, bunny, spaces
CDN_BUCKET="${CDN_BUCKET:-}"
CDN_PROFILE="${CDN_PROFILE:-default}"
CDN_REGION="${CDN_REGION:-us-east-1}"
CDN_ENDPOINT="${CDN_ENDPOINT:-}"
CLOUDFRONT_DISTRIBUTION_ID="${CLOUDFRONT_DISTRIBUTION_ID:-}"

# Functions
print_info() {
    echo -e "${GREEN}[INFO]${NC} $1"
}

print_warning() {
    echo -e "${YELLOW}[WARN]${NC} $1"
}

print_error() {
    echo -e "${RED}[ERROR]${NC} $1"
}

check_requirements() {
    print_info "Checking requirements..."
    
    # Check Node.js
    if ! command -v node &> /dev/null; then
        print_error "Node.js is not installed"
        exit 1
    fi
    
    # Check npm
    if ! command -v npm &> /dev/null; then
        print_error "npm is not installed"
        exit 1
    fi
    
    # Check AWS CLI (for S3, R2, CloudFront, Spaces)
    if [[ "$CDN_PROVIDER" == "s3" || "$CDN_PROVIDER" == "r2" || "$CDN_PROVIDER" == "cloudfront" || "$CDN_PROVIDER" == "spaces" ]]; then
        if ! command -v aws &> /dev/null; then
            print_error "AWS CLI is not installed (required for $CDN_PROVIDER)"
            exit 1
        fi
    fi
    
    print_info "All requirements met"
}

validate_config() {
    print_info "Validating configuration..."
    
    if [ -z "$CDN_URL" ]; then
        print_error "CDN_URL or ASSET_URL environment variable is required"
        echo "Example: export ASSET_URL=https://cdn.example.com"
        exit 1
    fi
    
    if [ -z "$CDN_BUCKET" ]; then
        print_error "CDN_BUCKET environment variable is required"
        echo "Example: export CDN_BUCKET=your-app-assets"
        exit 1
    fi
    
    print_info "Configuration validated"
}

build_assets() {
    print_info "Building assets with CDN URL: $CDN_URL"
    
    # Set ASSET_URL for build
    export ASSET_URL=$CDN_URL
    
    # Install dependencies if needed
    if [ ! -d "node_modules" ]; then
        print_info "Installing npm dependencies..."
        npm ci
    fi
    
    # Build production assets
    npm run build
    
    # Verify build output
    if [ ! -f "public/build/manifest.json" ]; then
        print_error "Build failed: manifest.json not found"
        exit 1
    fi
    
    print_info "Assets built successfully"
}

deploy_to_s3() {
    print_info "Deploying to AWS S3..."
    
    # Sync assets (excluding manifest)
    aws s3 sync public/build s3://$CDN_BUCKET/build \
        --profile $CDN_PROFILE \
        --region $CDN_REGION \
        --cache-control "public, max-age=31536000, immutable" \
        --exclude "manifest.json" \
        --delete
    
    # Upload manifest with shorter cache
    aws s3 cp public/build/manifest.json \
        s3://$CDN_BUCKET/build/manifest.json \
        --profile $CDN_PROFILE \
        --region $CDN_REGION \
        --cache-control "public, max-age=3600, must-revalidate"
    
    print_info "Deployed to S3 successfully"
}

deploy_to_r2() {
    print_info "Deploying to Cloudflare R2..."
    
    if [ -z "$CDN_ENDPOINT" ]; then
        print_error "CDN_ENDPOINT is required for R2"
        echo "Example: export CDN_ENDPOINT=https://YOUR_ACCOUNT_ID.r2.cloudflarestorage.com"
        exit 1
    fi
    
    # Sync assets (excluding manifest)
    aws s3 sync public/build s3://$CDN_BUCKET/build \
        --profile $CDN_PROFILE \
        --endpoint-url $CDN_ENDPOINT \
        --cache-control "public, max-age=31536000, immutable" \
        --exclude "manifest.json" \
        --delete
    
    # Upload manifest with shorter cache
    aws s3 cp public/build/manifest.json \
        s3://$CDN_BUCKET/build/manifest.json \
        --profile $CDN_PROFILE \
        --endpoint-url $CDN_ENDPOINT \
        --cache-control "public, max-age=3600, must-revalidate"
    
    print_info "Deployed to R2 successfully"
}

deploy_to_spaces() {
    print_info "Deploying to DigitalOcean Spaces..."
    
    if [ -z "$CDN_ENDPOINT" ]; then
        print_error "CDN_ENDPOINT is required for Spaces"
        echo "Example: export CDN_ENDPOINT=https://nyc3.digitaloceanspaces.com"
        exit 1
    fi
    
    # Sync assets (excluding manifest)
    aws s3 sync public/build s3://$CDN_BUCKET/build \
        --profile $CDN_PROFILE \
        --endpoint-url $CDN_ENDPOINT \
        --cache-control "public, max-age=31536000, immutable" \
        --exclude "manifest.json" \
        --delete
    
    # Upload manifest with shorter cache
    aws s3 cp public/build/manifest.json \
        s3://$CDN_BUCKET/build/manifest.json \
        --profile $CDN_PROFILE \
        --endpoint-url $CDN_ENDPOINT \
        --cache-control "public, max-age=3600, must-revalidate"
    
    print_info "Deployed to Spaces successfully"
}

invalidate_cloudfront() {
    if [ -n "$CLOUDFRONT_DISTRIBUTION_ID" ]; then
        print_info "Invalidating CloudFront cache..."
        
        aws cloudfront create-invalidation \
            --profile $CDN_PROFILE \
            --distribution-id $CLOUDFRONT_DISTRIBUTION_ID \
            --paths "/build/manifest.json"
        
        print_info "CloudFront invalidation created"
    else
        print_warning "CLOUDFRONT_DISTRIBUTION_ID not set, skipping cache invalidation"
    fi
}

deploy_assets() {
    case $CDN_PROVIDER in
        s3)
            deploy_to_s3
            ;;
        r2)
            deploy_to_r2
            ;;
        spaces)
            deploy_to_spaces
            ;;
        cloudfront)
            deploy_to_s3
            invalidate_cloudfront
            ;;
        *)
            print_error "Unknown CDN provider: $CDN_PROVIDER"
            echo "Supported providers: s3, r2, spaces, cloudfront"
            exit 1
            ;;
    esac
}

print_summary() {
    echo ""
    echo "=========================================="
    echo "  CDN Deployment Summary"
    echo "=========================================="
    echo "CDN URL:      $CDN_URL"
    echo "Provider:     $CDN_PROVIDER"
    echo "Bucket:       $CDN_BUCKET"
    echo "Status:       ${GREEN}SUCCESS${NC}"
    echo "=========================================="
    echo ""
    echo "Next steps:"
    echo "1. Update ASSET_URL in production .env:"
    echo "   ASSET_URL=$CDN_URL"
    echo ""
    echo "2. Clear Laravel caches:"
    echo "   php artisan config:clear"
    echo "   php artisan view:clear"
    echo ""
    echo "3. Test asset loading:"
    echo "   curl -I $CDN_URL/build/manifest.json"
    echo ""
}

# Main execution
main() {
    echo "=========================================="
    echo "  CDN Asset Deployment"
    echo "=========================================="
    echo ""
    
    check_requirements
    validate_config
    build_assets
    deploy_assets
    print_summary
}

# Run main function
main
