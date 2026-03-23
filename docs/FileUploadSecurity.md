# File Upload Security Implementation

## Overview

This document describes the file upload security implementation for the Creator Marketplace application, addressing Requirements 17.1-17.5 from the specification.

## Implementation Summary

The file upload security system consists of three main components:

1. **SecureImageUpload Validation Rule** - Custom Laravel validation rule for comprehensive image validation
2. **FileSecurityService** - Service class for malicious content scanning
3. **Controller Integration** - Updated controllers to use enhanced security

## Components

### 1. SecureImageUpload Validation Rule

**Location:** `app/Rules/SecureImageUpload.php`

**Features:**
- MIME type validation from file content (not just extension)
- File size validation with configurable limits
- Extension validation (jpeg, jpg, png, webp)
- Double extension detection (e.g., `image.php.jpg`)
- Image integrity verification using `getimagesize()`
- Protection against executable file uploads

**Usage:**
```php
// For product images (5MB limit)
'images.*' => [new SecureImageUpload(5120)]

// For profile images (2MB limit)
'profile_image' => ['nullable', new SecureImageUpload(2048)]
```

**Validation Checks:**
1. ✅ File is a valid UploadedFile instance
2. ✅ File upload was successful
3. ✅ Extension is in allowed list (jpeg, jpg, png, webp)
4. ✅ MIME type matches allowed types (image/jpeg, image/png, image/webp)
5. ✅ File size does not exceed limit
6. ✅ Filename does not contain suspicious patterns (.php, .exe, etc.)
7. ✅ File is a valid image (verified with getimagesize())
8. ✅ Image MIME type matches file content

### 2. FileSecurityService

**Location:** `app/Services/FileSecurityService.php`

**Features:**
- Scans uploaded files for malicious content
- Detects PHP code injection attempts
- Detects XSS script tags
- Detects null byte attacks
- Validates file headers match declared MIME types
- Optional ClamAV integration for virus scanning
- Comprehensive logging of security issues

**Methods:**

#### `scanFile(UploadedFile $file): array`
Performs comprehensive security scan on uploaded file.

**Returns:**
```php
[
    'safe' => bool,      // true if file passed all checks
    'issues' => array    // array of detected issues
]
```

**Security Checks:**
1. ✅ PHP code detection (<?php, eval, exec, system, etc.)
2. ✅ Script tag detection (<script>)
3. ✅ Null byte detection (path traversal attempts)
4. ✅ File header validation (magic bytes)

#### `scanWithClamAV(UploadedFile $file): array`
Optional integration with ClamAV antivirus scanner.

**Note:** Requires ClamAV to be installed on the server:
```bash
# Install ClamAV
apt-get install clamav clamav-daemon

# Install PHP extension
pecl install clamav
```

### 3. Controller Integration

#### ProductController

**Location:** `app/Http/Controllers/Creator/ProductController.php`

**Changes:**
- Added `SecureImageUpload` rule with 5MB limit
- Injected `FileSecurityService` into store method
- Added security scan before storing images
- Returns descriptive error messages on validation failure

**Example:**
```php
public function store(Request $request, FileSecurityService $securityService): RedirectResponse
{
    $validated = $request->validate([
        'images.*' => [new SecureImageUpload(5120)], // 5MB
    ]);

    foreach ($request->file('images') as $image) {
        $scanResult = $securityService->scanFile($image);
        
        if (!$scanResult['safe']) {
            return back()->withErrors([
                'images' => 'File upload rejected: ' . implode(', ', $scanResult['issues'])
            ])->withInput();
        }
    }
    
    // ... proceed with upload
}
```

#### OnboardingController

**Location:** `app/Http/Controllers/Creator/OnboardingController.php`

**Changes:**
- Added `SecureImageUpload` rule with 2MB limit for profile images
- Added `SecureImageUpload` rule with 5MB limit for banner images
- Injected `FileSecurityService` into store method
- Added security scan for both profile and banner images

## File Size Limits

As per Requirements 17.2 and 17.3:

| Image Type | Size Limit | Usage |
|------------|------------|-------|
| Product Images | 5MB (5120KB) | Product listings |
| Profile Images | 2MB (2048KB) | Creator profile pictures |
| Banner Images | 5MB (5120KB) | Creator shop banners |

## Allowed File Types

As per Requirement 17.1:

| Extension | MIME Type | Supported |
|-----------|-----------|-----------|
| .jpeg | image/jpeg | ✅ |
| .jpg | image/jpeg | ✅ |
| .png | image/png | ✅ |
| .webp | image/webp | ✅ |

## Security Features

### MIME Type Validation (Requirement 17.1)

The system validates MIME types at multiple levels:

1. **Extension Check:** Validates file extension is in allowed list
2. **MIME Type Check:** Validates MIME type from file metadata
3. **Content Check:** Uses `getimagesize()` to verify file is actually an image
4. **Header Check:** Validates magic bytes match declared MIME type

This multi-layer approach prevents:
- Extension spoofing (renaming `malicious.php` to `malicious.jpg`)
- MIME type manipulation
- Polyglot files (files that are valid in multiple formats)

### File Size Validation (Requirements 17.2, 17.3)

File size is validated in kilobytes with clear error messages:

```php
if ($fileSizeKb > $this->maxSizeKb) {
    $maxSizeMb = round($this->maxSizeKb / 1024, 1);
    $fail("The {$attribute} must not be larger than {$maxSizeMb}MB.");
}
```

### Malicious Content Scanning (Requirements 17.4, 17.5)

The `FileSecurityService` scans for:

1. **PHP Code Injection:**
   - `<?php`, `<?=`, `<%`
   - `eval()`, `exec()`, `system()`, `shell_exec()`
   - `base64_decode()`, `passthru()`

2. **XSS Attempts:**
   - `<script>` tags
   - JavaScript event handlers

3. **Path Traversal:**
   - Null bytes (`\0`)
   - Directory traversal sequences

4. **File Header Validation:**
   - JPEG: `\xFF\xD8\xFF`
   - PNG: `\x89\x50\x4E\x47\x0D\x0A\x1A\x0A`
   - WebP: `RIFF....WEBP`

### Descriptive Error Messages (Requirement 17.5)

All validation failures return descriptive error messages:

```php
// Extension error
"The images.0 must be a file of type: jpeg, jpg, png, webp."

// MIME type error
"The images.0 file type is not allowed. Detected type: text/plain."

// Size error
"The images.0 must not be larger than 5.0MB."

// Suspicious filename
"The images.0 filename contains suspicious patterns."

// Invalid image
"The images.0 is not a valid image file."

// Security scan failure
"File upload rejected: File contains suspicious PHP code, File contains null bytes"
```

## Testing

### Test Coverage

**Location:** `tests/Feature/FileUploadSecurityTest.php`

The test suite includes 13 comprehensive tests:

1. ✅ MIME type validation for product images
2. ✅ File size limit enforcement for product images (5MB)
3. ✅ File size limit enforcement for profile images (2MB)
4. ✅ Valid JPEG image acceptance
5. ✅ Valid PNG image acceptance
6. ✅ Suspicious extension detection
7. ✅ PHP code detection in files
8. ✅ Script tag detection in files
9. ✅ Null byte detection in files
10. ✅ Descriptive error messages
11. ✅ Different size limits for different image types
12. ✅ Multi-image upload validation
13. ✅ File header validation

### Running Tests

```bash
# Run all file upload security tests
php artisan test --filter=FileUploadSecurityTest

# Or using PHPUnit directly
./vendor/bin/phpunit --filter=FileUploadSecurityTest

# Run with coverage
php artisan test --filter=FileUploadSecurityTest --coverage
```

## Optional: ClamAV Integration

For production environments requiring virus scanning:

### Installation

```bash
# Ubuntu/Debian
sudo apt-get update
sudo apt-get install clamav clamav-daemon

# Start ClamAV daemon
sudo systemctl start clamav-daemon
sudo systemctl enable clamav-daemon

# Update virus definitions
sudo freshclam
```

### PHP Extension

```bash
# Install ClamAV PHP extension
sudo pecl install clamav

# Add to php.ini
echo "extension=clamav.so" | sudo tee -a /etc/php/8.3/cli/php.ini
echo "extension=clamav.so" | sudo tee -a /etc/php/8.3/fpm/php.ini

# Restart PHP-FPM
sudo systemctl restart php8.3-fpm
```

### Usage

```php
// In controller
$scanResult = $securityService->scanWithClamAV($file);

if (!$scanResult['safe']) {
    return back()->withErrors([
        'file' => $scanResult['message']
    ]);
}
```

## Security Best Practices

1. **Never Trust Client Input:** Always validate on server-side
2. **Multiple Validation Layers:** Extension + MIME + Content + Header
3. **Fail Secure:** Reject files if any check fails
4. **Log Security Events:** All rejected uploads are logged
5. **Regular Updates:** Keep ClamAV virus definitions updated
6. **Storage Isolation:** Store uploads outside web root when possible
7. **Content-Type Headers:** Set correct headers when serving files

## Monitoring and Logging

All security events are logged to Laravel's log system:

```php
Log::warning('File security scan detected issues', [
    'filename' => $file->getClientOriginalName(),
    'mime_type' => $file->getMimeType(),
    'issues' => $issues,
]);
```

**Log Location:** `storage/logs/laravel.log`

**Recommended Monitoring:**
- Set up alerts for repeated upload failures from same user
- Monitor for patterns of malicious upload attempts
- Track file upload success/failure rates
- Review security logs regularly

## Requirements Validation

| Requirement | Implementation | Status |
|-------------|----------------|--------|
| 17.1 - MIME type validation | SecureImageUpload rule + FileSecurityService | ✅ Complete |
| 17.2 - 5MB limit for products | SecureImageUpload(5120) | ✅ Complete |
| 17.3 - 2MB limit for profiles | SecureImageUpload(2048) | ✅ Complete |
| 17.4 - Malicious file scanning | FileSecurityService::scanFile() | ✅ Complete |
| 17.5 - Descriptive error messages | Custom validation messages | ✅ Complete |

## Future Enhancements

1. **Image Optimization:** Automatically resize/compress uploaded images
2. **CDN Integration:** Serve images through CDN for better performance
3. **Thumbnail Generation:** Create multiple sizes for responsive images
4. **Watermarking:** Add watermarks to product images
5. **EXIF Data Stripping:** Remove metadata for privacy
6. **Advanced Virus Scanning:** Integrate with cloud-based scanning services
7. **Rate Limiting:** Limit upload frequency per user
8. **Quarantine System:** Temporarily store suspicious files for review

## Troubleshooting

### Common Issues

**Issue:** "File upload rejected: File header does not match declared type"
**Solution:** Ensure file is a genuine image, not a renamed file

**Issue:** "The images.0 is not a valid image file"
**Solution:** File may be corrupted or not a real image

**Issue:** ClamAV not working
**Solution:** Check if clamav-daemon is running: `systemctl status clamav-daemon`

**Issue:** Large files timing out
**Solution:** Increase PHP upload limits in php.ini:
```ini
upload_max_filesize = 10M
post_max_size = 10M
max_execution_time = 300
```

## Conclusion

The file upload security implementation provides comprehensive protection against malicious file uploads while maintaining a good user experience with clear error messages. The multi-layer validation approach ensures that only safe, valid image files are accepted by the system.
