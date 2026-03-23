<?php

namespace Tests\Feature;

use App\Enums\UserRole;
use App\Models\CreatorShop;
use App\Models\User;
use App\Rules\SecureImageUpload;
use App\Services\FileSecurityService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class FileUploadSecurityTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        Storage::fake('s3');
    }

    /** @test */
    public function it_validates_mime_type_for_product_images()
    {
        $creator = User::factory()->create(['role' => UserRole::Creator]);
        CreatorShop::factory()->create(['user_id' => $creator->id, 'is_onboarded' => true]);

        // Create a fake text file disguised as an image
        $fakeImage = UploadedFile::fake()->create('malicious.jpg', 100, 'text/plain');

        $response = $this->actingAs($creator)->post(route('creator.products.store'), [
            'title' => 'Test Product',
            'description' => 'Test Description',
            'reserve_price' => 100,
            'auction_start' => now()->addDay(),
            'auction_end' => now()->addDays(2),
            'images' => [$fakeImage],
        ]);

        $response->assertSessionHasErrors('images.0');
    }

    /** @test */
    public function it_rejects_files_exceeding_size_limit_for_product_images()
    {
        $creator = User::factory()->create(['role' => UserRole::Creator]);
        CreatorShop::factory()->create(['user_id' => $creator->id, 'is_onboarded' => true]);

        // Create a file larger than 5MB
        $largeImage = UploadedFile::fake()->image('large.jpg')->size(6000); // 6MB

        $response = $this->actingAs($creator)->post(route('creator.products.store'), [
            'title' => 'Test Product',
            'description' => 'Test Description',
            'reserve_price' => 100,
            'auction_start' => now()->addDay(),
            'auction_end' => now()->addDays(2),
            'images' => [$largeImage],
        ]);

        $response->assertSessionHasErrors('images.0');
    }

    /** @test */
    public function it_rejects_files_exceeding_size_limit_for_profile_images()
    {
        $creator = User::factory()->create(['role' => UserRole::Creator]);
        CreatorShop::factory()->create(['user_id' => $creator->id, 'is_onboarded' => false]);

        // Create a file larger than 2MB
        $largeImage = UploadedFile::fake()->image('large.jpg')->size(3000); // 3MB

        $response = $this->actingAs($creator)->post(route('creator.onboarding.store'), [
            'shop_name' => 'Test Shop',
            'bio' => 'Test Bio',
            'profile_image' => $largeImage,
        ]);

        $response->assertSessionHasErrors('profile_image');
    }

    /** @test */
    public function it_accepts_valid_jpeg_images_for_products()
    {
        $creator = User::factory()->create(['role' => UserRole::Creator]);
        CreatorShop::factory()->create(['user_id' => $creator->id, 'is_onboarded' => true]);

        $validImage = UploadedFile::fake()->image('valid.jpg', 800, 600)->size(1000); // 1MB

        $response = $this->actingAs($creator)->post(route('creator.products.store'), [
            'title' => 'Test Product',
            'description' => 'Test Description',
            'reserve_price' => 100,
            'auction_start' => now()->addDay(),
            'auction_end' => now()->addDays(2),
            'images' => [$validImage],
        ]);

        $response->assertRedirect(route('creator.products.index'));
        $response->assertSessionHas('success');
    }

    /** @test */
    public function it_accepts_valid_png_images_for_products()
    {
        $creator = User::factory()->create(['role' => UserRole::Creator]);
        CreatorShop::factory()->create(['user_id' => $creator->id, 'is_onboarded' => true]);

        $validImage = UploadedFile::fake()->image('valid.png', 800, 600)->size(1000); // 1MB

        $response = $this->actingAs($creator)->post(route('creator.products.store'), [
            'title' => 'Test Product',
            'description' => 'Test Description',
            'reserve_price' => 100,
            'auction_start' => now()->addDay(),
            'auction_end' => now()->addDays(2),
            'images' => [$validImage],
        ]);

        $response->assertRedirect(route('creator.products.index'));
        $response->assertSessionHas('success');
    }

    /** @test */
    public function it_rejects_files_with_suspicious_extensions()
    {
        $rule = new SecureImageUpload(5120);
        
        // Create a file with double extension
        $suspiciousFile = UploadedFile::fake()->create('image.php.jpg', 100);
        
        $fails = false;
        $rule->validate('test', $suspiciousFile, function($message) use (&$fails) {
            $fails = true;
        });

        $this->assertTrue($fails, 'File with suspicious extension should be rejected');
    }

    /** @test */
    public function security_service_detects_php_code_in_files()
    {
        $service = new FileSecurityService();
        
        // Create a file with PHP code
        $maliciousContent = '<?php system("rm -rf /"); ?>';
        $tempFile = tmpfile();
        fwrite($tempFile, $maliciousContent);
        $tempPath = stream_get_meta_data($tempFile)['uri'];
        
        $file = new UploadedFile($tempPath, 'malicious.jpg', 'image/jpeg', null, true);
        
        $result = $service->scanFile($file);
        
        $this->assertFalse($result['safe']);
        $this->assertNotEmpty($result['issues']);
        $this->assertStringContainsString('PHP code', implode(' ', $result['issues']));
        
        fclose($tempFile);
    }

    /** @test */
    public function security_service_detects_script_tags_in_files()
    {
        $service = new FileSecurityService();
        
        // Create a file with script tags
        $maliciousContent = '<script>alert("XSS")</script>';
        $tempFile = tmpfile();
        fwrite($tempFile, $maliciousContent);
        $tempPath = stream_get_meta_data($tempFile)['uri'];
        
        $file = new UploadedFile($tempPath, 'malicious.jpg', 'image/jpeg', null, true);
        
        $result = $service->scanFile($file);
        
        $this->assertFalse($result['safe']);
        $this->assertNotEmpty($result['issues']);
        
        fclose($tempFile);
    }

    /** @test */
    public function security_service_detects_null_bytes_in_files()
    {
        $service = new FileSecurityService();
        
        // Create a file with null bytes
        $maliciousContent = "image.jpg\0.php";
        $tempFile = tmpfile();
        fwrite($tempFile, $maliciousContent);
        $tempPath = stream_get_meta_data($tempFile)['uri'];
        
        $file = new UploadedFile($tempPath, 'malicious.jpg', 'image/jpeg', null, true);
        
        $result = $service->scanFile($file);
        
        $this->assertFalse($result['safe']);
        $this->assertNotEmpty($result['issues']);
        $this->assertStringContainsString('null bytes', implode(' ', $result['issues']));
        
        fclose($tempFile);
    }

    /** @test */
    public function it_provides_descriptive_error_messages_for_invalid_uploads()
    {
        $creator = User::factory()->create(['role' => UserRole::Creator]);
        CreatorShop::factory()->create(['user_id' => $creator->id, 'is_onboarded' => true]);

        // Create an invalid file
        $invalidFile = UploadedFile::fake()->create('document.pdf', 100);

        $response = $this->actingAs($creator)->post(route('creator.products.store'), [
            'title' => 'Test Product',
            'description' => 'Test Description',
            'reserve_price' => 100,
            'auction_start' => now()->addDay(),
            'auction_end' => now()->addDays(2),
            'images' => [$invalidFile],
        ]);

        $response->assertSessionHasErrors('images.0');
        
        $errors = session('errors');
        $this->assertNotNull($errors);
        $this->assertStringContainsString('type', $errors->first('images.0'));
    }

    /** @test */
    public function it_enforces_different_size_limits_for_product_and_profile_images()
    {
        $creator = User::factory()->create(['role' => UserRole::Creator]);
        CreatorShop::factory()->create(['user_id' => $creator->id, 'is_onboarded' => false]);

        // 3MB image - should fail for profile (2MB limit) but pass for product (5MB limit)
        $mediumImage = UploadedFile::fake()->image('medium.jpg')->size(3000);

        // Test profile image (should fail)
        $response = $this->actingAs($creator)->post(route('creator.onboarding.store'), [
            'shop_name' => 'Test Shop',
            'bio' => 'Test Bio',
            'profile_image' => $mediumImage,
        ]);

        $response->assertSessionHasErrors('profile_image');

        // Mark as onboarded for product test
        $creator->creatorShop->update(['is_onboarded' => true]);

        // Test product image (should pass)
        $mediumImage2 = UploadedFile::fake()->image('medium2.jpg')->size(3000);
        
        $response = $this->actingAs($creator)->post(route('creator.products.store'), [
            'title' => 'Test Product',
            'description' => 'Test Description',
            'reserve_price' => 100,
            'auction_start' => now()->addDay(),
            'auction_end' => now()->addDays(2),
            'images' => [$mediumImage2],
        ]);

        $response->assertRedirect(route('creator.products.index'));
    }

    /** @test */
    public function it_validates_all_images_in_multi_image_upload()
    {
        $creator = User::factory()->create(['role' => UserRole::Creator]);
        CreatorShop::factory()->create(['user_id' => $creator->id, 'is_onboarded' => true]);

        $validImage = UploadedFile::fake()->image('valid.jpg')->size(1000);
        $invalidImage = UploadedFile::fake()->create('invalid.txt', 100);

        $response = $this->actingAs($creator)->post(route('creator.products.store'), [
            'title' => 'Test Product',
            'description' => 'Test Description',
            'reserve_price' => 100,
            'auction_start' => now()->addDay(),
            'auction_end' => now()->addDays(2),
            'images' => [$validImage, $invalidImage],
        ]);

        $response->assertSessionHasErrors('images.1');
    }
}
