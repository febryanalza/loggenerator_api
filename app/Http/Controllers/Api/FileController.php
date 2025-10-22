<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Http\Response;

class FileController extends Controller
{
    /**
     * Get logbook image by filename.
     *
     * @param string $filename
     * @return \Illuminate\Http\Response
     */
    public function getLogbookImage($filename)
    {
        try {
            $path = 'logbook_images/' . $filename;
            
            // Check if file exists
            if (!Storage::disk('public')->exists($path)) {
                return response()->json([
                    'success' => false,
                    'message' => 'Image not found'
                ], 404);
            }
            
            // Get file content
            $file = Storage::disk('public')->get($path);
            
            // Get mime type from file extension
            $extension = pathinfo($filename, PATHINFO_EXTENSION);
            $mimeType = match(strtolower($extension)) {
                'jpg', 'jpeg' => 'image/jpeg',
                'png' => 'image/png',
                'gif' => 'image/gif',
                'webp' => 'image/webp',
                default => 'image/jpeg'
            };
            
            return response($file)
                ->header('Content-Type', $mimeType)
                ->header('Cache-Control', 'public, max-age=31536000'); // Cache for 1 year
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error retrieving image: ' . $e->getMessage()
            ], 500);
        }
    }
    
    /**
     * Get avatar image by filename.
     *
     * @param string $filename
     * @return \Illuminate\Http\Response
     */
    public function getAvatarImage($filename)
    {
        try {
            $path = $filename;
            
            // Check if file exists in avatar disk
            if (!Storage::disk('avatar')->exists($path)) {
                return response()->json([
                    'success' => false,
                    'message' => 'Avatar image not found'
                ], 404);
            }
            
            // Get file content
            $file = Storage::disk('avatar')->get($path);
            
            // Get mime type from file extension
            $extension = pathinfo($filename, PATHINFO_EXTENSION);
            $mimeType = match(strtolower($extension)) {
                'jpg', 'jpeg' => 'image/jpeg',
                'png' => 'image/png',
                'gif' => 'image/gif',
                'webp' => 'image/webp',
                default => 'image/jpeg'
            };
            
            return response($file)
                ->header('Content-Type', $mimeType)
                ->header('Cache-Control', 'public, max-age=31536000'); // Cache for 1 year
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error retrieving avatar image: ' . $e->getMessage()
            ], 500);
        }
    }
    
    /**
     * Upload a single image file.
     *
     * @param \Illuminate\Http\Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function uploadImage(Request $request)
    {
        try {
            $request->validate([
                'image' => 'required|image|mimes:jpeg,png,jpg,gif|max:2048' // Max 2MB
            ]);
            
            $image = $request->file('image');
            $filename = 'logbook_' . time() . '_' . uniqid() . '.' . $image->getClientOriginalExtension();
            
            // Store the image
            $path = $image->storeAs('logbook_images', $filename, 'public');
            
            // Generate URL for accessing the image
            $url = url('/api/images/logbook/' . $filename);
            
            return response()->json([
                'success' => true,
                'message' => 'Image uploaded successfully',
                'data' => [
                    'filename' => $filename,
                    'path' => $path,
                    'url' => $url
                ]
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error uploading image: ' . $e->getMessage()
            ], 500);
        }
    }
}
