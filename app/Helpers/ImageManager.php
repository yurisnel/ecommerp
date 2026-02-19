<?php

namespace App\Helpers;

use Illuminate\Support\Str;
use Illuminate\Support\Facades\Log;
use Intervention\Image\Laravel\Facades\Image;

class ImageManager
{
    const MAX_FILE_SIZE = 2 * 1024 * 1024; // 2MB
    const ALLOWED_MIME_TYPES = ['image/jpeg', 'image/png', 'image/webp', 'image/gif'];
    const ALLOWED_EXTENSIONS = ['jpg', 'jpeg', 'png', 'webp', 'gif'];

    /**
     * Validate image file
     */
    public static function validateImage($file): array
    {
        $errors = [];

        if (!$file) {
            $errors[] = 'No file provided';
            return ['valid' => false, 'errors' => $errors];
        }

        // Check file size
        if ($file->getSize() > self::MAX_FILE_SIZE) {
            $errors[] = 'File size exceeds 2MB limit';
        }

        // Check MIME type
        if (!in_array($file->getMimeType(), self::ALLOWED_MIME_TYPES)) {
            $errors[] = 'Invalid file type. Allowed: ' . implode(', ', self::ALLOWED_EXTENSIONS);
        }

        // Check extension
        $extension = strtolower($file->getClientOriginalExtension());
        if (!in_array($extension, self::ALLOWED_EXTENSIONS)) {
            $errors[] = 'Invalid file extension';
        }

        return [
            'valid' => count($errors) === 0,
            'errors' => $errors
        ];
    }

    /**
     * Generate unique filename
     */
    public static function generateFilename(string $originalName): string
    {
        $extension = strtolower(pathinfo($originalName, PATHINFO_EXTENSION));
        return Str::random(32) . '.' . $extension;
    }

    /**
     * Get storage path for images
     */
    public static function getStoragePath(string $folder = 'uploads'): string
    {
        return $folder;
    }

    /**
     * Get image URL from storage path
     */
    public static function getImageUrl(string $path): string
    {
        if (str_starts_with($path, 'http')) {
            return $path; // Already a full URL
        }

        return url('storage/' . $path);
    }

    /**
     * Validate image dimensions
     */
    public static function validateDimensions($filePath, int $minWidth = 100, int $minHeight = 100): array
    {
        try {
            $size = getimagesize($filePath);
            if ($size === false) {
                return ['valid' => false, 'error' => 'Could not determine image dimensions'];
            }

            $width = $size[0];
            $height = $size[1];

            if ($width < $minWidth || $height < $minHeight) {
                return [
                    'valid' => false,
                    'error' => "Image must be at least {$minWidth}x{$minHeight} pixels"
                ];
            }

            return ['valid' => true, 'width' => $width, 'height' => $height];
        } catch (\Exception $e) {
            return ['valid' => false, 'error' => 'Error validating image dimensions'];
        }
    }

    /**
     * Get file info
     */
    public static function getFileInfo($file): array
    {
        return [
            'name' => $file->getClientOriginalName(),
            'size' => $file->getSize(),
            'mime_type' => $file->getMimeType(),
            'extension' => strtolower($file->getClientOriginalExtension()),
        ];
    }

    /**
     * Compress image
     */
    public static function compressImage($imagePath, int $quality = 85, int $maxWidth = 2000, int $maxHeight = 2000)
    {
        try {
            $image = Image::make($imagePath);

            $image->resize($maxWidth, $maxHeight, function ($constraint) {
                $constraint->aspectRatio();
                $constraint->upsize();
            })->encode($image->extension(), $quality);

            return $image;
        } catch (\Exception $e) {
            Log::warning('Image compression failed: ' . $e->getMessage());
            return null;
        }
    }

    /**
     * Add text watermark to image
     */
    public static function addTextWatermark($image, string $text, string $position = 'bottom-right', float $opacity = 0.5)
    {
        try {
            $image->text($text, 10, $image->height() - 20, function ($font) {
                $font->filename(resource_path('fonts/arial.ttf'));
                $font->size(16);
                $font->color('#ffffff');
                $font->align('right');
                $font->valign('bottom');
            });

            return $image;
        } catch (\Exception $e) {
            Log::warning('Text watermark addition failed: ' . $e->getMessage());
            return $image;
        }
    }

    /**
     * Add image watermark
     */
    public static function addImageWatermark($image, string $watermarkPath, string $position = 'bottom-right', int $padding = 10, float $opacity = 0.7)
    {
        try {
            if (!file_exists($watermarkPath)) {
                return $image;
            }

            $watermark = Image::make($watermarkPath);

            // Resize watermark to be 1/6 of the main image width
            $watermark->scale($image->width() / 6);

            $image->insert($watermark, $position, $padding, $padding);

            return $image;
        } catch (\Exception $e) {
            Log::warning('Image watermark addition failed: ' . $e->getMessage());
            return $image;
        }
    }

    /**
     * Generate different image sizes (thumbnails)
     */
    public static function generateThumbnails($imagePath, array $sizes = []): array
    {
        $defaults = [
            'thumb' => ['width' => 150, 'height' => 150],
            'medium' => ['width' => 400, 'height' => 400],
            'large' => ['width' => 800, 'height' => 800],
        ];

        $sizes = array_merge($defaults, $sizes);
        $generated = [];

        try {
            $image = Image::make($imagePath);
            $basePath = pathinfo($imagePath, PATHINFO_DIRNAME);
            $filename = pathinfo($imagePath, PATHINFO_FILENAME);
            $extension = pathinfo($imagePath, PATHINFO_EXTENSION);

            foreach ($sizes as $size => $dimensions) {
                $thumbPath = $basePath . '/' . $filename . '_' . $size . '.' . $extension;

                $thumbnail = clone $image;
                $thumbnail->cover($dimensions['width'], $dimensions['height'])->save($thumbPath);

                $generated[$size] = $thumbPath;
            }

            return $generated;
        } catch (\Exception $e) {
            Log::warning('Thumbnail generation failed: ' . $e->getMessage());
            return [];
        }
    }

    /**
     * Get image dimensions
     */
    public static function getImageDimensions($imagePath): array
    {
        try {
            $size = getimagesize($imagePath);
            if ($size === false) {
                return ['width' => 0, 'height' => 0];
            }
            return ['width' => $size[0], 'height' => $size[1]];
        } catch (\Exception $e) {
            return ['width' => 0, 'height' => 0];
        }
    }
}
