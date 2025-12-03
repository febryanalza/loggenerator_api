<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\LogbookData;
use App\Models\LogbookTemplate;
use App\Models\LogbookDataVerification;
use App\Models\UserLogbookAccess;
use App\Models\LogbookRole;
use App\Models\AuditLog;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;

class LogbookVerificationController extends Controller
{
    /**
     * Initialize controller with middleware.
     */
    public function __construct()
    {
        // Middleware is applied in routes file
    }

    /**
     * Get all logbook data entries for verification (Supervisor only)
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function getDataForVerification(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'template_id' => 'required|uuid|exists:logbook_template,id',
            'verified_status' => 'sometimes|in:verified,unverified,all',
            'per_page' => 'sometimes|integer|min:5|max:100'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation Error',
                'errors' => $validator->errors()
            ], 422);
        }

        try {
            $currentUser = $request->user();
            $templateId = $request->template_id;
            $verifiedStatus = $request->get('verified_status', 'all');
            $perPage = $request->get('per_page', 15);

            // Check if user is a supervisor for this template
            if (!$this->isSupervisorOfTemplate($currentUser->id, $templateId)) {
                return response()->json([
                    'success' => false,
                    'message' => 'Unauthorized. Only supervisors can access verification data.'
                ], 403);
            }

            // Build query with verifications relationship
            $query = LogbookData::with([
                    'writer:id,name,email', 
                    'template:id,name',
                    'verifications.verifier:id,name,email'
                ])
                ->where('template_id', $templateId);

            // Filter by verification status
            if ($verifiedStatus === 'verified') {
                $query->verified();
            } elseif ($verifiedStatus === 'unverified') {
                $query->unverified();
            }

            // Order by creation date (newest first)
            $query->orderBy('created_at', 'desc');

            $data = $query->paginate($perPage);

            // Transform data to include verification summary
            $data->getCollection()->transform(function ($item) use ($currentUser) {
                $item->verification_summary = [
                    'total_verifications' => $item->verifications->count(),
                    'verified_count' => $item->verifications->where('is_verified', true)->count(),
                    'rejected_count' => $item->verifications->where('is_verified', false)->count(),
                    'is_verified_by_me' => $item->isVerifiedBy($currentUser->id),
                    'my_verification' => $item->getVerificationFrom($currentUser->id),
                ];
                return $item;
            });

            // Log activity
            AuditLog::create([
                'user_id' => $currentUser->id,
                'action' => 'view_verification_data',
                'model_type' => 'LogbookTemplate',
                'model_id' => $templateId,
                'ip_address' => $request->ip(),
                'user_agent' => $request->userAgent(),
                'details' => json_encode([
                    'template_id' => $templateId,
                    'verified_status' => $verifiedStatus,
                    'total_entries' => $data->total()
                ])
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Verification data retrieved successfully',
                'data' => $data
            ]);

        } catch (\Exception $e) {
            Log::error('Error getting verification data: ' . $e->getMessage(), [
                'user_id' => $request->user()->id ?? 'unknown',
                'template_id' => $request->template_id ?? 'unknown',
                'trace' => $e->getTraceAsString()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Failed to retrieve verification data'
            ], 500);
        }
    }

    /**
     * Verify a specific logbook data entry (Supervisor only)
     * Now supports multiple verifiers per data entry
     *
     * @param Request $request
     * @param string $dataId
     * @return \Illuminate\Http\JsonResponse
     */
    public function verifyData(Request $request, string $dataId)
    {
        $validator = Validator::make($request->all(), [
            'verification_notes' => 'sometimes|string|max:1000'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation Error',
                'errors' => $validator->errors()
            ], 422);
        }

        try {
            DB::beginTransaction();

            $currentUser = $request->user();
            
            // Get the logbook data entry
            $logbookData = LogbookData::with(['template', 'writer'])->findOrFail($dataId);

            // Check if user is a supervisor for this template
            if (!$this->isSupervisorOfTemplate($currentUser->id, $logbookData->template_id)) {
                return response()->json([
                    'success' => false,
                    'message' => 'Unauthorized. Only supervisors can verify data entries.'
                ], 403);
            }

            // Check if user has already verified this entry (approval)
            $existingVerification = $logbookData->getVerificationFrom($currentUser->id);
            if ($existingVerification && $existingVerification->is_verified) {
                return response()->json([
                    'success' => false,
                    'message' => 'You have already verified this data entry.'
                ], 400);
            }

            // Create or update verification
            $verification = $logbookData->verify(
                $currentUser->id,
                $request->get('verification_notes')
            );

            // Log activity
            AuditLog::create([
                'user_id' => $currentUser->id,
                'action' => 'verify_data',
                'model_type' => 'LogbookData',
                'model_id' => $dataId,
                'ip_address' => $request->ip(),
                'user_agent' => $request->userAgent(),
                'details' => json_encode([
                    'template_id' => $logbookData->template_id,
                    'writer_id' => $logbookData->writer_id,
                    'verification_id' => $verification->id,
                    'verification_notes' => $request->get('verification_notes'),
                    'verified_at' => now()
                ])
            ]);

            DB::commit();

            // Get all verifications for this data
            $allVerifications = $logbookData->verifications()->with('verifier:id,name,email')->get();

            return response()->json([
                'success' => true,
                'message' => 'Data entry verified successfully',
                'data' => [
                    'id' => $logbookData->id,
                    'verification' => $verification,
                    'all_verifications' => $allVerifications,
                    'total_verifiers' => $allVerifications->count(),
                    'approved_count' => $allVerifications->where('is_verified', true)->count(),
                    'rejected_count' => $allVerifications->where('is_verified', false)->count(),
                ]
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            
            Log::error('Error verifying data: ' . $e->getMessage(), [
                'user_id' => $request->user()->id ?? 'unknown',
                'data_id' => $dataId,
                'trace' => $e->getTraceAsString()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Failed to verify data entry'
            ], 500);
        }
    }

    /**
     * Reject a specific logbook data entry (Supervisor only)
     * Changed from unverify to reject - records rejection instead of removing
     *
     * @param Request $request
     * @param string $dataId
     * @return \Illuminate\Http\JsonResponse
     */
    public function rejectData(Request $request, string $dataId)
    {
        $validator = Validator::make($request->all(), [
            'rejection_notes' => 'required|string|max:1000'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation Error',
                'errors' => $validator->errors()
            ], 422);
        }

        try {
            DB::beginTransaction();

            $currentUser = $request->user();
            
            // Get the logbook data entry
            $logbookData = LogbookData::with(['template', 'writer'])->findOrFail($dataId);

            // Check if user is a supervisor for this template
            if (!$this->isSupervisorOfTemplate($currentUser->id, $logbookData->template_id)) {
                return response()->json([
                    'success' => false,
                    'message' => 'Unauthorized. Only supervisors can reject data entries.'
                ], 403);
            }

            // Check if user has already rejected this entry
            $existingVerification = $logbookData->getVerificationFrom($currentUser->id);
            if ($existingVerification && !$existingVerification->is_verified) {
                return response()->json([
                    'success' => false,
                    'message' => 'You have already rejected this data entry.'
                ], 400);
            }

            // Create or update verification as rejected
            $verification = $logbookData->reject(
                $currentUser->id,
                $request->get('rejection_notes')
            );

            // Log activity
            AuditLog::create([
                'user_id' => $currentUser->id,
                'action' => 'reject_data',
                'model_type' => 'LogbookData',
                'model_id' => $dataId,
                'ip_address' => $request->ip(),
                'user_agent' => $request->userAgent(),
                'details' => json_encode([
                    'template_id' => $logbookData->template_id,
                    'writer_id' => $logbookData->writer_id,
                    'verification_id' => $verification->id,
                    'rejection_notes' => $request->get('rejection_notes'),
                    'rejected_at' => now()
                ])
            ]);

            DB::commit();

            // Get all verifications for this data
            $allVerifications = $logbookData->verifications()->with('verifier:id,name,email')->get();

            return response()->json([
                'success' => true,
                'message' => 'Data entry rejected successfully',
                'data' => [
                    'id' => $logbookData->id,
                    'verification' => $verification,
                    'all_verifications' => $allVerifications,
                    'total_verifiers' => $allVerifications->count(),
                    'approved_count' => $allVerifications->where('is_verified', true)->count(),
                    'rejected_count' => $allVerifications->where('is_verified', false)->count(),
                ]
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            
            Log::error('Error rejecting data: ' . $e->getMessage(), [
                'user_id' => $request->user()->id ?? 'unknown',
                'data_id' => $dataId,
                'trace' => $e->getTraceAsString()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Failed to reject data entry'
            ], 500);
        }
    }

    /**
     * Remove verification/rejection from a specific logbook data entry (Supervisor only)
     *
     * @param Request $request
     * @param string $dataId
     * @return \Illuminate\Http\JsonResponse
     */
    public function removeVerification(Request $request, string $dataId)
    {
        try {
            DB::beginTransaction();

            $currentUser = $request->user();
            
            // Get the logbook data entry
            $logbookData = LogbookData::with(['template', 'writer'])->findOrFail($dataId);

            // Check if user is a supervisor for this template
            if (!$this->isSupervisorOfTemplate($currentUser->id, $logbookData->template_id)) {
                return response()->json([
                    'success' => false,
                    'message' => 'Unauthorized. Only supervisors can remove their verification.'
                ], 403);
            }

            // Check if user has a verification for this entry
            if (!$logbookData->hasVerificationFrom($currentUser->id)) {
                return response()->json([
                    'success' => false,
                    'message' => 'You have not verified or rejected this data entry.'
                ], 400);
            }

            // Remove the verification
            $logbookData->removeVerification($currentUser->id);

            // Log activity
            AuditLog::create([
                'user_id' => $currentUser->id,
                'action' => 'remove_verification',
                'model_type' => 'LogbookData',
                'model_id' => $dataId,
                'ip_address' => $request->ip(),
                'user_agent' => $request->userAgent(),
                'details' => json_encode([
                    'template_id' => $logbookData->template_id,
                    'writer_id' => $logbookData->writer_id,
                    'removed_at' => now()
                ])
            ]);

            DB::commit();

            // Get all remaining verifications for this data
            $allVerifications = $logbookData->verifications()->with('verifier:id,name,email')->get();

            return response()->json([
                'success' => true,
                'message' => 'Verification removed successfully',
                'data' => [
                    'id' => $logbookData->id,
                    'all_verifications' => $allVerifications,
                    'total_verifiers' => $allVerifications->count(),
                    'approved_count' => $allVerifications->where('is_verified', true)->count(),
                    'rejected_count' => $allVerifications->where('is_verified', false)->count(),
                ]
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            
            Log::error('Error removing verification: ' . $e->getMessage(), [
                'user_id' => $request->user()->id ?? 'unknown',
                'data_id' => $dataId,
                'trace' => $e->getTraceAsString()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Failed to remove verification'
            ], 500);
        }
    }

    /**
     * Get all verifications for a specific data entry (Supervisor only)
     *
     * @param Request $request
     * @param string $dataId
     * @return \Illuminate\Http\JsonResponse
     */
    public function getVerifications(Request $request, string $dataId)
    {
        try {
            $currentUser = $request->user();
            
            // Get the logbook data entry
            $logbookData = LogbookData::with(['template', 'writer'])->findOrFail($dataId);

            // Check if user is a supervisor for this template
            if (!$this->isSupervisorOfTemplate($currentUser->id, $logbookData->template_id)) {
                return response()->json([
                    'success' => false,
                    'message' => 'Unauthorized. Only supervisors can view verifications.'
                ], 403);
            }

            // Get all verifications with verifier details
            $verifications = $logbookData->verifications()
                ->with('verifier:id,name,email')
                ->orderBy('verified_at', 'desc')
                ->get();

            return response()->json([
                'success' => true,
                'message' => 'Verifications retrieved successfully',
                'data' => [
                    'logbook_data_id' => $logbookData->id,
                    'verifications' => $verifications,
                    'summary' => [
                        'total_verifiers' => $verifications->count(),
                        'approved_count' => $verifications->where('is_verified', true)->count(),
                        'rejected_count' => $verifications->where('is_verified', false)->count(),
                        'my_verification' => $logbookData->getVerificationFrom($currentUser->id),
                    ]
                ]
            ]);

        } catch (\Exception $e) {
            Log::error('Error getting verifications: ' . $e->getMessage(), [
                'user_id' => $request->user()->id ?? 'unknown',
                'data_id' => $dataId,
                'trace' => $e->getTraceAsString()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Failed to retrieve verifications'
            ], 500);
        }
    }

    /**
     * Get verification statistics for a template (Supervisor only)
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function getVerificationStats(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'template_id' => 'required|uuid|exists:logbook_template,id'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation Error',
                'errors' => $validator->errors()
            ], 422);
        }

        try {
            $currentUser = $request->user();
            $templateId = $request->template_id;

            // Check if user is a supervisor for this template
            if (!$this->isSupervisorOfTemplate($currentUser->id, $templateId)) {
                return response()->json([
                    'success' => false,
                    'message' => 'Unauthorized. Only supervisors can view verification statistics.'
                ], 403);
            }

            $totalEntries = LogbookData::where('template_id', $templateId)->count();
            $verifiedEntries = LogbookData::where('template_id', $templateId)->verified()->count();
            $unverifiedEntries = LogbookData::where('template_id', $templateId)->unverified()->count();
            
            $verificationPercentage = $totalEntries > 0 ? round(($verifiedEntries / $totalEntries) * 100, 2) : 0;

            // Get unique verifiers count
            $totalVerifications = LogbookDataVerification::whereHas('data', function ($query) use ($templateId) {
                $query->where('template_id', $templateId);
            })->count();

            $uniqueVerifiers = LogbookDataVerification::whereHas('data', function ($query) use ($templateId) {
                $query->where('template_id', $templateId);
            })->distinct('verifier_id')->count('verifier_id');

            // Recent verification activity (last 7 days)
            $recentVerifications = LogbookDataVerification::whereHas('data', function ($query) use ($templateId) {
                $query->where('template_id', $templateId);
            })
                ->where('verified_at', '>=', now()->subDays(7))
                ->count();

            // Top verifiers
            $topVerifiers = LogbookDataVerification::whereHas('data', function ($query) use ($templateId) {
                $query->where('template_id', $templateId);
            })
                ->select('verifier_id', DB::raw('COUNT(*) as verification_count'))
                ->groupBy('verifier_id')
                ->orderBy('verification_count', 'desc')
                ->limit(5)
                ->with('verifier:id,name,email')
                ->get();

            return response()->json([
                'success' => true,
                'message' => 'Verification statistics retrieved successfully',
                'data' => [
                    'total_entries' => $totalEntries,
                    'verified_entries' => $verifiedEntries,
                    'unverified_entries' => $unverifiedEntries,
                    'verification_percentage' => $verificationPercentage,
                    'total_verifications' => $totalVerifications,
                    'unique_verifiers' => $uniqueVerifiers,
                    'recent_verifications' => $recentVerifications,
                    'top_verifiers' => $topVerifiers
                ]
            ]);

        } catch (\Exception $e) {
            Log::error('Error getting verification stats: ' . $e->getMessage(), [
                'user_id' => $request->user()->id ?? 'unknown',
                'template_id' => $request->template_id ?? 'unknown',
                'trace' => $e->getTraceAsString()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Failed to retrieve verification statistics'
            ], 500);
        }
    }

    /**
     * Bulk verify multiple data entries (Supervisor only)
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function bulkVerifyData(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'template_id' => 'required|uuid|exists:logbook_template,id',
            'data_ids' => 'required|array|min:1',
            'data_ids.*' => 'required|uuid|exists:logbook_datas,id',
            'verification_notes' => 'sometimes|string|max:1000'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation Error',
                'errors' => $validator->errors()
            ], 422);
        }

        try {
            DB::beginTransaction();

            $currentUser = $request->user();
            $templateId = $request->template_id;
            $dataIds = $request->data_ids;
            $verificationNotes = $request->get('verification_notes');

            // Check if user is a supervisor for this template FIRST
            if (!$this->isSupervisorOfTemplate($currentUser->id, $templateId)) {
                return response()->json([
                    'success' => false,
                    'message' => 'Unauthorized. Only supervisors can verify data for this template.'
                ], 403);
            }

            $verifiedCount = 0;
            $skippedCount = 0;
            $errors = [];

            foreach ($dataIds as $dataId) {
                try {
                    $logbookData = LogbookData::findOrFail($dataId);

                    // Verify that the data belongs to the template
                    if ($logbookData->template_id !== $templateId) {
                        $errors[] = "Data entry {$dataId} does not belong to template {$templateId}";
                        continue;
                    }

                    // Skip if already verified by this user
                    if ($logbookData->isVerifiedBy($currentUser->id)) {
                        $skippedCount++;
                        continue;
                    }

                    // Verify the data
                    $verification = $logbookData->verify($currentUser->id, $verificationNotes);
                    $verifiedCount++;

                    // Log activity for each verification
                    AuditLog::create([
                        'user_id' => $currentUser->id,
                        'action' => 'bulk_verify_data',
                        'model_type' => 'LogbookData',
                        'model_id' => $dataId,
                        'ip_address' => $request->ip(),
                        'user_agent' => $request->userAgent(),
                        'details' => json_encode([
                            'template_id' => $logbookData->template_id,
                            'verification_id' => $verification->id,
                            'verification_notes' => $verificationNotes,
                            'verified_at' => now()
                        ])
                    ]);

                } catch (\Exception $e) {
                    $errors[] = "Failed to verify data entry {$dataId}: " . $e->getMessage();
                }
            }

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => "Successfully verified {$verifiedCount} data entries",
                'data' => [
                    'verified_count' => $verifiedCount,
                    'skipped_count' => $skippedCount,
                    'total_requested' => count($dataIds),
                    'errors' => $errors
                ]
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            
            Log::error('Error bulk verifying data: ' . $e->getMessage(), [
                'user_id' => $request->user()->id ?? 'unknown',
                'data_ids' => $request->data_ids ?? [],
                'trace' => $e->getTraceAsString()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Failed to bulk verify data entries'
            ], 500);
        }
    }

    /**
     * Bulk reject multiple data entries (Supervisor only)
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function bulkRejectData(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'template_id' => 'required|uuid|exists:logbook_template,id',
            'data_ids' => 'required|array|min:1',
            'data_ids.*' => 'required|uuid|exists:logbook_datas,id',
            'rejection_notes' => 'required|string|max:1000'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation Error',
                'errors' => $validator->errors()
            ], 422);
        }

        try {
            DB::beginTransaction();

            $currentUser = $request->user();
            $templateId = $request->template_id;
            $dataIds = $request->data_ids;
            $rejectionNotes = $request->rejection_notes;

            // Check if user is a supervisor for this template FIRST
            if (!$this->isSupervisorOfTemplate($currentUser->id, $templateId)) {
                return response()->json([
                    'success' => false,
                    'message' => 'Unauthorized. Only supervisors can reject data for this template.'
                ], 403);
            }

            $rejectedCount = 0;
            $skippedCount = 0;
            $errors = [];

            foreach ($dataIds as $dataId) {
                try {
                    $logbookData = LogbookData::findOrFail($dataId);

                    // Verify that the data belongs to the template
                    if ($logbookData->template_id !== $templateId) {
                        $errors[] = "Data entry {$dataId} does not belong to template {$templateId}";
                        continue;
                    }

                    // Check if already rejected by this user
                    $existingVerification = $logbookData->getVerificationFrom($currentUser->id);
                    if ($existingVerification && !$existingVerification->is_verified) {
                        $skippedCount++;
                        continue;
                    }

                    // Reject the data
                    $verification = $logbookData->reject($currentUser->id, $rejectionNotes);
                    $rejectedCount++;

                    // Log activity for each rejection
                    AuditLog::create([
                        'user_id' => $currentUser->id,
                        'action' => 'bulk_reject_data',
                        'model_type' => 'LogbookData',
                        'model_id' => $dataId,
                        'ip_address' => $request->ip(),
                        'user_agent' => $request->userAgent(),
                        'details' => json_encode([
                            'template_id' => $logbookData->template_id,
                            'verification_id' => $verification->id,
                            'rejection_notes' => $rejectionNotes,
                            'rejected_at' => now()
                        ])
                    ]);

                } catch (\Exception $e) {
                    $errors[] = "Failed to reject data entry {$dataId}: " . $e->getMessage();
                }
            }

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => "Successfully rejected {$rejectedCount} data entries",
                'data' => [
                    'rejected_count' => $rejectedCount,
                    'skipped_count' => $skippedCount,
                    'total_requested' => count($dataIds),
                    'errors' => $errors
                ]
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            
            Log::error('Error bulk rejecting data: ' . $e->getMessage(), [
                'user_id' => $request->user()->id ?? 'unknown',
                'data_ids' => $request->data_ids ?? [],
                'trace' => $e->getTraceAsString()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Failed to bulk reject data entries'
            ], 500);
        }
    }

    /**
     * Check if user is a supervisor of a specific template
     *
     * @param string $userId
     * @param string $templateId
     * @return bool
     */
    private function isSupervisorOfTemplate(string $userId, string $templateId): bool
    {
        $supervisorRole = LogbookRole::where('name', 'Supervisor')->first();
        
        if (!$supervisorRole) {
            return false;
        }

        return UserLogbookAccess::where('user_id', $userId)
            ->where('logbook_template_id', $templateId)
            ->where('logbook_role_id', $supervisorRole->id)
            ->exists();
    }
}
