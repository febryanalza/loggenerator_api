<?php

namespace App\Providers;

use App\Events\LogbookAccessGranted;
use App\Events\LogbookDataUpdated;
use App\Events\SupervisorAddedToTemplate;
use App\Listeners\CreateVerificationRecordsForNewSupervisor;
use App\Listeners\ResetVerificationsOnDataUpdate;
use App\Listeners\SendLogbookAccessNotification;
use Illuminate\Foundation\Support\Providers\EventServiceProvider as ServiceProvider;

class EventServiceProvider extends ServiceProvider
{
    /**
     * The event listener mappings for the application.
     *
     * @var array<class-string, array<int, class-string>>
     */
    protected $listen = [
        LogbookAccessGranted::class => [
            SendLogbookAccessNotification::class,
        ],
        SupervisorAddedToTemplate::class => [
            CreateVerificationRecordsForNewSupervisor::class,
        ],
        LogbookDataUpdated::class => [
            ResetVerificationsOnDataUpdate::class,
        ],
    ];

    /**
     * Register services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap services.
     */
    public function boot(): void
    {
        parent::boot();
    }
}
