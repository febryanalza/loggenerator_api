<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\AuditLog;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;
use Illuminate\Support\Facades\URL;

class ProfileController extends Controller
{
    /**
     * Get current user profile
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function show(Request $request)
    {
        /** @var User $user */
        $user = $request->user();
        
        if (!$user) {
            return response()->json([
                'success' => false,
                'message' => 'User not authenticated'
            ], 401);
        }
        
        return response()->json([
            'success' => true,
            'message' => 'Profile retrieved successfully',
            'data' => [
                'id' => $user->id,
                'name' => $user->name,
                'email' => $user->email,
                'phone_number' => $user->phone_number,
                'profile_picture' => $user->profile_picture,
                'avatar_url' => $user->avatar_url,
                'auth_provider' => $user->auth_provider ?? 'email',
                'status' => $user->status,
                'created_at' => $user->created_at,
                'updated_at' => $user->updated_at,
                'roles' => $user->getRoleNames(),
            ]
        ]);
    }

    /**
     * Update user profile (partial update supported)
     * Allows updating name, phone_number, and profile picture
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function update(Request $request)
    {
        /** @var User $user */
        $user = Auth::user();
        
        if (!$user) {
            return response()->json([
                'success' => false,
                'message' => 'User not authenticated'
            ], 401);
        }
        
        // Validation rules - all fields optional for partial update
        $validator = Validator::make($request->all(), [
            'name' => 'sometimes|string|max:100',
            'phone_number' => 'sometimes|nullable|string|max:20',
            'profile_picture' => 'sometimes|string|nullable', // Base64 image or existing URL
            'current_password' => 'required_with:password|string',
            'password' => 'sometimes|string|min:8|confirmed',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation Error',
                'errors' => $validator->errors()
            ], 422);
        }

        try {
            $changes = [];
            $oldData = [
                'name' => $user->name,
                'phone_number' => $user->phone_number,
                'profile_picture' => $user->profile_picture,
            ];

            // Update name if provided
            if ($request->has('name') && $request->name !== $user->name) {
                $user->name = $request->name;
                $changes[] = 'name';
            }

            // Update phone number if provided
            if ($request->has('phone_number') && $request->phone_number !== $user->phone_number) {
                $user->phone_number = $request->phone_number;
                $changes[] = 'phone_number';
            }

            // Handle profile picture upload
            if ($request->has('profile_picture')) {
                $newPicturePath = $this->handleProfilePicture($request->profile_picture, $user);
                if ($newPicturePath !== $user->profile_picture) {
                    // Delete old profile picture if exists and not from Google
                    if ($user->profile_picture && !$user->avatar_url) {
                        Storage::disk('avatar')->delete(basename($user->profile_picture));
                    }
                    
                    $user->profile_picture = $newPicturePath;
                    $changes[] = 'profile_picture';
                }
            }

            // Handle password change
            if ($request->has('password')) {
                // Verify current password
                if (!Hash::check($request->current_password, $user->password)) {
                    return response()->json([
                        'success' => false,
                        'message' => 'Current password is incorrect'
                    ], 422);
                }
                
                $user->password = Hash::make($request->password);
                $changes[] = 'password';
            }

            // Save changes if any
            if (!empty($changes)) {
                $user->save();

                // Create audit log
                $newData = [
                    'name' => $user->name,
                    'phone_number' => $user->phone_number,
                    'profile_picture' => $user->profile_picture,
                ];
                
                AuditLog::create([
                    'user_id' => $user->id,
                    'action' => 'UPDATE_PROFILE',
                    'description' => 'Updated profile: ' . implode(', ', $changes),
                    'old_values' => array_intersect_key($oldData, array_flip($changes)),
                    'new_values' => array_intersect_key($newData, array_flip($changes)),
                    'ip_address' => $request->ip(),
                    'user_agent' => $request->userAgent()
                ]);

                return response()->json([
                    'success' => true,
                    'message' => 'Profile updated successfully',
                    'updated_fields' => $changes,
                    'data' => [
                        'id' => $user->id,
                        'name' => $user->name,
                        'email' => $user->email,
                        'phone_number' => $user->phone_number,
                        'profile_picture' => $user->profile_picture,
                        'avatar_url' => $user->avatar_url,
                        'status' => $user->status,
                        'updated_at' => $user->updated_at,
                    ]
                ]);
            } else {
                return response()->json([
                    'success' => true,
                    'message' => 'No changes detected',
                    'data' => [
                        'id' => $user->id,
                        'name' => $user->name,
                        'email' => $user->email,
                        'phone_number' => $user->phone_number,
                        'profile_picture' => $user->profile_picture,
                        'avatar_url' => $user->avatar_url,
                        'status' => $user->status,
                    ]
                ]);
            }

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to update profile',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Handle profile picture upload (base64 or URL)
     *
     * @param string $profilePicture
     * @param User $user
     * @return string|null
     */
    private function handleProfilePicture($profilePicture, User $user)
    {
        // If null or empty, remove profile picture
        if (empty($profilePicture)) {
            return null;
        }

        // If it's already a URL (like existing profile picture), return as is
        if (filter_var($profilePicture, FILTER_VALIDATE_URL)) {
            return $profilePicture;
        }

        // If it's base64 image data
        if ($this->isBase64Image($profilePicture)) {
            return $this->saveBase64Image($profilePicture, $user->id);
        }

        // If it's a relative path, convert to full URL
        if (is_string($profilePicture) && !str_starts_with($profilePicture, 'http')) {
            return url('storage/avatars/' . $profilePicture);
        }

        return $profilePicture;
    }

    /**
     * Check if string is valid base64 image
     *
     * @param string $string
     * @return bool
     */
    private function isBase64Image($string)
    {
        if (!is_string($string)) {
            return false;
        }

        // Check if it has data:image prefix
        if (str_starts_with($string, 'data:image/')) {
            $string = substr($string, strpos($string, ',') + 1);
        }

        // Validate base64
        $decoded = base64_decode($string, true);
        if ($decoded === false) {
            return false;
        }

        // Check if it's a valid image
        $imageInfo = getimagesizefromstring($decoded);
        return $imageInfo !== false;
    }

    /**
     * Save base64 image to avatar storage
     *
     * @param string $base64Image
     * @param string $userId
     * @return string
     */
    private function saveBase64Image($base64Image, $userId)
    {
        // Extract image data and type
        if (str_starts_with($base64Image, 'data:image/')) {
            $imageData = explode(',', $base64Image);
            $imageType = explode(';', explode('/', $imageData[0])[1])[0];
            $imageContent = base64_decode($imageData[1]);
        } else {
            $imageContent = base64_decode($base64Image);
            $imageType = 'jpg'; // Default to jpg
        }

        // Generate unique filename
        $filename = 'avatar_' . $userId . '_' . time() . '.' . $imageType;
        
        // Ensure avatar directory exists
        Storage::disk('avatar')->makeDirectory('');
        
        // Save image
        Storage::disk('avatar')->put($filename, $imageContent);
        
        // Return full URL
        return url('storage/avatars/' . $filename);
    }

    /**
     * Delete user's profile picture
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function deleteProfilePicture(Request $request)
    {
        /** @var User $user */
        $user = Auth::user();
        
        if (!$user) {
            return response()->json([
                'success' => false,
                'message' => 'User not authenticated'
            ], 401);
        }
        
        try {
            // Delete file if it's stored locally (not from Google)
            if ($user->profile_picture && !$user->avatar_url) {
                Storage::disk('avatar')->delete(basename($user->profile_picture));
            }
            
            // Clear profile picture field
            $user->profile_picture = null;
            $user->save();
            
            // Create audit log
            AuditLog::create([
                'user_id' => $user->id,
                'action' => 'DELETE_PROFILE_PICTURE',
                'description' => 'Deleted profile picture',
                'ip_address' => $request->ip(),
                'user_agent' => $request->userAgent()
            ]);
            
            return response()->json([
                'success' => true,
                'message' => 'Profile picture deleted successfully'
            ]);
            
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to delete profile picture',
                'error' => $e->getMessage()
            ], 500);
        }
    }
}