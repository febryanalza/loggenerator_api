<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\User;
use App\Models\LogbookTemplate;
use App\Models\LogbookData;

class HomeController extends Controller
{
    /**
     * Display the homepage with company profile
     *
     * @return \Illuminate\Contracts\View\View
     */
    public function index()
    {
        // Get dynamic statistics from database with real-time data
        $userCount = User::count();
        $templateCount = LogbookTemplate::count();
        $dataCount = LogbookData::count();
        
        // Additional real-time statistics using correct column names
        $activeUsersToday = User::whereDate('last_login', today())->count();
        $templatesThisMonth = LogbookTemplate::whereMonth('created_at', now()->month)
                                           ->whereYear('created_at', now()->year)
                                           ->count();
        $entriesThisWeek = LogbookData::whereBetween('created_at', [now()->startOfWeek(), now()->endOfWeek()])->count();
        
        $companyData = [
            'name' => config('company.name', config('app.name', 'LogGenerator')),
            'tagline' => config('company.tagline', 'Simplifying Digital Logbook Management'),
            'description' => config('company.description', 'Transform your traditional paper-based logbooks into powerful digital solutions. Streamline data collection, enhance accessibility, and boost productivity with our intuitive logbook management platform.'),
            'features' => [
                [
                    'icon' => '📱',
                    'title' => 'Mobile-First Design',
                    'description' => 'Access your logbooks anywhere, anytime with our responsive mobile application.'
                ],
                [
                    'icon' => '⚡',
                    'title' => 'Real-time Sync',
                    'description' => 'Instant data synchronization across all devices and team members.'
                ],
                [
                    'icon' => '🔒',
                    'title' => 'Secure & Compliant',
                    'description' => 'Enterprise-grade security with role-based access control and audit trails.'
                ],
                [
                    'icon' => '📊',
                    'title' => 'Analytics Dashboard',
                    'description' => 'Generate insights from your logbook data with powerful analytics tools.'
                ],
                [
                    'icon' => '🎨',
                    'title' => 'Customizable Templates',
                    'description' => 'Create custom logbook templates tailored to your specific needs.'
                ],
                [
                    'icon' => '👥',
                    'title' => 'Team Collaboration',
                    'description' => 'Enable seamless collaboration with permission management and notifications.'
                ]
            ],
            'app_links' => [
                'android' => config('company.android_app_url', 'https://play.google.com/store/apps/details?id=com.loggenerator.app'),
                'ios' => config('company.ios_app_url', 'https://apps.apple.com/app/loggenerator/id123456789')
            ],
            'contact' => [
                'email' => config('company.email', 'support@loggenerator.com'),
                'phone' => config('company.phone', '+62 21 1234 5678'),
                'address' => config('company.address', 'Jakarta, Indonesia')
            ],
            'stats' => [
                'users' => $this->formatNumber($userCount),
                'logbooks' => $this->formatNumber($templateCount),
                'entries' => $this->formatNumber($dataCount),
                'uptime' => '99.9%'
            ],
            'real_time_stats' => [
                'active_users_today' => $activeUsersToday,
                'templates_this_month' => $templatesThisMonth,
                'entries_this_week' => $entriesThisWeek,
                'total_activity' => $userCount + $templateCount + $dataCount,
                'last_updated' => now()->format('d M Y, H:i:s')
            ]
        ];

        return view('welcome', compact('companyData'));
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

    /**
     * Show about page
     *
     * @return \Illuminate\Contracts\View\View
     */
    public function about()
    {
        return view('pages.about');
    }

    /**
     * Show contact page
     *
     * @return \Illuminate\Contracts\View\View
     */
    public function contact()
    {
        return view('pages.contact');
    }
}
