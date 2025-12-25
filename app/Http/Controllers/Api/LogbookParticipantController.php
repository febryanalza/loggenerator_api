<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\LogbookParticipant;
use App\Models\LogbookTemplate;
use App\Models\AuditLog;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Log;

class LogbookParticipantController extends Controller
{
    /**
     * Get all participants for a specific template.
     * Accessible by template members with proper access.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function index(Request $request)
    {
        try {
            $templateId = $request->get('template_id');
            
            if (!$templateId) {
                return response()->json([
                    'success' => false,
                    'message' => 'Template ID is required'
                ], 400);
            }

            // Check if template exists
            $template = LogbookTemplate::find($templateId);
            if (!$template) {
                return response()->json([
                    'success' => false,
                    'message' => 'Template not found'
                ], 404);
            }

            $perPage = $request->get('per_page', 15);
            $query = LogbookParticipant::where('template_id', $templateId);

            // Filter by grade range if provided
            if ($request->has('min_grade') && $request->has('max_grade')) {
                $query->withGradeRange(
                    (int) $request->get('min_grade'),
                    (int) $request->get('max_grade')
                );
            }

            // Search in JSON data
            if ($request->has('search')) {
                $search = $request->get('search');
                $query->where('data', 'LIKE', "%{$search}%");
            }

            // Sorting
            $sortBy = $request->get('sort_by', 'created_at');
            $sortDirection = $request->get('sort_direction', 'desc');
            
            if (in_array($sortBy, ['grade', 'created_at', 'updated_at'])) {
                $query->orderBy($sortBy, $sortDirection);
            }

            $participants = $query->with('template:id,name')->paginate($perPage);

            return response()->json([
                'success' => true,
                'message' => 'Participants retrieved successfully',
                'data' => [
                    'participants' => $participants->items(),
                    'pagination' => [
                        'current_page' => $participants->currentPage(),
                        'last_page' => $participants->lastPage(),
                        'per_page' => $participants->perPage(),
                        'total' => $participants->total()
                    ]
                ]
            ], 200);

        } catch (\Exception $e) {
            Log::error('Failed to retrieve participants: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Failed to retrieve participants',
                'error' => config('app.debug') ? $e->getMessage() : 'Internal server error'
            ], 500);
        }
    }

    /**
     * Get a single participant by ID.
     *
     * @param  string  $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function show($id)
    {
        try {
            $participant = LogbookParticipant::with('template:id,name')->find($id);

            if (!$participant) {
                return response()->json([
                    'success' => false,
                    'message' => 'Participant not found'
                ], 404);
            }

            return response()->json([
                'success' => true,
                'message' => 'Participant retrieved successfully',
                'data' => $participant
            ], 200);

        } catch (\Exception $e) {
            Log::error('Failed to retrieve participant: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Failed to retrieve participant',
                'error' => config('app.debug') ? $e->getMessage() : 'Internal server error'
            ], 500);
        }
    }

    /**
     * Create a new participant.
     * 
     * Expected data format (based on institution's required_data_participants):
     * {
     *   "template_id": "uuid",
     *   "data": {
     *     "Nama Lengkap": "John Doe",
     *     "NIM": "12345678",
     *     "Email": "john@example.com"
     *   },
     *   "grade": 85
     * }
     * 
     * The keys in "data" object should match the "data_name" from required_data_participants table
     * for the institution. The number of fields depends on how many required data the institution has configured.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'template_id' => 'required|uuid|exists:logbook_template,id',
            'data' => 'required|array|min:1',
            'grade' => 'nullable|integer|min:1|max:100',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation error',
                'errors' => $validator->errors()
            ], 422);
        }

        try {
            $participantData = $request->input('data');
            
            $participant = LogbookParticipant::create([
                'template_id' => $request->input('template_id'),
                'data' => $participantData,
                'grade' => $request->input('grade'),
            ]);

            // Get first value from data for audit log description
            $firstValue = is_array($participantData) ? reset($participantData) : 'Unknown';
            
            // Create audit log
            AuditLog::create([
                'user_id' => Auth::id(),
                'action' => 'CREATE_PARTICIPANT',
                'description' => 'Created participant: ' . $firstValue,
                'ip_address' => $request->ip(),
                'user_agent' => $request->userAgent()
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Participant created successfully',
                'data' => $participant->load('template:id,name')
            ], 201);

        } catch (\Exception $e) {
            Log::error('Failed to create participant: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Failed to create participant',
                'error' => config('app.debug') ? $e->getMessage() : 'Internal server error'
            ], 500);
        }
    }

    /**
     * Update an existing participant.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  string  $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function update(Request $request, $id)
    {
        $validator = Validator::make($request->all(), [
            'data' => 'sometimes|required|array|min:1',
            'grade' => 'nullable|integer|min:1|max:100',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation error',
                'errors' => $validator->errors()
            ], 422);
        }

        try {
            $participant = LogbookParticipant::find($id);

            if (!$participant) {
                return response()->json([
                    'success' => false,
                    'message' => 'Participant not found'
                ], 404);
            }

            $oldData = $participant->data;
            $oldGrade = $participant->grade;

            // Update only fields that are present in request
            if ($request->has('data')) {
                $participant->data = $request->input('data');
            }
            if ($request->has('grade')) {
                $participant->grade = $request->input('grade');
            }
            $participant->save();

            // Get first value from data for audit log description
            $participantData = $participant->data;
            $firstValue = is_array($participantData) ? reset($participantData) : 'Unknown';

            // Create audit log
            AuditLog::create([
                'user_id' => Auth::id(),
                'action' => 'UPDATE_PARTICIPANT',
                'description' => 'Updated participant: ' . $firstValue,
                'ip_address' => $request->ip(),
                'user_agent' => $request->userAgent()
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Participant updated successfully',
                'data' => $participant->load('template:id,name')
            ], 200);

        } catch (\Exception $e) {
            Log::error('Failed to update participant: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Failed to update participant',
                'error' => config('app.debug') ? $e->getMessage() : 'Internal server error'
            ], 500);
        }
    }

    /**
     * Delete a participant.
     *
     * @param  string  $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function destroy(Request $request, $id)
    {
        try {
            $participant = LogbookParticipant::find($id);

            if (!$participant) {
                return response()->json([
                    'success' => false,
                    'message' => 'Participant not found'
                ], 404);
            }

            // Get first value from data for audit log description
            $participantData = $participant->data;
            $participantName = is_array($participantData) ? reset($participantData) : 'Unknown';
            $participant->delete();

            // Create audit log
            AuditLog::create([
                'user_id' => Auth::id(),
                'action' => 'DELETE_PARTICIPANT',
                'description' => 'Deleted participant: ' . $participantName,
                'ip_address' => $request->ip(),
                'user_agent' => $request->userAgent()
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Participant deleted successfully'
            ], 200);

        } catch (\Exception $e) {
            Log::error('Failed to delete participant: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Failed to delete participant',
                'error' => config('app.debug') ? $e->getMessage() : 'Internal server error'
            ], 500);
        }
    }

    /**
     * Get statistics for participants in a template.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function getStats(Request $request)
    {
        try {
            $templateId = $request->get('template_id');
            
            if (!$templateId) {
                return response()->json([
                    'success' => false,
                    'message' => 'Template ID is required'
                ], 400);
            }

            $participants = LogbookParticipant::where('template_id', $templateId)->get();
            
            $total = $participants->count();
            $withGrades = $participants->whereNotNull('grade')->count();
            $averageGrade = $participants->whereNotNull('grade')->avg('grade');
            $passed = $participants->filter(fn($p) => $p->hasPassed())->count();

            return response()->json([
                'success' => true,
                'message' => 'Statistics retrieved successfully',
                'data' => [
                    'total_participants' => $total,
                    'participants_with_grades' => $withGrades,
                    'average_grade' => $averageGrade ? round($averageGrade, 2) : null,
                    'passed_count' => $passed,
                    'pass_rate' => $withGrades > 0 ? round(($passed / $withGrades) * 100, 2) : 0
                ]
            ], 200);

        } catch (\Exception $e) {
            Log::error('Failed to get participant statistics: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Failed to get statistics',
                'error' => config('app.debug') ? $e->getMessage() : 'Internal server error'
            ], 500);
        }
    }

    /**
     * Bulk create participants.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function bulkStore(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'template_id' => 'required|uuid|exists:logbook_template,id',
            'participants' => 'required|array|min:1',
            'participants.*.data' => 'required|array|min:1',
            'participants.*.grade' => 'nullable|integer|min:1|max:100',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation error',
                'errors' => $validator->errors()
            ], 422);
        }

        try {
            $created = [];
            $templateId = $request->input('template_id');
            $participants = $request->input('participants');
            
            foreach ($participants as $participantData) {
                $participant = LogbookParticipant::create([
                    'template_id' => $templateId,
                    'data' => $participantData['data'],
                    'grade' => $participantData['grade'] ?? null,
                ]);
                $created[] = $participant;
            }

            // Create audit log
            AuditLog::create([
                'user_id' => Auth::id(),
                'action' => 'BULK_CREATE_PARTICIPANTS',
                'description' => 'Created ' . count($created) . ' participants',
                'ip_address' => $request->ip(),
                'user_agent' => $request->userAgent()
            ]);

            return response()->json([
                'success' => true,
                'message' => count($created) . ' participants created successfully',
                'data' => $created
            ], 201);

        } catch (\Exception $e) {
            Log::error('Failed to bulk create participants: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Failed to create participants',
                'error' => config('app.debug') ? $e->getMessage() : 'Internal server error'
            ], 500);
        }
    }

    /**
     * Update participant grade (for Supervisor only).
     * Allows Supervisor to give grades without modifying participant data.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  string  $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function updateGrade(Request $request, $id)
    {
        $validator = Validator::make($request->all(), [
            'grade' => 'required|integer|min:1|max:100',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation error',
                'errors' => $validator->errors()
            ], 422);
        }

        try {
            $participant = LogbookParticipant::find($id);

            if (!$participant) {
                return response()->json([
                    'success' => false,
                    'message' => 'Participant not found'
                ], 404);
            }

            $oldGrade = $participant->grade;
            $newGrade = $request->input('grade');
            $participant->grade = $newGrade;
            $participant->save();

            // Create audit log
            AuditLog::create([
                'user_id' => Auth::id(),
                'action' => 'UPDATE_PARTICIPANT_GRADE',
                'description' => "Updated participant grade from {$oldGrade} to {$newGrade}",
                'ip_address' => $request->ip(),
                'user_agent' => $request->userAgent()
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Participant grade updated successfully',
                'data' => $participant->load('template:id,name')
            ], 200);

        } catch (\Exception $e) {
            Log::error('Failed to update participant grade: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Failed to update participant grade',
                'error' => config('app.debug') ? $e->getMessage() : 'Internal server error'
            ], 500);
        }
    }

    /**
     * Bulk update participant grades (for Supervisor only).
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function bulkUpdateGrades(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'grades' => 'required|array|min:1',
            'grades.*.participant_id' => 'required|uuid|exists:logbook_participants,id',
            'grades.*.grade' => 'required|integer|min:1|max:100',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation error',
                'errors' => $validator->errors()
            ], 422);
        }

        try {
            $updated = [];
            $grades = $request->input('grades');
            
            foreach ($grades as $gradeData) {
                $participant = LogbookParticipant::find($gradeData['participant_id']);
                if ($participant) {
                    $participant->grade = $gradeData['grade'];
                    $participant->save();
                    $updated[] = $participant;
                }
            }

            // Create audit log
            AuditLog::create([
                'user_id' => Auth::id(),
                'action' => 'BULK_UPDATE_PARTICIPANT_GRADES',
                'description' => 'Updated grades for ' . count($updated) . ' participants',
                'ip_address' => $request->ip(),
                'user_agent' => $request->userAgent()
            ]);

            return response()->json([
                'success' => true,
                'message' => count($updated) . ' participant grades updated successfully',
                'data' => $updated
            ], 200);

        } catch (\Exception $e) {
            Log::error('Failed to bulk update participant grades: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Failed to update participant grades',
                'error' => config('app.debug') ? $e->getMessage() : 'Internal server error'
            ], 500);
        }
    }

    /**
     * Get all participants with their grades for a specific template.
     * Used for displaying participant list with grading status.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function getParticipantsList(Request $request)
    {
        try {
            $templateId = $request->get('template_id');
            
            if (!$templateId) {
                return response()->json([
                    'success' => false,
                    'message' => 'Template ID is required'
                ], 400);
            }

            // Check if template exists
            $template = LogbookTemplate::find($templateId);
            if (!$template) {
                return response()->json([
                    'success' => false,
                    'message' => 'Template not found'
                ], 404);
            }

            $participants = LogbookParticipant::where('template_id', $templateId)
                ->orderBy('created_at', 'desc')
                ->get()
                ->map(function ($participant) {
                    $participantData = $participant->data;
                    $name = is_array($participantData) ? reset($participantData) : 'Unknown';
                    
                    return [
                        'id' => $participant->id,
                        'name' => $name,
                        'data' => $participantData,
                        'grade' => $participant->grade,
                        'has_grade' => $participant->grade !== null,
                        'passed' => $participant->hasPassed(),
                        'created_at' => $participant->created_at,
                        'updated_at' => $participant->updated_at,
                    ];
                });

            return response()->json([
                'success' => true,
                'message' => 'Participants list retrieved successfully',
                'data' => [
                    'template' => [
                        'id' => $template->id,
                        'name' => $template->name
                    ],
                    'participants' => $participants,
                    'total' => $participants->count(),
                    'graded' => $participants->where('has_grade', true)->count(),
                    'passed' => $participants->where('passed', true)->count()
                ]
            ], 200);

        } catch (\Exception $e) {
            Log::error('Failed to retrieve participants list: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Failed to retrieve participants list',
                'error' => config('app.debug') ? $e->getMessage() : 'Internal server error'
            ], 500);
        }
    }
}
