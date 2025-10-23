<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\User;
use App\Models\LogbookTemplate;
use App\Models\LogbookData;
use App\Models\AuditLog;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class DashboardController extends Controller
{
    /**
     * API endpoint for dashboard data
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function index()
    {
        try {
            // Dashboard Statistics
            $stats = [
                'total_users' => User::count(),
                'active_users' => User::whereNotNull('email_verified_at')->count(),
                'total_templates' => LogbookTemplate::count(),
                'total_entries' => LogbookData::count(),
            ];

            // Recent Activity Stats
            $recentStats = [
                'users_today' => User::whereDate('created_at', today())->count(),
                'users_this_week' => User::whereBetween('created_at', [now()->startOfWeek(), now()->endOfWeek()])->count(),
                'users_this_month' => User::whereMonth('created_at', now()->month)->whereYear('created_at', now()->year)->count(),
                'templates_today' => LogbookTemplate::whereDate('created_at', today())->count(),
                'templates_this_week' => LogbookTemplate::whereBetween('created_at', [now()->startOfWeek(), now()->endOfWeek()])->count(),
                'entries_today' => LogbookData::whereDate('created_at', today())->count(),
                'entries_this_week' => LogbookData::whereBetween('created_at', [now()->startOfWeek(), now()->endOfWeek()])->count(),
            ];

            // Chart Data
            $chartData = [
                'daily_users' => $this->getDailyUserRegistrations(),
                'daily_entries' => $this->getDailyEntries(),
                'weekly_activity' => $this->getWeeklyActivity(),
            ];

            // Recent Users
            $recentUsers = User::orderBy('created_at', 'desc')->limit(5)->get();

            return response()->json([
                'success' => true,
                'data' => [
                    'stats' => $stats,
                    'recent_stats' => $recentStats,
                    'chart_data' => $chartData,
                    'recent_users' => $recentUsers,
                    'notifications' => $this->getSystemNotifications()
                ]
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error loading dashboard data: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get dashboard statistics (API endpoint for Bearer token access)
     */
    public function getStats()
    {
        return response()->json([
            'totalUsers' => User::count(),
            'totalTemplates' => LogbookTemplate::count(), 
            'totalEntries' => LogbookData::count(),
            'totalAuditLogs' => AuditLog::count(),
        ]);
    }

    /**
     * Get user registration data for chart (API endpoint)
     */
    public function getUserRegistrations()
    {
        $loginData = [];
        $labels = [];
        
        for ($i = 6; $i >= 0; $i--) {
            $date = now()->subDays($i);
            $count = User::whereDate('created_at', $date->format('Y-m-d'))->count();
            
            $loginData[] = $count;
            $labels[] = $date->format('M j');
        }

        return response()->json([
            'labels' => $labels,
            'data' => $loginData
        ]);
    }

    /**
     * Get logbook activity data for chart (API endpoint)
     */
    public function getLogbookActivity()
    {
        $activityData = [];
        $labels = [];
        
        for ($i = 6; $i >= 0; $i--) {
            $date = now()->subDays($i);
            $count = LogbookData::whereDate('created_at', $date->format('Y-m-d'))->count();
            
            $activityData[] = $count;
            $labels[] = $date->format('M j');
        }

        return response()->json([
            'labels' => $labels,
            'data' => $activityData
        ]);
    }

    /**
     * Get recent activity data (API endpoint)
     */
    public function getRecentActivity()
    {
        try {
            $recentActivity = AuditLog::with('user')
                ->orderBy('created_at', 'desc')
                ->limit(10)
                ->get()
                ->map(function ($log) {
                    return [
                        'id' => $log->id,
                        'action' => $log->action ?? 'N/A',
                        'model_type' => $log->model_type ?? 'Unknown',
                        'description' => $log->description ?? '',
                        'user_name' => $log->user ? $log->user->name : 'System',
                        'user_email' => $log->user ? $log->user->email : 'system@app.com',
                        'ip_address' => $log->ip_address ?? 'N/A',
                        'created_at' => $log->created_at ? $log->created_at->diffForHumans() : 'N/A',
                        'created_at_formatted' => $log->created_at ? $log->created_at->format('M j, Y H:i') : 'N/A',
                    ];
                });

            return response()->json([
                'activities' => $recentActivity
            ]);
        } catch (\Exception $e) {
            Log::error('Recent Activity Error: ' . $e->getMessage());
            return response()->json([
                'activities' => [],
                'error' => 'Failed to load recent activity'
            ], 500);
        }
    }

    /**
     * Get login activity for last 7 days
     */
    private function getLoginActivity()
    {
        $loginData = [];
        for ($i = 6; $i >= 0; $i--) {
            $date = now()->subDays($i);
            $count = User::whereDate('created_at', $date->format('Y-m-d'))->count();
            $loginData[] = [
                'date' => $date->format('M d'),
                'count' => $count
            ];
        }
        return $loginData;
    }

    /**
     * Get system notifications
     */
    private function getSystemNotifications()
    {
        return [
            [
                'type' => 'info',
                'title' => 'Sistem Berjalan Normal',
                'message' => 'Semua layanan berjalan dengan baik.',
                'time' => now()->format('H:i')
            ],
            [
                'type' => 'success',
                'title' => 'Database Backup',
                'message' => 'Backup database berhasil dilakukan.',
                'time' => now()->subHours(2)->format('H:i')
            ],
            [
                'type' => 'warning',
                'title' => 'Penggunaan Storage',
                'message' => 'Penggunaan storage mencapai 75%.',
                'time' => now()->subHours(4)->format('H:i')
            ]
        ];
    }

    /**
     * Get daily user registrations for last 30 days
     */
    private function getDailyUserRegistrations()
    {
        $data = [];
        for ($i = 29; $i >= 0; $i--) {
            $date = now()->subDays($i);
            $count = User::whereDate('created_at', $date->format('Y-m-d'))->count();
            $data[] = [
                'date' => $date->format('Y-m-d'),
                'count' => $count
            ];
        }
        return $data;
    }

    /**
     * Get daily entries for last 30 days
     */
    private function getDailyEntries()
    {
        $data = [];
        for ($i = 29; $i >= 0; $i--) {
            $date = now()->subDays($i);
            $count = LogbookData::whereDate('created_at', $date->format('Y-m-d'))->count();
            $data[] = [
                'date' => $date->format('Y-m-d'),
                'count' => $count
            ];
        }
        return $data;
    }

    /**
     * Get weekly activity summary
     */
    private function getWeeklyActivity()
    {
        return [
            'users' => User::whereBetween('created_at', [now()->startOfWeek(), now()->endOfWeek()])->count(),
            'templates' => LogbookTemplate::whereBetween('created_at', [now()->startOfWeek(), now()->endOfWeek()])->count(),
            'entries' => LogbookData::whereBetween('created_at', [now()->startOfWeek(), now()->endOfWeek()])->count(),
        ];
    }

    /**
     * User Management Page (Under Development)
     */
    public function userManagement()
    {
        return view('admin.user-management');
    }

    /**
     * Logbook Management Page - Manage all logbook templates
     */
    public function logbookManagement()
    {
        return view('admin.logbook-management');
    }

    /**
     * Content Management Page (Under Development)
     */
    public function contentManagement()
    {
        return view('admin.content-management');
    }

    /**
     * Transaction/Activity Management Page (Under Development)
     */
    public function transactions()
    {
        return view('admin.transactions');
    }
}