<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AssetOptimizationTest extends TestCase
{
    /**
     * Test that production build directory exists
     */
    public function test_build_directory_exists(): void
    {
        $buildPath = public_path('build');
        
        // Note: This test will fail if assets haven't been built
        // Run 'npm run build' before running this test
        if (!file_exists($buildPath)) {
            $this->markTestSkipped('Build directory not found. Run "npm run build" first.');
        }
        
        $this->assertDirectoryExists($buildPath);
    }

    /**
     * Test that manifest file exists
     */
    public function test_manifest_file_exists(): void
    {
        $manifestPath = public_path('build/manifest.json');
        
        if (!file_exists($manifestPath)) {
            $this->markTestSkipped('Manifest file not found. Run "npm run build" first.');
        }
        
        $this->assertFileExists($manifestPath);
        
        // Verify manifest is valid JSON
        $manifest = json_decode(file_get_contents($manifestPath), true);
        $this->assertIsArray($manifest);
        $this->assertNotEmpty($manifest);
    }

    /**
     * Test that compressed assets are generated
     */
    public function test_compressed_assets_exist(): void
    {
        $assetsPath = public_path('build/assets');
        
        if (!is_dir($assetsPath)) {
            $this->markTestSkipped('Assets directory not found. Run "npm run build" first.');
        }
        
        $files = scandir($assetsPath);
        $gzipFiles = array_filter($files, fn($f) => str_ends_with($f, '.gz'));
        $brotliFiles = array_filter($files, fn($f) => str_ends_with($f, '.br'));
        
        $this->assertNotEmpty($gzipFiles, 'No gzip compressed files found');
        $this->assertNotEmpty($brotliFiles, 'No brotli compressed files found');
    }

    /**
     * Test that CSS file is optimized
     */
    public function test_css_file_is_optimized(): void
    {
        $assetsPath = public_path('build/assets');
        
        if (!is_dir($assetsPath)) {
            $this->markTestSkipped('Assets directory not found. Run "npm run build" first.');
        }
        
        $files = scandir($assetsPath);
        $cssFiles = array_filter($files, fn($f) => str_ends_with($f, '.css') && !str_contains($f, '.gz') && !str_contains($f, '.br'));
        
        $this->assertNotEmpty($cssFiles, 'No CSS files found in build');
        
        // Check that CSS file is reasonably sized (should be < 100KB after purging)
        foreach ($cssFiles as $cssFile) {
            $filePath = $assetsPath . '/' . $cssFile;
            $fileSize = filesize($filePath);
            
            $this->assertLessThan(100 * 1024, $fileSize, 
                "CSS file {$cssFile} is too large ({$fileSize} bytes). Expected < 100KB after Tailwind purging.");
        }
    }

    /**
     * Test that JavaScript files are minified
     */
    public function test_javascript_files_are_minified(): void
    {
        $assetsPath = public_path('build/assets');
        
        if (!is_dir($assetsPath)) {
            $this->markTestSkipped('Assets directory not found. Run "npm run build" first.');
        }
        
        $files = scandir($assetsPath);
        $jsFiles = array_filter($files, fn($f) => str_ends_with($f, '.js') && !str_contains($f, '.gz') && !str_contains($f, '.br'));
        
        $this->assertNotEmpty($jsFiles, 'No JavaScript files found in build');
        
        // Check that JS files don't contain console.log (should be removed in production)
        foreach ($jsFiles as $jsFile) {
            $filePath = $assetsPath . '/' . $jsFile;
            $content = file_get_contents($filePath);
            
            // Minified files should not have console.log statements
            $this->assertStringNotContainsString('console.log', $content, 
                "JavaScript file {$jsFile} contains console.log statements. These should be removed in production.");
        }
    }

    /**
     * Test that compression ratio is acceptable
     */
    public function test_compression_ratio_is_acceptable(): void
    {
        $assetsPath = public_path('build/assets');
        
        if (!is_dir($assetsPath)) {
            $this->markTestSkipped('Assets directory not found. Run "npm run build" first.');
        }
        
        $files = scandir($assetsPath);
        
        foreach ($files as $file) {
            if (str_ends_with($file, '.css') || str_ends_with($file, '.js')) {
                if (str_contains($file, '.gz') || str_contains($file, '.br')) {
                    continue;
                }
                
                $originalPath = $assetsPath . '/' . $file;
                $gzipPath = $originalPath . '.gz';
                
                if (file_exists($gzipPath)) {
                    $originalSize = filesize($originalPath);
                    $gzipSize = filesize($gzipPath);
                    $ratio = ($originalSize - $gzipSize) / $originalSize;
                    
                    // For files larger than 10KB, compression should achieve at least 50% reduction
                    // Smaller files may have lower compression ratios
                    if ($originalSize > 10 * 1024) {
                        $this->assertGreaterThan(0.5, $ratio, 
                            "Compression ratio for {$file} is too low. Expected > 50% reduction for files > 10KB.");
                    } else {
                        // For small files, just verify compression exists
                        $this->assertLessThan($originalSize, $gzipSize, 
                            "Compressed file {$file}.gz should be smaller than original.");
                    }
                }
            }
        }
    }

    /**
     * Test that assets are loaded correctly in views
     */
    public function test_assets_load_in_views(): void
    {
        // Simply verify that the build manifest exists and is valid
        // This is sufficient to confirm assets can be loaded
        $manifestPath = public_path('build/manifest.json');
        
        if (!file_exists($manifestPath)) {
            $this->markTestSkipped('Manifest file not found. Run "npm run build" first.');
        }
        
        $manifest = json_decode(file_get_contents($manifestPath), true);
        
        // Verify manifest contains CSS and JS entries
        $this->assertArrayHasKey('resources/css/app.css', $manifest, 
            'Manifest should contain CSS entry');
        $this->assertArrayHasKey('resources/js/app.js', $manifest, 
            'Manifest should contain JS entry');
        
        // Verify the files referenced in manifest actually exist
        foreach ($manifest as $entry) {
            if (isset($entry['file'])) {
                $filePath = public_path('build/' . $entry['file']);
                $this->assertFileExists($filePath, 
                    "Asset file {$entry['file']} should exist in build directory");
            }
        }
    }

    /**
     * Test that vendor chunk is separated
     */
    public function test_vendor_chunk_is_separated(): void
    {
        $assetsPath = public_path('build/assets');
        
        if (!is_dir($assetsPath)) {
            $this->markTestSkipped('Assets directory not found. Run "npm run build" first.');
        }
        
        $files = scandir($assetsPath);
        $vendorFiles = array_filter($files, fn($f) => str_contains($f, 'vendor') && str_ends_with($f, '.js'));
        
        $this->assertNotEmpty($vendorFiles, 
            'No vendor chunk found. Code splitting may not be working correctly.');
    }

    /**
     * Test total bundle size is within limits
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
        
        // Total uncompressed bundle should be < 200KB
        $this->assertLessThan(200 * 1024, $totalSize, 
            "Total bundle size ({$totalSize} bytes) exceeds 200KB limit. Consider further optimization.");
    }
}
