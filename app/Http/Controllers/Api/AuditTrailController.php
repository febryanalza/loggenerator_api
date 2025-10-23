<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\AuditLog;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;

class AuditTrailController extends Controller
{
    /**
     * Get audit trail statistics with date range filter
     * Supports caching on frontend (returns cacheable data)
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function getStatistics(Request $request)
    {
        try {
            $startDate = $request->get('start_date', Carbon::now()->subDays(7)->format('Y-m-d'));
            $endDate = $request->get('end_date', Carbon::now()->format('Y-m-d'));

            // Validate dates
            $start = Carbon::parse($startDate)->startOfDay();
            $end = Carbon::parse($endDate)->endOfDay();

            // Get activity statistics by action type
            $activityStats = AuditLog::select('action', DB::raw('COUNT(*) as count'))
                ->whereBetween('created_at', [$start, $end])
                ->groupBy('action')
                ->orderBy('count', 'desc')
                ->get();

            // Get daily activity trend
            $dailyActivity = AuditLog::select(
                    DB::raw('DATE(created_at) as date'),
                    DB::raw('COUNT(*) as count')
                )
                ->whereBetween('created_at', [$start, $end])
                ->groupBy(DB::raw('DATE(created_at)'))
                ->orderBy('date', 'asc')
                ->get();

            // Get activity by user (top 10)
            $userActivity = AuditLog::select('user_id', DB::raw('COUNT(*) as count'))
                ->with('user:id,name,email')
                ->whereBetween('created_at', [$start, $end])
                ->whereNotNull('user_id')
                ->groupBy('user_id')
                ->orderBy('count', 'desc')
                ->limit(10)
                ->get()
                ->map(function ($item) {
                    return [
                        'user_id' => $item->user_id,
                        'user_name' => $item->user ? $item->user->name : 'Unknown',
                        'user_email' => $item->user ? $item->user->email : 'N/A',
                        'count' => $item->count
                    ];
                });

            // Get total counts
            $totalActivities = AuditLog::whereBetween('created_at', [$start, $end])->count();
            $uniqueUsers = AuditLog::whereBetween('created_at', [$start, $end])
                ->whereNotNull('user_id')
                ->distinct('user_id')
                ->count();

            return response()->json([
                'success' => true,
                'message' => 'Audit trail statistics retrieved successfully',
                'data' => [
                    'period' => [
                        'start_date' => $start->format('Y-m-d'),
                        'end_date' => $end->format('Y-m-d'),
                        'days' => $start->diffInDays($end) + 1
                    ],
                    'summary' => [
                        'total_activities' => $totalActivities,
                        'unique_users' => $uniqueUsers,
                        'action_types' => $activityStats->count()
                    ],
                    'activity_by_type' => $activityStats,
                    'daily_trend' => $dailyActivity,
                    'top_users' => $userActivity,
                    'cache_hint' => 'Cache this data for 10 minutes'
                ]
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to retrieve audit trail statistics',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get paginated audit logs with filters
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function getLogs(Request $request)
    {
        try {
            $perPage = $request->get('per_page', 20);
            $startDate = $request->get('start_date', Carbon::now()->subDays(7)->format('Y-m-d'));
            $endDate = $request->get('end_date', Carbon::now()->format('Y-m-d'));
            $action = $request->get('action');
            $userId = $request->get('user_id');
            $search = $request->get('search');

            $start = Carbon::parse($startDate)->startOfDay();
            $end = Carbon::parse($endDate)->endOfDay();

            $query = AuditLog::with('user:id,name,email')
                ->whereBetween('created_at', [$start, $end]);

            // Apply filters
            if ($action) {
                $query->where('action', $action);
            }

            if ($userId) {
                $query->where('user_id', $userId);
            }

            if ($search) {
                $query->where(function ($q) use ($search) {
                    $q->where('description', 'ILIKE', "%{$search}%")
                        ->orWhere('action', 'ILIKE', "%{$search}%");
                });
            }

            $logs = $query->orderBy('created_at', 'desc')
                ->paginate($perPage);

            return response()->json([
                'success' => true,
                'message' => 'Audit logs retrieved successfully',
                'data' => [
                    'logs' => $logs->items(),
                    'pagination' => [
                        'current_page' => $logs->currentPage(),
                        'last_page' => $logs->lastPage(),
                        'per_page' => $logs->perPage(),
                        'total' => $logs->total(),
                        'from' => $logs->firstItem(),
                        'to' => $logs->lastItem()
                    ]
                ]
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to retrieve audit logs',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Stream real-time audit logs using Server-Sent Events (SSE)
     *
     * @param Request $request
     * @return \Symfony\Component\HttpFoundation\StreamedResponse
     */
    public function streamLogs(Request $request)
    {
        $response = response()->stream(function () use ($request) {
            // Set infinite execution time for streaming
            set_time_limit(0);
            
            $lastId = $request->get('last_id', 0);
            $action = $request->get('action');

            while (true) {
                // Query for new logs since last ID
                $query = AuditLog::with('user:id,name,email')
                    ->where('id', '>', $lastId)
                    ->orderBy('created_at', 'desc')
                    ->limit(10);

                if ($action) {
                    $query->where('action', $action);
                }

                $newLogs = $query->get();

                if ($newLogs->count() > 0) {
                    // Update last ID
                    $lastId = $newLogs->first()->id;

                    // Format logs for streaming
                    $formattedLogs = $newLogs->map(function ($log) {
                        return [
                            'id' => $log->id,
                            'action' => $log->action,
                            'description' => $log->description,
                            'user_name' => $log->user ? $log->user->name : 'System',
                            'user_email' => $log->user ? $log->user->email : 'system@app.com',
                            'ip_address' => $log->ip_address,
                            'created_at' => $log->created_at->toISOString(),
                            'created_at_human' => $log->created_at->diffForHumans()
                        ];
                    });

                    // Send SSE data
                    echo "data: " . json_encode([
                        'type' => 'new_logs',
                        'logs' => $formattedLogs,
                        'timestamp' => now()->toISOString()
                    ]) . "\n\n";
                    
                    ob_flush();
                    flush();
                } else {
                    // Send heartbeat to keep connection alive
                    echo "data: " . json_encode([
                        'type' => 'heartbeat',
                        'timestamp' => now()->toISOString()
                    ]) . "\n\n";
                    
                    ob_flush();
                    flush();
                }

                // Sleep for 2 seconds before next check
                sleep(2);

                // Check if client disconnected
                if (connection_aborted()) {
                    break;
                }
            }
        }, 200, [
            'Content-Type' => 'text/event-stream',
            'Cache-Control' => 'no-cache',
            'Connection' => 'keep-alive',
            'X-Accel-Buffering' => 'no', // Disable nginx buffering
        ]);

        return $response;
    }

    /**
     * Get available action types for filtering
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function getActionTypes()
    {
        try {
            $actionTypes = AuditLog::select('action', DB::raw('COUNT(*) as count'))
                ->groupBy('action')
                ->orderBy('count', 'desc')
                ->get();

            return response()->json([
                'success' => true,
                'message' => 'Action types retrieved successfully',
                'data' => $actionTypes
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to retrieve action types',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Export audit logs to CSV
     *
     * @param Request $request
     * @return \Illuminate\Http\Response
     */
    public function exportLogs(Request $request)
    {
        try {
            $startDate = $request->get('start_date', Carbon::now()->subDays(7)->format('Y-m-d'));
            $endDate = $request->get('end_date', Carbon::now()->format('Y-m-d'));
            $action = $request->get('action');

            $start = Carbon::parse($startDate)->startOfDay();
            $end = Carbon::parse($endDate)->endOfDay();

            $query = AuditLog::with('user:id,name,email')
                ->whereBetween('created_at', [$start, $end]);

            if ($action) {
                $query->where('action', $action);
            }

            $logs = $query->orderBy('created_at', 'desc')->get();

            // Create CSV content
            $csvContent = "Timestamp,Action,User,Email,Description,IP Address\n";
            
            foreach ($logs as $log) {
                $csvContent .= sprintf(
                    "%s,%s,%s,%s,%s,%s\n",
                    $log->created_at->format('Y-m-d H:i:s'),
                    $log->action,
                    $log->user ? $log->user->name : 'System',
                    $log->user ? $log->user->email : 'N/A',
                    '"' . str_replace('"', '""', $log->description ?? '') . '"',
                    $log->ip_address ?? 'N/A'
                );
            }

            $filename = 'audit_logs_' . $start->format('Ymd') . '_' . $end->format('Ymd') . '.csv';

            return response($csvContent, 200, [
                'Content-Type' => 'text/csv',
                'Content-Disposition' => 'attachment; filename="' . $filename . '"',
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to export audit logs',
                'error' => $e->getMessage()
            ], 500);
        }
    }
}
