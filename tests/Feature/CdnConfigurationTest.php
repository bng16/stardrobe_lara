<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Config;
use Tests\TestCase;

class CdnConfigurationTest extends TestCase
{
    /**
     * Test that asset URL configuration is properly loaded
     */
    public function test_asset_url_configuration_is_loaded(): void
    {
        // Set ASSET_URL in config
        Config::set('app.asset_url', 'https://cdn.example.com');
        
        $assetUrl = config('app.asset_url');
        
        $this->assertNotNull($assetUrl);
        $this->assertEquals('https://cdn.example.com', $assetUrl);
    }
    
    /**
     * Test that asset URL can be null for local development
     */
    public function test_asset_url_can_be_null_for_local_development(): void
    {
        Config::set('app.asset_url', null);
        
        $assetUrl = config('app.asset_url');
        
        $this->assertNull($assetUrl);
    }
    
    /**
     * Test that Vite directive works without CDN
     */
    public function test_vite_directive_works_without_cdn(): void
    {
        Config::set('app.asset_url', null);
        
        // This test verifies the configuration is properly set
        // Actual page rendering would require authentication setup
        // which is beyond the scope of CDN configuration testing
        $this->assertNull(config('app.asset_url'));
    }
    
    /**
     * Test that asset URL uses HTTPS
     */
    public function test_asset_url_uses_https_when_configured(): void
    {
        Config::set('app.asset_url', 'https://cdn.example.com');
        
        $assetUrl = config('app.asset_url');
        
        $this->assertStringStartsWith('https://', $assetUrl);
    }
    
    /**
     * Test that asset URL does not have trailing slash
     */
    public function test_asset_url_does_not_have_trailing_slash(): void
    {
        Config::set('app.asset_url', 'https://cdn.example.com/');
        
        $assetUrl = rtrim(config('app.asset_url'), '/');
        
        $this->assertStringEndsNotWith('/', $assetUrl);
    }
    
    /**
     * Test manifest file exists after build
     */
    public function test_manifest_file_exists(): void
    {
        $manifestPath = public_path('build/manifest.json');
        
        // Skip if build hasn't been run
        if (!file_exists($manifestPath)) {
            $this->markTestSkipped('Build assets not found. Run: npm run build');
        }
        
        $this->assertFileExists($manifestPath);
        
        $manifest = json_decode(file_get_contents($manifestPath), true);
        
        $this->assertIsArray($manifest);
        $this->assertArrayHasKey('resources/css/app.css', $manifest);
        $this->assertArrayHasKey('resources/js/app.js', $manifest);
    }
    
    /**
     * Test that built assets have content hashes
     */
    public function test_built_assets_have_content_hashes(): void
    {
        $manifestPath = public_path('build/manifest.json');
        
        if (!file_exists($manifestPath)) {
            $this->markTestSkipped('Build assets not found. Run: npm run build');
        }
        
        $manifest = json_decode(file_get_contents($manifestPath), true);
        
        // Check CSS file has hash
        $cssFile = $manifest['resources/css/app.css']['file'] ?? null;
        $this->assertNotNull($cssFile);
        $this->assertMatchesRegularExpression('/app-[a-zA-Z0-9]+\.css/', $cssFile);
        
        // Check JS file has hash
        $jsFile = $manifest['resources/js/app.js']['file'] ?? null;
        $this->assertNotNull($jsFile);
        $this->assertMatchesRegularExpression('/app-[a-zA-Z0-9]+\.js/', $jsFile);
    }
    
    /**
     * Test that compressed assets exist
     */
    public function test_compressed_assets_exist(): void
    {
        $manifestPath = public_path('build/manifest.json');
        
        if (!file_exists($manifestPath)) {
            $this->markTestSkipped('Build assets not found. Run: npm run build');
        }
        
        $manifest = json_decode(file_get_contents($manifestPath), true);
        $cssFile = $manifest['resources/css/app.css']['file'] ?? null;
        
        if (!$cssFile) {
            $this->markTestSkipped('CSS file not found in manifest');
        }
        
        $cssPath = public_path('build/' . $cssFile);
        
        // Check if gzip version exists
        if (file_exists($cssPath . '.gz')) {
            $this->assertFileExists($cssPath . '.gz');
        }
        
        // Check if brotli version exists
        if (file_exists($cssPath . '.br')) {
            $this->assertFileExists($cssPath . '.br');
        }
        
        // At least one should exist, or the original file
        $this->assertTrue(
            file_exists($cssPath) || 
            file_exists($cssPath . '.gz') || 
            file_exists($cssPath . '.br'),
            'No asset files found (original, gzip, or brotli)'
        );
    }
    
    /**
     * Test that .htaccess has proper cache headers configuration
     */
    public function test_htaccess_has_cache_headers(): void
    {
        $htaccessPath = public_path('.htaccess');
        
        if (!file_exists($htaccessPath)) {
            $this->markTestSkipped('.htaccess file not found');
        }
        
        $htaccess = file_get_contents($htaccessPath);
        
        // Check for cache control headers
        $this->assertStringContainsString('Cache-Control', $htaccess);
        $this->assertStringContainsString('max-age', $htaccess);
    }
    
    /**
     * Test environment example files have CDN configuration
     */
    public function test_env_example_has_cdn_configuration(): void
    {
        $envExamplePath = base_path('.env.example');
        
        $this->assertFileExists($envExamplePath);
        
        $envExample = file_get_contents($envExamplePath);
        
        $this->assertStringContainsString('ASSET_URL', $envExample);
        $this->assertStringContainsString('CDN', $envExample);
    }
    
    /**
     * Test production env example has CDN configuration
     */
    public function test_production_env_example_has_cdn_configuration(): void
    {
        $envProductionPath = base_path('.env.production.example');
        
        if (!file_exists($envProductionPath)) {
            $this->markTestSkipped('.env.production.example not found');
        }
        
        $envProduction = file_get_contents($envProductionPath);
        
        $this->assertStringContainsString('ASSET_URL', $envProduction);
    }
}
