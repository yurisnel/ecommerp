<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class UploadController extends Controller
{
    /**
     * Upload an image and return the URL.
     */
    public function upload(Request $request)
    {
        $request->validate([
            'file' => 'required|image|mimes:jpeg,png,jpg,gif,webp|max:2048',
            'folder' => 'nullable|string',
            'compress' => 'nullable|in:0,1,true,false,on,off',
            'quality' => 'nullable|integer|min:50|max:100',
            'add_watermark' => 'nullable|in:0,1,true,false,on,off'
        ]);

        if ($request->hasFile('file')) {
            $file = $request->file('file');
            $folder = $request->get('folder', 'uploads');
            $compress = filter_var($request->get('compress', true), FILTER_VALIDATE_BOOLEAN, FILTER_NULL_ON_FAILURE) ?? true;
            $quality = $request->integer('quality', 85);
            $addWatermark = filter_var($request->get('add_watermark', false), FILTER_VALIDATE_BOOLEAN, FILTER_NULL_ON_FAILURE) ?? false;

            $filename = Str::random(20) . '.' . $file->getClientOriginalExtension();

            try {
                // Load the image
                $image = imagecreatefromstring(file_get_contents($file));
                if (!$image) {
                    throw new \Exception('Invalid image file.');
                }

                // Get original dimensions
                $width = imagesx($image);
                $height = imagesy($image);

                // Compress image
                if ($compress) {
                    $newWidth = min(1000, $width);
                    $newHeight = (int) (($newWidth / $width) * $height);

                    $resizedImage = imagecreatetruecolor($newWidth, $newHeight);
                    imagecopyresampled($resizedImage, $image, 0, 0, 0, 0, $newWidth, $newHeight, $width, $height);
                    $image = $resizedImage;
                }

                // Add watermark if requested
                if ($addWatermark) {
                    $this->addWatermark($image);
                }

                // Save the image
                $path = $folder . '/' . $filename;
                ob_start();
                imagejpeg($image, null, $quality);
                $imageData = ob_get_clean();

                Storage::disk('public')->put($path, $imageData);

                return response()->json([
                    'success' => true,
                    'message' => __('api.file_uploaded_successfully'),
                    'url' => Storage::url($path),
                    'path' => $path,
                    'size' => Storage::disk('public')->size($path)
                ]);
            } catch (\Exception $e) {
                return response()->json([
                    'success' => false,
                    'message' => __('api.error_processing_image') . $e->getMessage()
                ], 400);
            }
        }

        return response()->json([
            'success' => false,
            'message' => __('api.no_file_uploaded')
        ], 400);
    }

    /**
     * Add watermark to image
     */
    private function addWatermark(&$image)
    {
        try {
            $watermarkPath = public_path('images/watermark.png');

            if (file_exists($watermarkPath)) {
                $watermark = imagecreatefrompng($watermarkPath);
                if (!$watermark) {
                    throw new \Exception('Invalid watermark file.');
                }

                $watermarkWidth = imagesx($watermark);
                $watermarkHeight = imagesy($watermark);

                $destX = imagesx($image) - $watermarkWidth - 10;
                $destY = imagesy($image) - $watermarkHeight - 10;

                imagecopy($image, $watermark, $destX, $destY, 0, 0, $watermarkWidth, $watermarkHeight);
            }
        } catch (\Exception $e) {
            \Log::warning('Failed to add watermark: ' . $e->getMessage());
        }
    }
}
