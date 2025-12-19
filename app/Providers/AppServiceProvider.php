<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Gate;
use Illuminate\Http\Resources\Json\JsonResource;
use Laravel\Sanctum\Sanctum;
use App\Models\PersonalAccessToken;
use App\Models\LogbookData;
use App\Models\UserLogbookAccess;
use App\Models\User;
use App\Models\Institution;
use App\Models\LogbookTemplate;
use App\Models\AvailableTemplate;
use App\Observers\LogbookDataObserver;
use App\Observers\UserLogbookAccessObserver;
use App\Mail\BrevoApiTransport;
use App\Policies\UserPolicy;
use App\Policies\InstitutionPolicy;
use App\Policies\LogbookTemplatePolicy;
use App\Policies\AvailableTemplatePolicy;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        Sanctum::usePersonalAccessTokenModel(PersonalAccessToken::class);
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        // Mempercepat migrasi dengan menentukan panjang string default
        Schema::defaultStringLength(191);
        
        // Menghilangkan data wrapper untuk API resources
        JsonResource::withoutWrapping();

        // Register Brevo API transport
        Mail::extend('brevo', function () {
            return new BrevoApiTransport(
                config('services.brevo.api_key')
            );
        });

        // Register observers
        UserLogbookAccess::observe(UserLogbookAccessObserver::class);
        LogbookData::observe(LogbookDataObserver::class);

        // Register policies
        Gate::policy(User::class, UserPolicy::class);
        Gate::policy(Institution::class, InstitutionPolicy::class);
        Gate::policy(LogbookTemplate::class, LogbookTemplatePolicy::class);
        Gate::policy(AvailableTemplate::class, AvailableTemplatePolicy::class);
    }
}
