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
            'folder' => 'nullable|string'
        ]);

        if ($request->hasFile('file')) {
            $file = $request->file('file');
            $folder = $request->get('folder', 'uploads');

            // Generate a unique filename
            $filename = Str::random(20) . '.' . $file->getClientOriginalExtension();

            // Store the file
            $path = $file->storeAs($folder, $filename, 'public');

            return response()->json([
                'success' => true,
                'message' => 'File uploaded successfully',
                'url' => Storage::url($path),
                'path' => $path
            ]);
        }

        return response()->json([
            'success' => false,
            'message' => 'No file uploaded'
        ], 400);
    }
}
