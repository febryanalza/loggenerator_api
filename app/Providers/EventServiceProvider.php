<?php

namespace App\Providers;

use App\Events\LogbookAccessGranted;
use App\Events\LogbookDataUpdated;
use App\Events\NotificationSent;
use App\Events\SupervisorAddedToTemplate;
use App\Events\PermissionChanged;
use App\Events\RoleAssigned;
use App\Events\RoleRevoked;
use App\Listeners\CreateVerificationRecordsForNewSupervisor;
use App\Listeners\ResetVerificationsOnDataUpdate;
use App\Listeners\SendFCMNotification;
use App\Listeners\SendLogbookAccessNotification;
use App\Listeners\LogPermissionChange;
use App\Listeners\LogRoleAssignment;
use App\Listeners\LogRoleRevocation;
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
        PermissionChanged::class => [
            LogPermissionChange::class,
        ],
        RoleAssigned::class => [
            LogRoleAssignment::class,
        ],
        RoleRevoked::class => [
            LogRoleRevocation::class,
        ],
        NotificationSent::class => [
            SendFCMNotification::class,
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
