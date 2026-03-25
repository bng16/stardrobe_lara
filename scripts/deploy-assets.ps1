# Asset Deployment Script (PowerShell)
# 
# This script optimizes and deploys assets for production

$ErrorActionPreference = "Stop"

Write-Host "🚀 Starting asset deployment..." -ForegroundColor Cyan
Write-Host ""

# Check if node_modules exists
if (-not (Test-Path "node_modules")) {
    Write-Host "⚠️  node_modules not found. Installing dependencies..." -ForegroundColor Yellow
    npm install
}

# Clean previous build
if (Test-Path "public/build") {
    Write-Host "🧹 Cleaning previous build..." -ForegroundColor Cyan
    Remove-Item -Recurse -Force "public/build"
}

# Run production build
Write-Host "📦 Building assets for production..." -ForegroundColor Cyan
npm run build

# Check if build was successful
if (-not (Test-Path "public/build")) {
    Write-Host "❌ Build failed! public/build directory not created." -ForegroundColor Red
    exit 1
}

# Analyze bundle
Write-Host ""
Write-Host "📊 Analyzing bundle sizes..." -ForegroundColor Cyan
node scripts/analyze-bundle.js

# Cache Laravel views
Write-Host ""
Write-Host "🔧 Optimizing Laravel..." -ForegroundColor Cyan
php artisan view:cache
php artisan config:cache
php artisan route:cache

Write-Host ""
Write-Host "✅ Asset deployment complete!" -ForegroundColor Green
Write-Host ""
Write-Host "Next steps:"
Write-Host "  1. Test the application locally"
Write-Host "  2. Commit the changes (excluding public/build)"
Write-Host "  3. Deploy to production server"
Write-Host "  4. Run this script on production server"
Write-Host ""
