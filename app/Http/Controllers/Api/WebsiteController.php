<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\User;
use App\Models\LogbookTemplate;
use App\Models\LogbookData;

class WebsiteController extends Controller
{
    /**
     * Get homepage data for real-time display
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function getHomepageData()
    {
        try {
            // Get real-time statistics from database
            $userCount = User::count();
            $templateCount = LogbookTemplate::count();
            $dataCount = LogbookData::count();
            
            // Recent activity statistics
            $recentUsers = User::whereDate('created_at', '>=', now()->subDays(30))->count();
            $recentTemplates = LogbookTemplate::whereDate('created_at', '>=', now()->subDays(30))->count();
            $recentEntries = LogbookData::whereDate('created_at', '>=', now()->subDays(7))->count();

            $homepageData = [
                'stats' => [
                    'users' => $this->formatNumber($userCount) . '+',
                    'logbooks' => $this->formatNumber($templateCount) . '+',
                    'entries' => $this->formatNumber($dataCount) . '+',
                    'uptime' => '99.9%'
                ],
                'recent_activity' => [
                    'new_users_this_month' => $recentUsers,
                    'templates_created_this_month' => $recentTemplates,
                    'entries_this_week' => $recentEntries
                ],
                'system_info' => [
                    'last_updated' => now()->format('Y-m-d H:i:s'),
                    'total_data_points' => $userCount + $templateCount + $dataCount,
                    'active_status' => 'online'
                ]
            ];

            return response()->json([
                'success' => true,
                'data' => $homepageData,
                'timestamp' => now()->toISOString()
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to fetch homepage data',
                'error' => config('app.debug') ? $e->getMessage() : 'Internal server error'
            ], 500);
        }
    }

    /**
     * Get website statistics and data
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function getStats()
    {
        try {
            $stats = [
                'users' => [
                    'total' => User::count(),
                    'active' => User::where('status', 'active')->count(),
                    'this_month' => User::whereMonth('created_at', now()->month)
                                       ->whereYear('created_at', now()->year)
                                       ->count()
                ],
                'logbooks' => [
                    'total' => LogbookTemplate::count(),
                    'with_data' => LogbookTemplate::whereHas('data')->count(),
                    'this_month' => LogbookTemplate::whereMonth('created_at', now()->month)
                                                  ->whereYear('created_at', now()->year)
                                                  ->count()
                ],
                'entries' => [
                    'total' => LogbookData::count(),
                    'this_month' => LogbookData::whereMonth('created_at', now()->month)
                                              ->whereYear('created_at', now()->year)
                                              ->count(),
                    'today' => LogbookData::whereDate('created_at', today())->count()
                ],
                'system' => [
                    'uptime' => '99.9%',
                    'version' => config('app.version', '1.0.0'),
                    'last_updated' => now()->format('Y-m-d H:i:s')
                ]
            ];

            return response()->json([
                'success' => true,
                'data' => $stats,
                'formatted' => [
                    'users' => $this->formatNumber($stats['users']['total']) . '+',
                    'logbooks' => $this->formatNumber($stats['logbooks']['total']) . '+',
                    'entries' => $this->formatNumber($stats['entries']['total']) . '+',
                    'uptime' => $stats['system']['uptime']
                ]
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to get statistics',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get company information
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function getCompanyInfo()
    {
        try {
            $companyInfo = [
                'name' => config('company.name'),
                'tagline' => config('company.tagline'),
                'description' => config('company.description'),
                'contact' => [
                    'email' => config('company.email'),
                    'phone' => config('company.phone'),
                    'address' => config('company.address'),
                ],
                'app_links' => [
                    'android' => config('company.android_app_url'),
                    'ios' => config('company.ios_app_url'),
                ],
                'social' => config('company.social'),
                'admin_dashboard_enabled' => config('company.admin_dashboard_enabled'),
                'website_url' => config('company.website_url')
            ];

            return response()->json([
                'success' => true,
                'data' => $companyInfo
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to get company information',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get website features list
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function getFeatures()
    {
        $features = [
            [
                'icon' => '📱',
                'title' => 'Mobile-First Design',
                'description' => 'Access your logbooks anywhere, anytime with our responsive mobile application.',
                'category' => 'design'
            ],
            [
                'icon' => '⚡',
                'title' => 'Real-time Sync',
                'description' => 'Instant data synchronization across all devices and team members.',
                'category' => 'performance'
            ],
            [
                'icon' => '🔒',
                'title' => 'Secure & Compliant',
                'description' => 'Enterprise-grade security with role-based access control and audit trails.',
                'category' => 'security'
            ],
            [
                'icon' => '📊',
                'title' => 'Analytics Dashboard',
                'description' => 'Generate insights from your logbook data with powerful analytics tools.',
                'category' => 'analytics'
            ],
            [
                'icon' => '🎨',
                'title' => 'Customizable Templates',
                'description' => 'Create custom logbook templates tailored to your specific needs.',
                'category' => 'customization'
            ],
            [
                'icon' => '👥',
                'title' => 'Team Collaboration',
                'description' => 'Enable seamless collaboration with permission management and notifications.',
                'category' => 'collaboration'
            ]
        ];

        return response()->json([
            'success' => true,
            'data' => $features,
            'count' => count($features)
        ]);
    }

    /**
     * Format number for display (K, M format)
     *
     * @param int $number
     * @return string
     */
    private function formatNumber($number)
    {
        if ($number >= 1000000) {
            return number_format($number / 1000000, 1) . 'M';
        } elseif ($number >= 1000) {
            return number_format($number / 1000, 1) . 'K';
        }
        
        return (string) $number;
    }
}