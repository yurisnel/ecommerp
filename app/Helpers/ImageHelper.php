<?php

namespace App\Helpers;

use Illuminate\Support\Facades\Storage;
use Exception;

class ImageHelper
{
    /**
     * Generate a simple category image with title
     */
    public static function generateCategoryImage(string $categoryName, string $hexColor): string
    {
        try {
            // Create image
            $image = imagecreatetruecolor(400, 400);
            
            // Convert hex to RGB
            $rgb = self::hexToRgb($hexColor);
            $bgColor = imagecolorallocate($image, $rgb['r'], $rgb['g'], $rgb['b']);
            
            // Fill background
            imagefill($image, 0, 0, $bgColor);
            
            // Add text
            $textColor = imagecolorallocate($image, 255, 255, 255);
            $fontSize = 5;
            
            // Center text
            $text = strtoupper($categoryName);
            $textBox = imagettfbbox($fontSize, 0, __DIR__ . '/../../resources/fonts/arial.ttf', $text);
            $textWidth = $textBox[2] - $textBox[0];
            $textHeight = $textBox[1] - $textBox[7];
            
            $x = (400 - $textWidth) / 2;
            $y = (400 - $textHeight) / 2 + $textHeight;
            
            imagestring($image, $fontSize, (int)$x, (int)$y, $text, $textColor);
            
            // Save image
            $filename = 'categories/' . str_replace(' ', '-', strtolower($categoryName)) . '.jpg';
            $path = storage_path('app/public/' . $filename);
            
            // Ensure directory exists
            if (!is_dir(dirname($path))) {
                mkdir(dirname($path), 0755, true);
            }
            
            imagejpeg($image, $path, 85);
            imagedestroy($image);
            
            return $filename;
        } catch (Exception $e) {
            // Fallback to placeholder URL
            return 'categories/placeholder-' . str_replace(' ', '-', strtolower($categoryName)) . '.jpg';
        }
    }

    /**
     * Generate a placeholder image URL
     */
    public static function getPlaceholderUrl(string $categoryName, string $hexColor): string
    {
        // Using placeholder service with color and text
        $text = urlencode(strtoupper(substr($categoryName, 0, 20)));
        $bgColor = str_replace('#', '', $hexColor);
        return "https://via.placeholder.com/400x400/{$bgColor}/FFFFFF?text={$text}";
    }

    /**
     * Convert hex color to RGB
     */
    private static function hexToRgb(string $hex): array
    {
        $hex = str_replace('#', '', $hex);
        
        if (strlen($hex) === 3) {
            $hex = $hex[0] . $hex[0] . $hex[1] . $hex[1] . $hex[2] . $hex[2];
        }
        
        return [
            'r' => hexdec(substr($hex, 0, 2)),
            'g' => hexdec(substr($hex, 2, 2)),
            'b' => hexdec(substr($hex, 4, 2)),
        ];
    }
}
