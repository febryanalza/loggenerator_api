<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\LogbookData;
use App\Models\LogbookTemplate;
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

            // Build query
            $query = LogbookData::with(['writer:id,name,email', 'verifier:id,name,email', 'template:id,name'])
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

            // Check if data is already verified
            if ($logbookData->isVerified()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Data entry is already verified.'
                ], 400);
            }

            // Verify the data
            $logbookData->markAsVerified(
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
                    'verification_notes' => $request->get('verification_notes'),
                    'verified_at' => now()
                ])
            ]);

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Data entry verified successfully',
                'data' => [
                    'id' => $logbookData->id,
                    'is_verified' => $logbookData->is_verified,
                    'verified_by' => $logbookData->verified_by,
                    'verified_at' => $logbookData->verified_at,
                    'verification_notes' => $logbookData->verification_notes
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
     * Unverify a specific logbook data entry (Supervisor only)
     *
     * @param Request $request
     * @param string $dataId
     * @return \Illuminate\Http\JsonResponse
     */
    public function unverifyData(Request $request, string $dataId)
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
                    'message' => 'Unauthorized. Only supervisors can unverify data entries.'
                ], 403);
            }

            // Check if data is not verified
            if (!$logbookData->isVerified()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Data entry is not verified.'
                ], 400);
            }

            // Unverify the data
            $logbookData->markAsUnverified();

            // Log activity
            AuditLog::create([
                'user_id' => $currentUser->id,
                'action' => 'unverify_data',
                'model_type' => 'LogbookData',
                'model_id' => $dataId,
                'ip_address' => $request->ip(),
                'user_agent' => $request->userAgent(),
                'details' => json_encode([
                    'template_id' => $logbookData->template_id,
                    'writer_id' => $logbookData->writer_id,
                    'unverified_at' => now()
                ])
            ]);

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Data entry unverified successfully',
                'data' => [
                    'id' => $logbookData->id,
                    'is_verified' => $logbookData->is_verified,
                    'verified_by' => $logbookData->verified_by,
                    'verified_at' => $logbookData->verified_at,
                    'verification_notes' => $logbookData->verification_notes
                ]
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            
            Log::error('Error unverifying data: ' . $e->getMessage(), [
                'user_id' => $request->user()->id ?? 'unknown',
                'data_id' => $dataId,
                'trace' => $e->getTraceAsString()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Failed to unverify data entry'
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

            // Recent verification activity (last 7 days)
            $recentVerifications = LogbookData::where('template_id', $templateId)
                ->where('verified_at', '>=', now()->subDays(7))
                ->verified()
                ->count();

            return response()->json([
                'success' => true,
                'message' => 'Verification statistics retrieved successfully',
                'data' => [
                    'total_entries' => $totalEntries,
                    'verified_entries' => $verifiedEntries,
                    'unverified_entries' => $unverifiedEntries,
                    'verification_percentage' => $verificationPercentage,
                    'recent_verifications' => $recentVerifications
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
            $dataIds = $request->data_ids;
            $verificationNotes = $request->get('verification_notes');

            $verifiedCount = 0;
            $errors = [];

            foreach ($dataIds as $dataId) {
                try {
                    $logbookData = LogbookData::findOrFail($dataId);

                    // Check if user is a supervisor for this template
                    if (!$this->isSupervisorOfTemplate($currentUser->id, $logbookData->template_id)) {
                        $errors[] = "Unauthorized to verify data entry: {$dataId}";
                        continue;
                    }

                    // Skip if already verified
                    if ($logbookData->isVerified()) {
                        $errors[] = "Data entry already verified: {$dataId}";
                        continue;
                    }

                    // Verify the data
                    $logbookData->markAsVerified($currentUser->id, $verificationNotes);
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
