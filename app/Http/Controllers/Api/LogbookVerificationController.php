<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\LogbookTemplate;
use App\Models\User;
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
     * Update logbook verification status
     * New workflow: Owner verifies first, then Supervisor, then Institution Admin can assess
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function updateVerificationStatus(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'template_id' => 'required|uuid|exists:logbook_template,id',
            'has_been_verified_logbook' => 'required|boolean'
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
            $hasBeenVerifiedLogbook = $request->has_been_verified_logbook;

            // Get the logbook template
            $template = LogbookTemplate::findOrFail($templateId);

            // Check if current user has access to this template and get their role
            $currentUserAccess = UserLogbookAccess::where('user_id', $currentUser->id)
                ->where('logbook_template_id', $templateId)
                ->with('logbookRole')
                ->first();

            if (!$currentUserAccess) {
                return response()->json([
                    'success' => false,
                    'message' => 'You do not have access to this logbook template.'
                ], 403);
            }

            $currentUserRole = $currentUserAccess->logbookRole->name;

            // New logic: Only Owner and Supervisor can verify, but in sequence
            // Owner must verify first, then Supervisor
            if (!in_array($currentUserRole, ['Owner', 'Supervisor'])) {
                return response()->json([
                    'success' => false,
                    'message' => 'Unauthorized. Only Owner and Supervisor can update verification status.'
                ], 403);
            }

            // Get Owner and Supervisor access records for this template
            $ownerAccess = UserLogbookAccess::where('logbook_template_id', $templateId)
                ->whereHas('logbookRole', function($query) {
                    $query->where('name', 'Owner');
                })
                ->first();

            $supervisorAccess = UserLogbookAccess::where('logbook_template_id', $templateId)
                ->whereHas('logbookRole', function($query) {
                    $query->where('name', 'Supervisor');
                })
                ->first();

            // Sequential verification logic
            if ($currentUserRole === 'Owner') {
                // Owner can always verify
                $currentUserAccess->has_been_verified_logbook = $hasBeenVerifiedLogbook;
                $currentUserAccess->save();
                $verifiedRole = 'Owner';
                
            } else if ($currentUserRole === 'Supervisor') {
                // Supervisor can only verify if Owner has already verified
                if ($ownerAccess && !$ownerAccess->has_been_verified_logbook) {
                    return response()->json([
                        'success' => false,
                        'message' => 'Supervisor cannot verify until Owner has verified first.'
                    ], 422);
                }
                
                $currentUserAccess->has_been_verified_logbook = $hasBeenVerifiedLogbook;
                $currentUserAccess->save();
                $verifiedRole = 'Supervisor';
            }

            // Create audit log
            AuditLog::create([
                'user_id' => $currentUser->id,
                'action' => 'UPDATE_LOGBOOK_VERIFICATION',
                'table_name' => 'user_logbook_access',
                'record_id' => $currentUserAccess->id,
                'old_values' => ['has_been_verified_logbook' => !$hasBeenVerifiedLogbook],
                'new_values' => ['has_been_verified_logbook' => $hasBeenVerifiedLogbook],
                'ip_address' => $request->ip(),
                'user_agent' => $request->userAgent(),
                'description' => "{$verifiedRole} {$currentUser->name} " . ($hasBeenVerifiedLogbook ? 'verified' : 'unverified') . " logbook template: {$template->name}"
            ]);

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Logbook verification status updated successfully',
                'data' => [
                    'template_id' => $template->id,
                    'template_name' => $template->name,
                    'user_role' => $verifiedRole,
                    'user_name' => $currentUser->name,
                    'has_been_verified_logbook' => $hasBeenVerifiedLogbook,
                    'updated_at' => $currentUserAccess->updated_at
                ]
            ]);

        } catch (\Exception $e) {
            Log::error('Failed to update verification status: ' . $e->getMessage());

            return response()->json([
                'success' => false,
                'message' => 'Failed to update verification status.',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get logbook verification status for a template
     * Shows the sequential verification status: Owner -> Supervisor -> Assessment ready
     *
     * @param Request $request
     * @param string $templateId
     * @return \Illuminate\Http\JsonResponse
     */
    public function getVerificationStatus(Request $request, string $templateId)
    {
        try {
            $currentUser = $request->user();

            // Check if user has access to this template
            $userAccess = UserLogbookAccess::where('user_id', $currentUser->id)
                ->where('logbook_template_id', $templateId)
                ->exists();

            if (!$userAccess) {
                return response()->json([
                    'success' => false,
                    'message' => 'You do not have access to this template.'
                ], 403);
            }

            // Get template info
            $template = LogbookTemplate::findOrFail($templateId);

            // Get Owner and Supervisor verification status specifically
            $ownerStatus = UserLogbookAccess::where('logbook_template_id', $templateId)
                ->whereHas('logbookRole', function($query) {
                    $query->where('name', 'Owner');
                })
                ->with(['user', 'logbookRole'])
                ->first();

            $supervisorStatus = UserLogbookAccess::where('logbook_template_id', $templateId)
                ->whereHas('logbookRole', function($query) {
                    $query->where('name', 'Supervisor');
                })
                ->with(['user', 'logbookRole'])
                ->first();

            // Determine if assessment is ready (both Owner and Supervisor verified)
            $ownerVerified = $ownerStatus ? $ownerStatus->has_been_verified_logbook : false;
            $supervisorVerified = $supervisorStatus ? $supervisorStatus->has_been_verified_logbook : false;
            $assessmentReady = $ownerVerified && $supervisorVerified;

            $verificationData = [
                'template_id' => $template->id,
                'template_name' => $template->name,
                'has_been_assessed' => $template->has_been_assessed,
                'owner_verification' => $ownerStatus ? [
                    'user_id' => $ownerStatus->user->id,
                    'user_name' => $ownerStatus->user->name,
                    'user_email' => $ownerStatus->user->email,
                    'has_been_verified_logbook' => $ownerStatus->has_been_verified_logbook,
                    'updated_at' => $ownerStatus->updated_at
                ] : null,
                'supervisor_verification' => $supervisorStatus ? [
                    'user_id' => $supervisorStatus->user->id,
                    'user_name' => $supervisorStatus->user->name,
                    'user_email' => $supervisorStatus->user->email,
                    'has_been_verified_logbook' => $supervisorStatus->has_been_verified_logbook,
                    'updated_at' => $supervisorStatus->updated_at
                ] : null,
                'assessment_ready' => $assessmentReady,
                'verification_workflow' => [
                    'step_1_owner' => $ownerVerified ? 'completed' : 'pending',
                    'step_2_supervisor' => $supervisorVerified ? 'completed' : ($ownerVerified ? 'ready' : 'waiting_for_owner'),
                    'step_3_assessment' => $template->has_been_assessed ? 'completed' : ($assessmentReady ? 'ready' : 'waiting_for_verification')
                ]
            ];

            return response()->json([
                'success' => true,
                'message' => 'Logbook verification status retrieved successfully',
                'data' => $verificationData
            ]);

        } catch (\Exception $e) {
            Log::error('Failed to get verification status: ' . $e->getMessage());

            return response()->json([
                'success' => false,
                'message' => 'Failed to get verification status.',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Update assessment status for template (Institution Admin only)
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function updateAssessmentStatus(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'template_id' => 'required|uuid|exists:logbook_template,id',
            'has_been_assessed' => 'required|boolean'
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
            $hasBeenAssessed = $request->has_been_assessed;

            // Check if user is Institution Admin
            if (!$currentUser->hasRole('Institution Admin')) {
                return response()->json([
                    'success' => false,
                    'message' => 'Unauthorized. Only Institution Admin can update assessment status.'
                ], 403);
            }

            $template = LogbookTemplate::find($templateId);

            // Check if Institution Admin belongs to the same institution as the template
            if ($template->institution_id && $currentUser->institution_id !== $template->institution_id) {
                return response()->json([
                    'success' => false,
                    'message' => 'Unauthorized. You can only assess templates from your institution.'
                ], 403);
            }

            // If setting to true, check if both Owner and Supervisor have verified logbook
            if ($hasBeenAssessed) {
                // Check Owner verification
                $ownerVerified = UserLogbookAccess::where('logbook_template_id', $templateId)
                    ->whereHas('logbookRole', function($query) {
                        $query->where('name', 'Owner');
                    })
                    ->where('has_been_verified_logbook', true)
                    ->exists();

                // Check Supervisor verification
                $supervisorVerified = UserLogbookAccess::where('logbook_template_id', $templateId)
                    ->whereHas('logbookRole', function($query) {
                        $query->where('name', 'Supervisor');
                    })
                    ->where('has_been_verified_logbook', true)
                    ->exists();

                if (!$ownerVerified || !$supervisorVerified) {
                    $missingVerifications = [];
                    if (!$ownerVerified) $missingVerifications[] = 'Owner';
                    if (!$supervisorVerified) $missingVerifications[] = 'Supervisor';

                    return response()->json([
                        'success' => false,
                        'message' => 'Cannot assess template. The following roles have not verified the logbook yet: ' . implode(', ', $missingVerifications),
                        'missing_verifications' => $missingVerifications
                    ], 422);
                }
            }

            // Update assessment status
            $template->update([
                'has_been_assessed' => $hasBeenAssessed
            ]);

            // Create audit log
            \App\Models\AuditLog::create([
                'user_id' => $currentUser->id,
                'action' => 'UPDATE_ASSESSMENT',
                'description' => "Updated assessment status for template '{$template->name}' to " . ($hasBeenAssessed ? 'assessed' : 'not assessed'),
                'ip_address' => $request->ip(),
                'user_agent' => $request->userAgent()
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Assessment status updated successfully',
                'data' => [
                    'template_id' => $templateId,
                    'template_name' => $template->name,
                    'has_been_assessed' => $hasBeenAssessed,
                    'assessed_by' => $currentUser->name,
                    'updated_at' => $template->updated_at
                ]
            ]);

        } catch (\Exception $e) {
            Log::error('Failed to update assessment status: ' . $e->getMessage());

            return response()->json([
                'success' => false,
                'message' => 'Failed to update assessment status.',
                'error' => $e->getMessage()
            ], 500);
        }
    }
}
