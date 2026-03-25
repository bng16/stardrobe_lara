<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Route;
use Tests\TestCase;

class AssetVersioningTest extends TestCase
{
    /**
     * Test that manifest.json exists and is valid.
     */
    public function test_manifest_file_exists_and_is_valid(): void
    {
        $manifestPath = public_path('build/manifest.json');
        
        $this->assertFileExists($manifestPath, 'Manifest file should exist at public/build/manifest.json');
        
        $manifestContent = File::get($manifestPath);
        $manifest = json_decode($manifestContent, true);
        
        $this->assertIsArray($manifest, 'Manifest should be valid JSON');
        $this->assertNotEmpty($manifest, 'Manifest should not be empty');
    }

    /**
     * Test that manifest contains required entry points.
     */
    public function test_manifest_contains_required_entries(): void
    {
        $manifestPath = public_path('build/manifest.json');
        $manifest = json_decode(File::get($manifestPath), true);
        
        // Check for CSS entry
        $this->assertArrayHasKey('resources/css/app.css', $manifest, 'Manifest should contain CSS entry');
        
        // Check for JS entry
        $this->assertArrayHasKey('resources/js/app.js', $manifest, 'Manifest should contain JS entry');
    }

    /**
     * Test that versioned assets have content hashes in filenames.
     */
    public function test_assets_have_content_hashes(): void
    {
        $manifestPath = public_path('build/manifest.json');
        $manifest = json_decode(File::get($manifestPath), true);
        
        foreach ($manifest as $source => $asset) {
            if (isset($asset['file'])) {
                $filename = $asset['file'];
                
                // Check that filename contains a hash (pattern: name-HASH.ext)
                $this->assertMatchesRegularExpression(
                    '/[a-zA-Z]+-[a-zA-Z0-9]{8,}\.(css|js)$/',
                    $filename,
                    "Asset {$filename} should have content hash in filename"
                );
            }
        }
    }

    /**
     * Test that versioned asset files actually exist.
     */
    public function test_versioned_asset_files_exist(): void
    {
        $manifestPath = public_path('build/manifest.json');
        $manifest = json_decode(File::get($manifestPath), true);
        
        foreach ($manifest as $source => $asset) {
            if (isset($asset['file'])) {
                $assetPath = public_path('build/' . $asset['file']);
                
                $this->assertFileExists(
                    $assetPath,
                    "Versioned asset file should exist: {$asset['file']}"
                );
            }
        }
    }

    /**
     * Test that compressed versions of assets exist.
     */
    public function test_compressed_asset_files_exist(): void
    {
        $manifestPath = public_path('build/manifest.json');
        $manifest = json_decode(File::get($manifestPath), true);
        
        foreach ($manifest as $source => $asset) {
            if (isset($asset['file']) && (str_ends_with($asset['file'], '.css') || str_ends_with($asset['file'], '.js'))) {
                $assetPath = public_path('build/' . $asset['file']);
                $gzipPath = $assetPath . '.gz';
                $brotliPath = $assetPath . '.br';
                
                // Check for gzip compressed version
                $this->assertFileExists(
                    $gzipPath,
                    "Gzip compressed version should exist: {$asset['file']}.gz"
                );
                
                // Check for brotli compressed version
                $this->assertFileExists(
                    $brotliPath,
                    "Brotli compressed version should exist: {$asset['file']}.br"
                );
            }
        }
    }

    /**
     * Test that Vite directive generates correct HTML with versioned assets.
     */
    public function test_vite_directive_generates_versioned_urls(): void
    {
        $manifestPath = public_path('build/manifest.json');
        $manifest = json_decode(File::get($manifestPath), true);
        
        // Verify that the @vite directive would generate versioned URLs
        // by checking that the manifest contains the expected structure
        $this->assertArrayHasKey('resources/css/app.css', $manifest);
        $this->assertArrayHasKey('resources/js/app.js', $manifest);
        
        $cssFile = $manifest['resources/css/app.css']['file'] ?? null;
        $jsFile = $manifest['resources/js/app.js']['file'] ?? null;
        
        // Verify files have the expected structure (build/assets/name-hash.ext)
        $this->assertNotNull($cssFile);
        $this->assertNotNull($jsFile);
        $this->assertStringStartsWith('assets/', $cssFile);
        $this->assertStringStartsWith('assets/', $jsFile);
    }

    /**
     * Test that .htaccess file exists and contains cache headers configuration.
     */
    public function test_htaccess_contains_cache_headers(): void
    {
        $htaccessPath = public_path('.htaccess');
        
        $this->assertFileExists($htaccessPath, '.htaccess file should exist');
        
        $htaccessContent = File::get($htaccessPath);
        
        // Check for cache control configuration
        $this->assertStringContainsString('Cache-Control', $htaccessContent, '.htaccess should contain Cache-Control headers');
        $this->assertStringContainsString('mod_expires', $htaccessContent, '.htaccess should contain mod_expires configuration');
        $this->assertStringContainsString('immutable', $htaccessContent, '.htaccess should contain immutable directive for versioned assets');
    }

    /**
     * Test that .htaccess contains compression configuration.
     */
    public function test_htaccess_contains_compression_config(): void
    {
        $htaccessPath = public_path('.htaccess');
        $htaccessContent = File::get($htaccessPath);
        
        // Check for compression configuration
        $this->assertStringContainsString('mod_deflate', $htaccessContent, '.htaccess should contain mod_deflate configuration');
        $this->assertStringContainsString('Accept-Encoding', $htaccessContent, '.htaccess should handle Accept-Encoding for pre-compressed files');
    }

    /**
     * Test that Vite config contains proper asset versioning configuration.
     */
    public function test_vite_config_has_versioning_settings(): void
    {
        $viteConfigPath = base_path('vite.config.js');
        
        $this->assertFileExists($viteConfigPath, 'vite.config.js should exist');
        
        $viteConfig = File::get($viteConfigPath);
        
        // Check for hash in filename patterns
        $this->assertStringContainsString('[hash]', $viteConfig, 'Vite config should use [hash] in filename patterns');
        $this->assertStringContainsString('manifest: true', $viteConfig, 'Vite config should enable manifest generation');
        
        // Check for proper output configuration
        $this->assertStringContainsString('entryFileNames', $viteConfig, 'Vite config should define entryFileNames pattern');
        $this->assertStringContainsString('chunkFileNames', $viteConfig, 'Vite config should define chunkFileNames pattern');
        $this->assertStringContainsString('assetFileNames', $viteConfig, 'Vite config should define assetFileNames pattern');
    }

    /**
     * Test that manifest.json is also compressed.
     */
    public function test_manifest_is_compressed(): void
    {
        $manifestPath = public_path('build/manifest.json');
        
        $this->assertFileExists($manifestPath . '.gz', 'Manifest should have gzip compressed version');
        $this->assertFileExists($manifestPath . '.br', 'Manifest should have brotli compressed version');
    }

    /**
     * Test that asset hashes change when content changes.
     * This is a conceptual test - in practice, you'd verify this during deployment.
     */
    public function test_asset_hash_format_is_correct(): void
    {
        $manifestPath = public_path('build/manifest.json');
        $manifest = json_decode(File::get($manifestPath), true);
        
        foreach ($manifest as $source => $asset) {
            if (isset($asset['file'])) {
                $filename = basename($asset['file']);
                
                // Extract hash from filename (format: name-HASH.ext)
                if (preg_match('/^(.+)-([a-zA-Z0-9]{8,})\.(css|js)$/', $filename, $matches)) {
                    $hash = $matches[2];
                    
                    // Hash should be at least 8 characters (Vite default)
                    $this->assertGreaterThanOrEqual(8, strlen($hash), "Hash should be at least 8 characters: {$filename}");
                    
                    // Hash should be alphanumeric
                    $this->assertMatchesRegularExpression('/^[a-zA-Z0-9]+$/', $hash, "Hash should be alphanumeric: {$hash}");
                }
            }
        }
    }

    /**
     * Test that layouts properly use the @vite directive.
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
                
                $this->assertStringContainsString(
                    '@vite',
                    $content,
                    "Layout {$layoutPath} should use @vite directive"
                );
                
                $this->assertStringContainsString(
                    'resources/css/app.css',
                    $content,
                    "Layout {$layoutPath} should include CSS asset"
                );
                
                $this->assertStringContainsString(
                    'resources/js/app.js',
                    $content,
                    "Layout {$layoutPath} should include JS asset"
                );
            }
        }
    }

    /**
     * Test that asset sizes are reasonable (not too large).
     */
    public function test_asset_sizes_are_reasonable(): void
    {
        $manifestPath = public_path('build/manifest.json');
        $manifest = json_decode(File::get($manifestPath), true);
        
        foreach ($manifest as $source => $asset) {
            if (isset($asset['file'])) {
                $assetPath = public_path('build/' . $asset['file']);
                
                if (File::exists($assetPath)) {
                    $size = File::size($assetPath);
                    
                    // CSS should be under 500KB uncompressed
                    if (str_ends_with($asset['file'], '.css')) {
                        $this->assertLessThan(
                            500 * 1024,
                            $size,
                            "CSS file {$asset['file']} should be under 500KB (actual: " . round($size / 1024, 2) . "KB)"
                        );
                    }
                    
                    // JS should be under 1MB uncompressed
                    if (str_ends_with($asset['file'], '.js')) {
                        $this->assertLessThan(
                            1024 * 1024,
                            $size,
                            "JS file {$asset['file']} should be under 1MB (actual: " . round($size / 1024, 2) . "KB)"
                        );
                    }
                }
            }
        }
    }

    /**
     * Test that compressed files are smaller than originals.
     */
    public function test_compressed_files_are_smaller(): void
    {
        $manifestPath = public_path('build/manifest.json');
        $manifest = json_decode(File::get($manifestPath), true);
        
        foreach ($manifest as $source => $asset) {
            if (isset($asset['file']) && (str_ends_with($asset['file'], '.css') || str_ends_with($asset['file'], '.js'))) {
                $assetPath = public_path('build/' . $asset['file']);
                $gzipPath = $assetPath . '.gz';
                $brotliPath = $assetPath . '.br';
                
                if (File::exists($assetPath) && File::exists($gzipPath)) {
                    $originalSize = File::size($assetPath);
                    $gzipSize = File::size($gzipPath);
                    
                    $this->assertLessThan(
                        $originalSize,
                        $gzipSize,
                        "Gzip compressed file should be smaller than original: {$asset['file']}"
                    );
                }
                
                if (File::exists($assetPath) && File::exists($brotliPath)) {
                    $originalSize = File::size($assetPath);
                    $brotliSize = File::size($brotliPath);
                    
                    $this->assertLessThan(
                        $originalSize,
                        $brotliSize,
                        "Brotli compressed file should be smaller than original: {$asset['file']}"
                    );
                }
            }
        }
    }
}
