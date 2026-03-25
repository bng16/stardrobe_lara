#!/bin/bash

###############################################################################
# Asset Deployment Script
# 
# This script optimizes and deploys assets for production
###############################################################################

set -e  # Exit on error

echo "🚀 Starting asset deployment..."
echo ""

# Colors for output
RED='\033[0;31m'
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
NC='\033[0m' # No Color

# Check if node_modules exists
if [ ! -d "node_modules" ]; then
    echo -e "${YELLOW}⚠️  node_modules not found. Installing dependencies...${NC}"
    npm install
fi

# Clean previous build
if [ -d "public/build" ]; then
    echo "🧹 Cleaning previous build..."
    rm -rf public/build
fi

# Run production build
echo "📦 Building assets for production..."
npm run build

# Check if build was successful
if [ ! -d "public/build" ]; then
    echo -e "${RED}❌ Build failed! public/build directory not created.${NC}"
    exit 1
fi

# Analyze bundle
echo ""
echo "📊 Analyzing bundle sizes..."
node scripts/analyze-bundle.js

# Cache Laravel views
echo ""
echo "🔧 Optimizing Laravel..."
php artisan view:cache
php artisan config:cache
php artisan route:cache

# Set proper permissions
echo "🔒 Setting file permissions..."
chmod -R 755 public/build

echo ""
echo -e "${GREEN}✅ Asset deployment complete!${NC}"
echo ""
echo "Next steps:"
echo "  1. Test the application locally"
echo "  2. Commit the changes (excluding public/build)"
echo "  3. Deploy to production server"
echo "  4. Run this script on production server"
echo ""
