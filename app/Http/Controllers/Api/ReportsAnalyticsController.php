<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\LogbookTemplate;
use App\Models\LogbookData;
use App\Models\AuditLog;
use App\Models\Institution;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class ReportsAnalyticsController extends Controller
{
    // ========================================
    // LOGBOOK REPORTS
    // ========================================

    /**
     * Get logbook reports with period and institution filter
     */
    public function getLogbookReports(Request $request)
    {
        try {
            $period = $request->get('period', 'monthly'); // daily, weekly, monthly
            $institutionId = $request->get('institution_id');
            $startDate = $request->get('start_date', Carbon::now()->subMonth()->format('Y-m-d'));
            $endDate = $request->get('end_date', Carbon::now()->format('Y-m-d'));

            $start = Carbon::parse($startDate)->startOfDay();
            $end = Carbon::parse($endDate)->endOfDay();

            // Build query
            $query = LogbookData::query()
                ->join('logbook_template', 'logbook_datas.template_id', '=', 'logbook_template.id')
                ->whereBetween('logbook_datas.created_at', [$start, $end]);

            if ($institutionId) {
                $query->where('logbook_template.institution_id', $institutionId);
            }

            // Get entries by period
            $groupFormat = match($period) {
                'daily' => 'DATE(logbook_datas.created_at)',
                'weekly' => "TO_CHAR(logbook_datas.created_at, 'IYYY-IW')",
                'monthly' => "TO_CHAR(logbook_datas.created_at, 'YYYY-MM')",
                default => "TO_CHAR(logbook_datas.created_at, 'YYYY-MM')"
            };

            $entriesByPeriod = (clone $query)
                ->select(DB::raw("$groupFormat as period"), DB::raw('COUNT(*) as count'))
                ->groupBy(DB::raw($groupFormat))
                ->orderBy('period', 'asc')
                ->get();

            // Get entries by template
            $entriesByTemplate = (clone $query)
                ->select('logbook_template.name as template_name', DB::raw('COUNT(logbook_datas.id) as count'))
                ->groupBy('logbook_template.name')
                ->orderBy('count', 'desc')
                ->limit(10)
                ->get();

            // Get summary stats
            $totalEntries = (clone $query)->count();
            $totalTemplates = (clone $query)->distinct('logbook_template.id')->count('logbook_template.id');
            $avgEntriesPerDay = $totalEntries / max(1, $start->diffInDays($end) + 1);

            // Get verification stats
            $verifiedEntries = (clone $query)->where('logbook_datas.is_verified', true)->count();
            $verificationRate = $totalEntries > 0 ? round(($verifiedEntries / $totalEntries) * 100, 2) : 0;

            return response()->json([
                'success' => true,
                'data' => [
                    'period_filter' => [
                        'type' => $period,
                        'start_date' => $start->format('Y-m-d'),
                        'end_date' => $end->format('Y-m-d'),
                        'institution_id' => $institutionId
                    ],
                    'summary' => [
                        'total_entries' => $totalEntries,
                        'total_templates' => $totalTemplates,
                        'avg_entries_per_day' => round($avgEntriesPerDay, 2),
                        'verified_entries' => $verifiedEntries,
                        'verification_rate' => $verificationRate
                    ],
                    'entries_by_period' => $entriesByPeriod,
                    'entries_by_template' => $entriesByTemplate
                ]
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to retrieve logbook reports',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    // ========================================
    // USER ACTIVITY REPORTS
    // ========================================

    /**
     * Get user activity reports
     */
    public function getUserActivityReports(Request $request)
    {
        try {
            $startDate = $request->get('start_date', Carbon::now()->subMonth()->format('Y-m-d'));
            $endDate = $request->get('end_date', Carbon::now()->format('Y-m-d'));
            $institutionId = $request->get('institution_id');

            $start = Carbon::parse($startDate)->startOfDay();
            $end = Carbon::parse($endDate)->endOfDay();

            // Login frequency (from audit logs)
            $loginFrequency = AuditLog::where('action', 'login')
                ->whereBetween('created_at', [$start, $end])
                ->select(DB::raw("DATE(created_at) as date"), DB::raw('COUNT(*) as count'))
                ->groupBy(DB::raw('DATE(created_at)'))
                ->orderBy('date', 'asc')
                ->get();

            // Logbook entries submitted per user
            $userEntriesQuery = LogbookData::query()
                ->join('users', 'logbook_datas.writer_id', '=', 'users.id')
                ->whereBetween('logbook_datas.created_at', [$start, $end]);

            if ($institutionId) {
                $userEntriesQuery->where('users.institution_id', $institutionId);
            }

            $userEntries = $userEntriesQuery
                ->select('users.id', 'users.name', 'users.email', DB::raw('COUNT(logbook_datas.id) as entries_count'))
                ->groupBy('users.id', 'users.name', 'users.email')
                ->orderBy('entries_count', 'desc')
                ->limit(20)
                ->get();

            // Active users count per day
            $activeUsersPerDay = LogbookData::whereBetween('created_at', [$start, $end])
                ->select(DB::raw('DATE(created_at) as date'), DB::raw('COUNT(DISTINCT writer_id) as active_users'))
                ->groupBy(DB::raw('DATE(created_at)'))
                ->orderBy('date', 'asc')
                ->get();

            // User registration trend
            $registrationTrend = User::whereBetween('created_at', [$start, $end])
                ->select(DB::raw('DATE(created_at) as date'), DB::raw('COUNT(*) as count'))
                ->groupBy(DB::raw('DATE(created_at)'))
                ->orderBy('date', 'asc')
                ->get();

            // Summary stats
            $totalLogins = AuditLog::where('action', 'login')->whereBetween('created_at', [$start, $end])->count();
            $uniqueLoggedInUsers = AuditLog::where('action', 'login')
                ->whereBetween('created_at', [$start, $end])
                ->distinct('user_id')
                ->count('user_id');
            $newRegistrations = User::whereBetween('created_at', [$start, $end])->count();

            return response()->json([
                'success' => true,
                'data' => [
                    'period' => [
                        'start_date' => $start->format('Y-m-d'),
                        'end_date' => $end->format('Y-m-d')
                    ],
                    'summary' => [
                        'total_logins' => $totalLogins,
                        'unique_logged_in_users' => $uniqueLoggedInUsers,
                        'new_registrations' => $newRegistrations,
                        'avg_logins_per_day' => round($totalLogins / max(1, $start->diffInDays($end) + 1), 2)
                    ],
                    'login_frequency' => $loginFrequency,
                    'top_users_by_entries' => $userEntries,
                    'active_users_per_day' => $activeUsersPerDay,
                    'registration_trend' => $registrationTrend
                ]
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to retrieve user activity reports',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    // ========================================
    // INSTITUTION PERFORMANCE
    // ========================================

    /**
     * Get institution performance comparison
     */
    public function getInstitutionPerformance(Request $request)
    {
        try {
            $startDate = $request->get('start_date', Carbon::now()->subMonth()->format('Y-m-d'));
            $endDate = $request->get('end_date', Carbon::now()->format('Y-m-d'));

            $start = Carbon::parse($startDate)->startOfDay();
            $end = Carbon::parse($endDate)->endOfDay();

            // Get all institutions with their stats
            $institutions = Institution::select('institutions.id', 'institutions.name')
                ->withCount(['users', 'logbookTemplates'])
                ->get()
                ->map(function ($institution) use ($start, $end) {
                    // Count logbook entries for this institution
                    $entriesCount = LogbookData::join('logbook_template', 'logbook_datas.template_id', '=', 'logbook_template.id')
                        ->where('logbook_template.institution_id', $institution->id)
                        ->whereBetween('logbook_datas.created_at', [$start, $end])
                        ->count();

                    // Active users (users who submitted at least one entry)
                    $activeUsers = LogbookData::join('logbook_template', 'logbook_datas.template_id', '=', 'logbook_template.id')
                        ->where('logbook_template.institution_id', $institution->id)
                        ->whereBetween('logbook_datas.created_at', [$start, $end])
                        ->distinct('logbook_datas.writer_id')
                        ->count('logbook_datas.writer_id');

                    // Verified entries
                    $verifiedEntries = LogbookData::join('logbook_template', 'logbook_datas.template_id', '=', 'logbook_template.id')
                        ->where('logbook_template.institution_id', $institution->id)
                        ->where('logbook_datas.is_verified', true)
                        ->whereBetween('logbook_datas.created_at', [$start, $end])
                        ->count();

                    return [
                        'id' => $institution->id,
                        'name' => $institution->name,
                        'total_users' => $institution->users_count,
                        'total_templates' => $institution->logbook_templates_count,
                        'entries_count' => $entriesCount,
                        'active_users' => $activeUsers,
                        'verified_entries' => $verifiedEntries,
                        'verification_rate' => $entriesCount > 0 ? round(($verifiedEntries / $entriesCount) * 100, 2) : 0,
                        'avg_entries_per_user' => $activeUsers > 0 ? round($entriesCount / $activeUsers, 2) : 0
                    ];
                })
                ->sortByDesc('entries_count')
                ->values();

            // Summary
            $totalInstitutions = $institutions->count();
            $totalEntries = $institutions->sum('entries_count');
            $totalActiveUsers = $institutions->sum('active_users');
            $avgEntriesPerInstitution = $totalInstitutions > 0 ? round($totalEntries / $totalInstitutions, 2) : 0;

            return response()->json([
                'success' => true,
                'data' => [
                    'period' => [
                        'start_date' => $start->format('Y-m-d'),
                        'end_date' => $end->format('Y-m-d')
                    ],
                    'summary' => [
                        'total_institutions' => $totalInstitutions,
                        'total_entries' => $totalEntries,
                        'total_active_users' => $totalActiveUsers,
                        'avg_entries_per_institution' => $avgEntriesPerInstitution
                    ],
                    'institutions' => $institutions
                ]
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to retrieve institution performance',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    // ========================================
    // EXPORT CENTER
    // ========================================

    /**
     * Export data to CSV
     */
    public function exportData(Request $request)
    {
        try {
            $type = $request->get('type', 'logbook_entries'); // logbook_entries, users, institutions
            $startDate = $request->get('start_date', Carbon::now()->subMonth()->format('Y-m-d'));
            $endDate = $request->get('end_date', Carbon::now()->format('Y-m-d'));
            $institutionId = $request->get('institution_id');
            $format = $request->get('format', 'csv'); // csv, json

            $start = Carbon::parse($startDate)->startOfDay();
            $end = Carbon::parse($endDate)->endOfDay();

            $data = [];
            $headers = [];
            $filename = '';

            switch ($type) {
                case 'logbook_entries':
                    $query = LogbookData::with(['writer:id,name,email', 'template:id,name'])
                        ->whereBetween('created_at', [$start, $end]);

                    if ($institutionId) {
                        $query->whereHas('template', function ($q) use ($institutionId) {
                            $q->where('institution_id', $institutionId);
                        });
                    }

                    $entries = $query->orderBy('created_at', 'desc')->limit(5000)->get();
                    $headers = ['ID', 'Template', 'User', 'Email', 'Data', 'Verified', 'Created At'];
                    $data = $entries->map(function ($entry) {
                        return [
                            $entry->id,
                            $entry->template->name ?? 'N/A',
                            $entry->writer->name ?? 'N/A',
                            $entry->writer->email ?? 'N/A',
                            json_encode($entry->data),
                            $entry->is_verified ? 'Yes' : 'No',
                            $entry->created_at->format('Y-m-d H:i:s')
                        ];
                    });
                    $filename = 'logbook_entries_' . $start->format('Ymd') . '_' . $end->format('Ymd');
                    break;

                case 'users':
                    $query = User::with('institution:id,name')
                        ->whereBetween('created_at', [$start, $end]);

                    if ($institutionId) {
                        $query->where('institution_id', $institutionId);
                    }

                    $users = $query->orderBy('created_at', 'desc')->limit(5000)->get();
                    $headers = ['ID', 'Name', 'Email', 'Institution', 'Status', 'Verified', 'Created At'];
                    $data = $users->map(function ($user) {
                        return [
                            $user->id,
                            $user->name,
                            $user->email,
                            $user->institution->name ?? 'No Institution',
                            $user->status,
                            $user->email_verified_at ? 'Yes' : 'No',
                            $user->created_at->format('Y-m-d H:i:s')
                        ];
                    });
                    $filename = 'users_' . $start->format('Ymd') . '_' . $end->format('Ymd');
                    break;

                case 'institutions':
                    $institutions = Institution::withCount(['users', 'templates'])->get();
                    $headers = ['ID', 'Name', 'Description', 'Users Count', 'Templates Count', 'Phone', 'Email', 'Created At'];
                    $data = $institutions->map(function ($inst) {
                        return [
                            $inst->id,
                            $inst->name,
                            $inst->description ?? '',
                            $inst->users_count,
                            $inst->templates_count,
                            $inst->phone_number ?? '',
                            $inst->company_email ?? '',
                            $inst->created_at->format('Y-m-d H:i:s')
                        ];
                    });
                    $filename = 'institutions_' . now()->format('Ymd');
                    break;

                case 'audit_logs':
                    $logs = AuditLog::with('user:id,name,email')
                        ->whereBetween('created_at', [$start, $end])
                        ->orderBy('created_at', 'desc')
                        ->limit(5000)
                        ->get();
                    $headers = ['ID', 'Action', 'User', 'Description', 'IP Address', 'Created At'];
                    $data = $logs->map(function ($log) {
                        return [
                            $log->id,
                            $log->action,
                            $log->user->name ?? 'System',
                            $log->description ?? '',
                            $log->ip_address ?? 'N/A',
                            $log->created_at->format('Y-m-d H:i:s')
                        ];
                    });
                    $filename = 'audit_logs_' . $start->format('Ymd') . '_' . $end->format('Ymd');
                    break;

                default:
                    return response()->json([
                        'success' => false,
                        'message' => 'Invalid export type'
                    ], 400);
            }

            if ($format === 'json') {
                return response()->json([
                    'success' => true,
                    'data' => [
                        'headers' => $headers,
                        'rows' => $data
                    ]
                ]);
            }

            // Generate CSV
            $csvContent = implode(',', $headers) . "\n";
            foreach ($data as $row) {
                $csvContent .= implode(',', array_map(function ($cell) {
                    return '"' . str_replace('"', '""', $cell) . '"';
                }, $row->toArray())) . "\n";
            }

            return response($csvContent, 200, [
                'Content-Type' => 'text/csv',
                'Content-Disposition' => 'attachment; filename="' . $filename . '.csv"'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to export data',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get export templates/options
     */
    public function getExportOptions()
    {
        return response()->json([
            'success' => true,
            'data' => [
                'export_types' => [
                    ['value' => 'logbook_entries', 'label' => 'Logbook Entries', 'description' => 'Export all logbook data entries'],
                    ['value' => 'users', 'label' => 'Users', 'description' => 'Export user list with details'],
                    ['value' => 'institutions', 'label' => 'Institutions', 'description' => 'Export institution list'],
                    ['value' => 'audit_logs', 'label' => 'Audit Logs', 'description' => 'Export system audit logs']
                ],
                'formats' => [
                    ['value' => 'csv', 'label' => 'CSV (Spreadsheet)'],
                    ['value' => 'json', 'label' => 'JSON']
                ]
            ]
        ]);
    }

    // ========================================
    // SCHEDULED REPORTS (DUMMY)
    // ========================================

    /**
     * Get scheduled reports list
     */
    public function getScheduledReports()
    {
        // Dummy data for scheduled reports
        return response()->json([
            'success' => true,
            'data' => [
                'scheduled_reports' => [
                    [
                        'id' => '1',
                        'name' => 'Weekly Logbook Summary',
                        'type' => 'logbook_entries',
                        'schedule' => 'weekly',
                        'recipients' => ['admin@example.com'],
                        'last_sent' => Carbon::now()->subWeek()->format('Y-m-d H:i:s'),
                        'next_run' => Carbon::now()->addWeek()->format('Y-m-d H:i:s'),
                        'status' => 'active'
                    ],
                    [
                        'id' => '2',
                        'name' => 'Monthly User Report',
                        'type' => 'users',
                        'schedule' => 'monthly',
                        'recipients' => ['hr@example.com', 'admin@example.com'],
                        'last_sent' => Carbon::now()->subMonth()->format('Y-m-d H:i:s'),
                        'next_run' => Carbon::now()->addMonth()->format('Y-m-d H:i:s'),
                        'status' => 'active'
                    ],
                    [
                        'id' => '3',
                        'name' => 'Daily Activity Digest',
                        'type' => 'audit_logs',
                        'schedule' => 'daily',
                        'recipients' => ['security@example.com'],
                        'last_sent' => Carbon::now()->subDay()->format('Y-m-d H:i:s'),
                        'next_run' => Carbon::now()->addDay()->format('Y-m-d H:i:s'),
                        'status' => 'paused'
                    ]
                ],
                'available_schedules' => [
                    ['value' => 'daily', 'label' => 'Daily'],
                    ['value' => 'weekly', 'label' => 'Weekly'],
                    ['value' => 'monthly', 'label' => 'Monthly']
                ]
            ]
        ]);
    }

    /**
     * Create scheduled report (dummy)
     */
    public function createScheduledReport(Request $request)
    {
        // Dummy - just return success
        return response()->json([
            'success' => true,
            'message' => 'Scheduled report created successfully (demo mode)',
            'data' => [
                'id' => uniqid(),
                'name' => $request->get('name', 'New Report'),
                'type' => $request->get('type', 'logbook_entries'),
                'schedule' => $request->get('schedule', 'weekly'),
                'recipients' => $request->get('recipients', []),
                'status' => 'active',
                'next_run' => Carbon::now()->addWeek()->format('Y-m-d H:i:s')
            ]
        ]);
    }

    /**
     * Delete scheduled report (dummy)
     */
    public function deleteScheduledReport($id)
    {
        return response()->json([
            'success' => true,
            'message' => 'Scheduled report deleted successfully (demo mode)'
        ]);
    }

    /**
     * Toggle scheduled report status (dummy)
     */
    public function toggleScheduledReport($id)
    {
        return response()->json([
            'success' => true,
            'message' => 'Scheduled report status toggled (demo mode)'
        ]);
    }

    // ========================================
    // DASHBOARD SUMMARY
    // ========================================

    /**
     * Get reports dashboard summary
     */
    public function getDashboardSummary()
    {
        try {
            $today = Carbon::today();
            $thisWeek = [Carbon::now()->startOfWeek(), Carbon::now()->endOfWeek()];
            $thisMonth = [Carbon::now()->startOfMonth(), Carbon::now()->endOfMonth()];

            return response()->json([
                'success' => true,
                'data' => [
                    'quick_stats' => [
                        'entries_today' => LogbookData::whereDate('created_at', $today)->count(),
                        'entries_this_week' => LogbookData::whereBetween('created_at', $thisWeek)->count(),
                        'entries_this_month' => LogbookData::whereBetween('created_at', $thisMonth)->count(),
                        'active_users_today' => LogbookData::whereDate('created_at', $today)->distinct('writer_id')->count('writer_id'),
                        'active_users_this_week' => LogbookData::whereBetween('created_at', $thisWeek)->distinct('writer_id')->count('writer_id'),
                        'new_users_this_month' => User::whereBetween('created_at', $thisMonth)->count(),
                        'total_institutions' => Institution::count(),
                        'total_templates' => LogbookTemplate::count()
                    ],
                    'recent_activity' => AuditLog::with('user:id,name')
                        ->orderBy('created_at', 'desc')
                        ->limit(5)
                        ->get()
                        ->map(function ($log) {
                            return [
                                'action' => $log->action,
                                'user' => $log->user->name ?? 'System',
                                'time' => $log->created_at->diffForHumans()
                            ];
                        })
                ]
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to get dashboard summary',
                'error' => $e->getMessage()
            ], 500);
        }
    }
}
