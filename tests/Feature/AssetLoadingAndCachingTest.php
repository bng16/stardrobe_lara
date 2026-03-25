<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\File;
use Tests\TestCase;

/**
 * Asset Loading and Caching Test
 * 
 * Tests asset loading in production environment, cache headers,
 * browser caching behavior, compressed assets, and CDN integration.
 * 
 * Task: 12.2.4 - Test asset loading and caching
 */
class AssetLoadingAndCachingTest extends TestCase
{
    /**
     * Test that production build exists and is ready for deployment.
     */
    public function test_production_build_exists(): void
    {
        $buildPath = public_path('build');
        
        if (!file_exists($buildPath)) {
            $this->markTestSkipped('Build directory not found. Run "npm run build" first.');
        }
        
        $this->assertDirectoryExists($buildPath, 'Production build directory should exist');
        
        // Verify essential files exist
        $this->assertFileExists(public_path('build/manifest.json'), 'Manifest file should exist');
        $this->assertDirectoryExists(public_path('build/assets'), 'Assets directory should exist');
    }

    /**
     * Test that cache headers are correctly configured in .htaccess.
     */
    public function test_htaccess_cache_headers_configuration(): void
    {
        $htaccessPath = public_path('.htaccess');
        
        $this->assertFileExists($htaccessPath, '.htaccess file should exist');
        
        $htaccessContent = File::get($htaccessPath);
        
        // Verify mod_expires is configured
        $this->assertStringContainsString('mod_expires', $htaccessContent, 
            '.htaccess should contain mod_expires configuration');
        
        // Verify Cache-Control headers are set
        $this->assertStringContainsString('Cache-Control', $htaccessContent, 
            '.htaccess should contain Cache-Control headers');
        
        // Verify immutable directive for versioned assets
        $this->assertStringContainsString('immutable', $htaccessContent, 
            '.htaccess should contain immutable directive for versioned assets');
        
        // Verify long cache duration (1 year = 31536000 seconds)
        $this->assertStringContainsString('31536000', $htaccessContent, 
            '.htaccess should set 1 year cache for versioned assets');
    }

    /**
     * Test that versioned assets have proper cache headers configuration.
     */
    public function test_versioned_assets_cache_configuration(): void
    {
        $htaccessPath = public_path('.htaccess');
        $htaccessContent = File::get($htaccessPath);
        
        // Check for versioned assets pattern matching
        $this->assertStringContainsString('/build/assets/', $htaccessContent, 
            '.htaccess should target /build/assets/ directory');
        
        // Verify cache headers for CSS and JS files
        $this->assertMatchesRegularExpression('/\.(css|js)/', $htaccessContent, 
            '.htaccess should configure cache for CSS and JS files');
        
        // Verify public cache directive
        $this->assertStringContainsString('public', $htaccessContent, 
            '.htaccess should set public cache directive');
    }

    /**
     * Test that manifest file has shorter cache duration.
     */
    public function test_manifest_has_shorter_cache(): void
    {
        $htaccessPath = public_path('.htaccess');
        $htaccessContent = File::get($htaccessPath);
        
        // Verify manifest.json has specific cache rules
        $this->assertStringContainsString('manifest', $htaccessContent, 
            '.htaccess should contain manifest-specific cache rules');
        
        // Verify must-revalidate directive for manifest
        $this->assertStringContainsString('must-revalidate', $htaccessContent, 
            '.htaccess should set must-revalidate for manifest');
        
        // Verify shorter cache duration (1 hour = 3600 seconds)
        $this->assertStringContainsString('3600', $htaccessContent, 
            '.htaccess should set 1 hour cache for manifest');
    }

    /**
     * Test that compression is properly configured.
     */
    public function test_compression_configuration(): void
    {
        $htaccessPath = public_path('.htaccess');
        $htaccessContent = File::get($htaccessPath);
        
        // Verify mod_deflate is configured
        $this->assertStringContainsString('mod_deflate', $htaccessContent, 
            '.htaccess should contain mod_deflate configuration');
        
        // Verify Accept-Encoding handling
        $this->assertStringContainsString('Accept-Encoding', $htaccessContent, 
            '.htaccess should handle Accept-Encoding for pre-compressed files');
        
        // Verify gzip and brotli support
        $this->assertStringContainsString('gzip', $htaccessContent, 
            '.htaccess should support gzip compression');
        $this->assertStringContainsString('br', $htaccessContent, 
            '.htaccess should support brotli compression');
    }

    /**
     * Test that pre-compressed files exist for all assets.
     */
    public function test_precompressed_files_exist(): void
    {
        $manifestPath = public_path('build/manifest.json');
        
        if (!file_exists($manifestPath)) {
            $this->markTestSkipped('Manifest file not found. Run "npm run build" first.');
        }
        
        $manifest = json_decode(File::get($manifestPath), true);
        
        foreach ($manifest as $source => $asset) {
            if (isset($asset['file']) && (str_ends_with($asset['file'], '.css') || str_ends_with($asset['file'], '.js'))) {
                $assetPath = public_path('build/' . $asset['file']);
                $gzipPath = $assetPath . '.gz';
                $brotliPath = $assetPath . '.br';
                
                // Verify gzip compressed version exists
                $this->assertFileExists($gzipPath, 
                    "Gzip compressed version should exist: {$asset['file']}.gz");
                
                // Verify brotli compressed version exists
                $this->assertFileExists($brotliPath, 
                    "Brotli compressed version should exist: {$asset['file']}.br");
                
                // Verify compressed files are smaller than original
                if (File::exists($assetPath)) {
                    $originalSize = File::size($assetPath);
                    $gzipSize = File::size($gzipPath);
                    $brotliSize = File::size($brotliPath);
                    
                    $this->assertLessThan($originalSize, $gzipSize, 
                        "Gzip file should be smaller: {$asset['file']}");
                    $this->assertLessThan($originalSize, $brotliSize, 
                        "Brotli file should be smaller: {$asset['file']}");
                    
                    // Brotli should typically be smaller than gzip
                    $this->assertLessThanOrEqual($gzipSize, $brotliSize, 
                        "Brotli should be smaller or equal to gzip: {$asset['file']}");
                }
            }
        }
    }

    /**
     * Test that manifest.json is also compressed.
     */
    public function test_manifest_is_compressed(): void
    {
        $manifestPath = public_path('build/manifest.json');
        
        if (!file_exists($manifestPath)) {
            $this->markTestSkipped('Manifest file not found. Run "npm run build" first.');
        }
        
        $this->assertFileExists($manifestPath . '.gz', 
            'Manifest should have gzip compressed version');
        $this->assertFileExists($manifestPath . '.br', 
            'Manifest should have brotli compressed version');
        
        // Verify compressed versions are smaller
        $originalSize = File::size($manifestPath);
        $gzipSize = File::size($manifestPath . '.gz');
        $brotliSize = File::size($manifestPath . '.br');
        
        $this->assertLessThan($originalSize, $gzipSize, 
            'Gzip manifest should be smaller than original');
        $this->assertLessThan($originalSize, $brotliSize, 
            'Brotli manifest should be smaller than original');
    }

    /**
     * Test compression ratio is acceptable for production.
     */
    public function test_compression_ratio_is_acceptable(): void
    {
        $manifestPath = public_path('build/manifest.json');
        
        if (!file_exists($manifestPath)) {
            $this->markTestSkipped('Manifest file not found. Run "npm run build" first.');
        }
        
        $manifest = json_decode(File::get($manifestPath), true);
        
        foreach ($manifest as $source => $asset) {
            if (isset($asset['file']) && (str_ends_with($asset['file'], '.css') || str_ends_with($asset['file'], '.js'))) {
                $assetPath = public_path('build/' . $asset['file']);
                $gzipPath = $assetPath . '.gz';
                
                if (File::exists($assetPath) && File::exists($gzipPath)) {
                    $originalSize = File::size($assetPath);
                    $gzipSize = File::size($gzipPath);
                    
                    // Skip very small files (compression may not be effective)
                    if ($originalSize < 1024) {
                        continue;
                    }
                    
                    $compressionRatio = ($originalSize - $gzipSize) / $originalSize;
                    
                    // For files larger than 10KB, expect at least 50% compression
                    if ($originalSize > 10 * 1024) {
                        $this->assertGreaterThan(0.5, $compressionRatio, 
                            "Compression ratio for {$asset['file']} should be > 50% for files > 10KB. " .
                            "Original: " . round($originalSize / 1024, 2) . "KB, " .
                            "Compressed: " . round($gzipSize / 1024, 2) . "KB, " .
                            "Ratio: " . round($compressionRatio * 100, 2) . "%");
                    } else {
                        // For smaller files, just verify some compression occurred
                        $this->assertGreaterThan(0, $compressionRatio, 
                            "Some compression should occur for {$asset['file']}");
                    }
                }
            }
        }
    }

    /**
     * Test that CDN deployment script exists and is executable.
     */
    public function test_cdn_deployment_script_exists(): void
    {
        $scriptPath = base_path('scripts/deploy-cdn.sh');
        
        $this->assertFileExists($scriptPath, 'CDN deployment script should exist');
        
        // Verify script is executable (on Unix-like systems)
        if (PHP_OS_FAMILY !== 'Windows') {
            $perms = fileperms($scriptPath);
            $isExecutable = ($perms & 0x0040) || ($perms & 0x0008) || ($perms & 0x0001);
            
            $this->assertTrue($isExecutable, 
                'CDN deployment script should be executable. Run: chmod +x scripts/deploy-cdn.sh');
        }
    }

    /**
     * Test that CDN deployment documentation exists.
     */
    public function test_cdn_deployment_documentation_exists(): void
    {
        $readmePath = base_path('scripts/README-CDN-DEPLOYMENT.md');
        
        $this->assertFileExists($readmePath, 'CDN deployment README should exist');
        
        $content = File::get($readmePath);
        
        // Verify documentation contains essential sections
        $this->assertStringContainsString('Prerequisites', $content, 
            'README should contain Prerequisites section');
        $this->assertStringContainsString('Configuration', $content, 
            'README should contain Configuration section');
        $this->assertStringContainsString('Usage', $content, 
            'README should contain Usage section');
        $this->assertStringContainsString('Troubleshooting', $content, 
            'README should contain Troubleshooting section');
    }

    /**
     * Test that CDN configuration guide exists.
     */
    public function test_cdn_configuration_guide_exists(): void
    {
        $guidePath = base_path('docs/CDN-Configuration.md');
        
        $this->assertFileExists($guidePath, 'CDN configuration guide should exist');
        
        $content = File::get($guidePath);
        
        // Verify guide contains essential information
        $this->assertStringContainsString('CloudFlare', $content, 
            'Guide should cover CloudFlare CDN');
        $this->assertStringContainsString('AWS CloudFront', $content, 
            'Guide should cover AWS CloudFront');
        $this->assertStringContainsString('CORS', $content, 
            'Guide should cover CORS configuration');
        $this->assertStringContainsString('Cache Headers', $content, 
            'Guide should cover cache headers');
    }

    /**
     * Test that Vite config supports CDN URL configuration.
     */
    public function test_vite_config_supports_cdn(): void
    {
        $viteConfigPath = base_path('vite.config.js');
        
        $this->assertFileExists($viteConfigPath, 'vite.config.js should exist');
        
        $viteConfig = File::get($viteConfigPath);
        
        // Verify CDN base URL configuration
        $this->assertStringContainsString('ASSET_URL', $viteConfig, 
            'Vite config should read ASSET_URL environment variable');
        
        $this->assertStringContainsString('base:', $viteConfig, 
            'Vite config should set base URL for CDN support');
        
        // Verify manifest generation is enabled
        $this->assertStringContainsString('manifest: true', $viteConfig, 
            'Vite config should enable manifest generation');
    }

    /**
     * Test that asset versioning is properly configured in Vite.
     */
    public function test_vite_asset_versioning_configuration(): void
    {
        $viteConfigPath = base_path('vite.config.js');
        $viteConfig = File::get($viteConfigPath);
        
        // Verify hash-based versioning
        $this->assertStringContainsString('[hash]', $viteConfig, 
            'Vite config should use [hash] for asset versioning');
        
        // Verify output file patterns
        $this->assertStringContainsString('entryFileNames', $viteConfig, 
            'Vite config should define entryFileNames pattern');
        $this->assertStringContainsString('chunkFileNames', $viteConfig, 
            'Vite config should define chunkFileNames pattern');
        $this->assertStringContainsString('assetFileNames', $viteConfig, 
            'Vite config should define assetFileNames pattern');
    }

    /**
     * Test that compression plugins are configured in Vite.
     */
    public function test_vite_compression_plugins_configured(): void
    {
        $viteConfigPath = base_path('vite.config.js');
        $viteConfig = File::get($viteConfigPath);
        
        // Verify compression plugin is imported
        $this->assertStringContainsString('vite-plugin-compression', $viteConfig, 
            'Vite config should import compression plugin');
        
        // Verify gzip compression is configured
        $this->assertStringContainsString('gzip', $viteConfig, 
            'Vite config should configure gzip compression');
        
        // Verify brotli compression is configured
        $this->assertStringContainsString('brotli', $viteConfig, 
            'Vite config should configure brotli compression');
    }

    /**
     * Test that layouts use @vite directive for asset loading.
     */
    public function test_layouts_use_vite_directive(): void
    {
        $layouts = [
            resource_path('views/layouts/app.blade.php'),
            resource_path('views/layouts/admin.blade.php'),
            resource_path('views/layouts/auth.blade.php'),
        ];
        
        foreach ($layouts as $layoutPath) {
            if (File::exists($layoutPath)) {
                $content = File::get($layoutPath);
                
                $this->assertStringContainsString('@vite', $content, 
                    "Layout {$layoutPath} should use @vite directive");
                
                // Verify CSS and JS are included
                $this->assertStringContainsString('resources/css/app.css', $content, 
                    "Layout {$layoutPath} should include CSS asset");
                $this->assertStringContainsString('resources/js/app.js', $content, 
                    "Layout {$layoutPath} should include JS asset");
            }
        }
    }

    /**
     * Test that asset sizes are optimized for production.
     */
    public function test_asset_sizes_are_optimized(): void
    {
        $manifestPath = public_path('build/manifest.json');
        
        if (!file_exists($manifestPath)) {
            $this->markTestSkipped('Manifest file not found. Run "npm run build" first.');
        }
        
        $manifest = json_decode(File::get($manifestPath), true);
        
        foreach ($manifest as $source => $asset) {
            if (isset($asset['file'])) {
                $assetPath = public_path('build/' . $asset['file']);
                
                if (File::exists($assetPath)) {
                    $size = File::size($assetPath);
                    
                    // CSS should be under 500KB uncompressed
                    if (str_ends_with($asset['file'], '.css')) {
                        $this->assertLessThan(500 * 1024, $size, 
                            "CSS file {$asset['file']} should be under 500KB. " .
                            "Actual: " . round($size / 1024, 2) . "KB. " .
                            "Consider further optimization or code splitting.");
                    }
                    
                    // JS should be under 1MB uncompressed
                    if (str_ends_with($asset['file'], '.js')) {
                        $this->assertLessThan(1024 * 1024, $size, 
                            "JS file {$asset['file']} should be under 1MB. " .
                            "Actual: " . round($size / 1024, 2) . "KB. " .
                            "Consider code splitting or lazy loading.");
                    }
                }
            }
        }
    }

    /**
     * Test that vendor chunks are separated for better caching.
     */
    public function test_vendor_chunks_are_separated(): void
    {
        $assetsPath = public_path('build/assets');
        
        if (!is_dir($assetsPath)) {
            $this->markTestSkipped('Assets directory not found. Run "npm run build" first.');
        }
        
        $files = scandir($assetsPath);
        $vendorFiles = array_filter($files, fn($f) => str_contains($f, 'vendor') && str_ends_with($f, '.js'));
        
        $this->assertNotEmpty($vendorFiles, 
            'Vendor chunk should be separated for better caching. ' .
            'This allows vendor code to be cached independently from application code.');
    }

    /**
     * Test that security headers are configured for assets.
     */
    public function test_security_headers_configured(): void
    {
        $htaccessPath = public_path('.htaccess');
        $htaccessContent = File::get($htaccessPath);
        
        // Verify X-Content-Type-Options header
        $this->assertStringContainsString('X-Content-Type-Options', $htaccessContent, 
            '.htaccess should set X-Content-Type-Options header');
        
        $this->assertStringContainsString('nosniff', $htaccessContent, 
            '.htaccess should set nosniff directive');
    }

    /**
     * Test that CORS headers are configured for CDN usage.
     */
    public function test_cors_headers_configured_for_cdn(): void
    {
        $htaccessPath = public_path('.htaccess');
        $htaccessContent = File::get($htaccessPath);
        
        // Verify Access-Control-Allow-Origin is configured
        $this->assertStringContainsString('Access-Control-Allow-Origin', $htaccessContent, 
            '.htaccess should configure CORS headers for CDN usage');
    }

    /**
     * Test that asset versioning strategy documentation exists.
     */
    public function test_asset_versioning_documentation_exists(): void
    {
        $docPath = base_path('docs/Asset-Versioning-Strategy.md');
        
        $this->assertFileExists($docPath, 'Asset versioning strategy documentation should exist');
        
        $content = File::get($docPath);
        
        // Verify documentation covers key topics
        $this->assertStringContainsString('Content-Based Hashing', $content, 
            'Documentation should explain content-based hashing');
        $this->assertStringContainsString('Cache Busting', $content, 
            'Documentation should explain cache busting');
        $this->assertStringContainsString('Vite Configuration', $content, 
            'Documentation should cover Vite configuration');
        $this->assertStringContainsString('Deployment', $content, 
            'Documentation should cover deployment procedures');
    }

    /**
     * Test that browser caching behavior is documented.
     */
    public function test_browser_caching_behavior_documented(): void
    {
        $docPath = base_path('docs/Asset-Versioning-Strategy.md');
        $content = File::get($docPath);
        
        // Verify caching behavior is explained
        $this->assertStringContainsString('Browser Caching', $content, 
            'Documentation should explain browser caching behavior');
        
        $this->assertStringContainsString('First Visit', $content, 
            'Documentation should explain first visit behavior');
        
        $this->assertStringContainsString('Subsequent Visits', $content, 
            'Documentation should explain subsequent visit behavior');
        
        $this->assertStringContainsString('After Asset Update', $content, 
            'Documentation should explain behavior after asset updates');
    }

    /**
     * Test that troubleshooting guide exists for asset loading issues.
     */
    public function test_troubleshooting_guide_exists(): void
    {
        $docPath = base_path('docs/Asset-Versioning-Strategy.md');
        $content = File::get($docPath);
        
        // Verify troubleshooting section exists
        $this->assertStringContainsString('Troubleshooting', $content, 
            'Documentation should include troubleshooting section');
        
        // Verify common issues are covered
        $this->assertStringContainsString('Assets Not Loading', $content, 
            'Documentation should cover asset loading issues');
        
        $this->assertStringContainsString('Old Assets Still Loading', $content, 
            'Documentation should cover cache invalidation issues');
        
        $this->assertStringContainsString('Cache Headers Not Applied', $content, 
            'Documentation should cover cache header issues');
    }

    /**
     * Test that performance metrics are documented.
     */
    public function test_performance_metrics_documented(): void
    {
        $docPath = base_path('docs/Asset-Versioning-Strategy.md');
        $content = File::get($docPath);
        
        // Verify performance metrics are documented
        $this->assertStringContainsString('Performance', $content, 
            'Documentation should include performance metrics');
        
        $this->assertStringContainsString('Cache Hit Rate', $content, 
            'Documentation should mention cache hit rate');
    }

    /**
     * Test that total bundle size is within acceptable limits.
     */
    public function test_total_bundle_size_is_acceptable(): void
    {
        $assetsPath = public_path('build/assets');
        
        if (!is_dir($assetsPath)) {
            $this->markTestSkipped('Assets directory not found. Run "npm run build" first.');
        }
        
        $files = scandir($assetsPath);
        $totalSize = 0;
        
        foreach ($files as $file) {
            if ((str_ends_with($file, '.css') || str_ends_with($file, '.js')) 
                && !str_contains($file, '.gz') 
                && !str_contains($file, '.br')) {
                $totalSize += filesize($assetsPath . '/' . $file);
            }
        }
        
        // Total uncompressed bundle should be reasonable (< 2MB for typical app)
        $this->assertLessThan(2 * 1024 * 1024, $totalSize, 
            "Total bundle size (" . round($totalSize / 1024, 2) . "KB) should be under 2MB. " .
            "Actual: " . round($totalSize / 1024 / 1024, 2) . "MB. " .
            "Consider code splitting, lazy loading, or removing unused dependencies.");
    }
}
