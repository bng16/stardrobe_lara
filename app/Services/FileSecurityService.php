<?php

namespace App\Services;

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Log;

class FileSecurityService
{
    /**
     * Scan uploaded file for malicious content.
     * 
     * This method performs basic security checks. For production environments,
     * consider integrating ClamAV or similar antivirus scanning.
     */
    public function scanFile(UploadedFile $file): array
    {
        $issues = [];

        // Check for executable content in image files
        $content = file_get_contents($file->getRealPath());
        
        // Check for PHP code injection
        if ($this->containsPhpCode($content)) {
            $issues[] = 'File contains suspicious PHP code';
        }

        // Check for script tags (XSS attempts)
        if ($this->containsScriptTags($content)) {
            $issues[] = 'File contains suspicious script tags';
        }

        // Check for null bytes (path traversal attempts)
        if (strpos($content, "\0") !== false) {
            $issues[] = 'File contains null bytes';
        }

        // Check file header matches declared type
        if (!$this->validateFileHeader($file)) {
            $issues[] = 'File header does not match declared type';
        }

        // Log scan results
        if (!empty($issues)) {
            Log::warning('File security scan detected issues', [
                'filename' => $file->getClientOriginalName(),
                'mime_type' => $file->getMimeType(),
                'issues' => $issues,
            ]);
        }

        return [
            'safe' => empty($issues),
            'issues' => $issues,
        ];
    }

    /**
     * Check if content contains PHP code patterns.
     */
    private function containsPhpCode(string $content): bool
    {
        $patterns = [
            '/<\?php/i',
            '/<\?=/i',
            '/<\?/i',
            '/<%/i',
            '/eval\s*\(/i',
            '/base64_decode\s*\(/i',
            '/exec\s*\(/i',
            '/system\s*\(/i',
            '/passthru\s*\(/i',
            '/shell_exec\s*\(/i',
        ];

        foreach ($patterns as $pattern) {
            if (preg_match($pattern, $content)) {
                return true;
            }
        }

        return false;
    }

    /**
     * Check if content contains script tags.
     */
    private function containsScriptTags(string $content): bool
    {
        return preg_match('/<script[^>]*>.*?<\/script>/is', $content) === 1;
    }

    /**
     * Validate file header matches the declared MIME type.
     */
    private function validateFileHeader(UploadedFile $file): bool
    {
        $mimeType = $file->getMimeType();
        $handle = fopen($file->getRealPath(), 'rb');
        $header = fread($handle, 12);
        fclose($handle);

        // Check magic bytes for common image formats
        $signatures = [
            'image/jpeg' => [
                "\xFF\xD8\xFF", // JPEG
            ],
            'image/png' => [
                "\x89\x50\x4E\x47\x0D\x0A\x1A\x0A", // PNG
            ],
            'image/webp' => [
                "RIFF", // WebP (first 4 bytes)
            ],
        ];

        if (!isset($signatures[$mimeType])) {
            return false;
        }

        foreach ($signatures[$mimeType] as $signature) {
            if (strpos($header, $signature) === 0) {
                return true;
            }
        }

        // Special case for WebP - check WEBP signature at offset 8
        if ($mimeType === 'image/webp' && strlen($header) >= 12) {
            if (substr($header, 8, 4) === 'WEBP') {
                return true;
            }
        }

        return false;
    }

    /**
     * Optional: Integrate with ClamAV for virus scanning.
     * 
     * This requires ClamAV to be installed and configured on the server.
     * Install: apt-get install clamav clamav-daemon
     * PHP extension: pecl install clamav
     */
    public function scanWithClamAV(UploadedFile $file): array
    {
        // Check if ClamAV is available
        if (!function_exists('cl_scanfile')) {
            return [
                'safe' => true,
                'message' => 'ClamAV not available, skipping virus scan',
            ];
        }

        try {
            $result = cl_scanfile($file->getRealPath());
            
            if ($result === CL_CLEAN) {
                return [
                    'safe' => true,
                    'message' => 'File is clean',
                ];
            }

            Log::warning('ClamAV detected malware', [
                'filename' => $file->getClientOriginalName(),
                'result' => $result,
            ]);

            return [
                'safe' => false,
                'message' => 'File contains malicious content',
            ];
        } catch (\Exception $e) {
            Log::error('ClamAV scan failed', [
                'filename' => $file->getClientOriginalName(),
                'error' => $e->getMessage(),
            ]);

            // Fail safe: reject file if scan fails
            return [
                'safe' => false,
                'message' => 'Unable to verify file safety',
            ];
        }
    }
}
