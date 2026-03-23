<?php

namespace App\Rules;

use Closure;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Http\UploadedFile;

class SecureImageUpload implements ValidationRule
{
    private int $maxSizeKb;
    private array $allowedMimeTypes = [
        'image/jpeg',
        'image/jpg',
        'image/png',
        'image/webp',
    ];
    private array $allowedExtensions = ['jpeg', 'jpg', 'png', 'webp'];

    public function __construct(int $maxSizeKb = 5120)
    {
        $this->maxSizeKb = $maxSizeKb;
    }

    /**
     * Run the validation rule.
     */
    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        if (!$value instanceof UploadedFile) {
            $fail("The {$attribute} must be a valid file.");
            return;
        }

        // Check if file is actually an image
        if (!$value->isValid()) {
            $fail("The {$attribute} upload failed. Please try again.");
            return;
        }

        // Validate file extension
        $extension = strtolower($value->getClientOriginalExtension());
        if (!in_array($extension, $this->allowedExtensions)) {
            $fail("The {$attribute} must be a file of type: " . implode(', ', $this->allowedExtensions) . ".");
            return;
        }

        // Validate MIME type from file content (not just extension)
        $mimeType = $value->getMimeType();
        if (!in_array($mimeType, $this->allowedMimeTypes)) {
            $fail("The {$attribute} file type is not allowed. Detected type: {$mimeType}.");
            return;
        }

        // Validate file size
        $fileSizeKb = $value->getSize() / 1024;
        if ($fileSizeKb > $this->maxSizeKb) {
            $maxSizeMb = round($this->maxSizeKb / 1024, 1);
            $fail("The {$attribute} must not be larger than {$maxSizeMb}MB.");
            return;
        }

        // Additional security: Check for double extensions
        $filename = $value->getClientOriginalName();
        if (preg_match('/\.(php|phtml|php3|php4|php5|phar|exe|sh|bat|cmd)(\.|$)/i', $filename)) {
            $fail("The {$attribute} filename contains suspicious patterns.");
            return;
        }

        // Verify image integrity by attempting to read image dimensions
        try {
            $imageInfo = @getimagesize($value->getRealPath());
            if ($imageInfo === false) {
                $fail("The {$attribute} is not a valid image file.");
                return;
            }

            // Verify MIME type matches image type
            if (!in_array($imageInfo['mime'], $this->allowedMimeTypes)) {
                $fail("The {$attribute} image format is not supported.");
                return;
            }
        } catch (\Exception $e) {
            $fail("The {$attribute} could not be validated as a safe image.");
            return;
        }
    }
}
