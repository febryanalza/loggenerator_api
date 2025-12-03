<?php

namespace App\Observers;

use App\Events\SupervisorAddedToTemplate;
use App\Models\LogbookData;
use App\Models\LogbookRole;
use App\Models\UserLogbookAccess;
use Illuminate\Support\Facades\Log;

class UserLogbookAccessObserver
{
    /**
     * The name of the supervisor role.
     */
    protected const SUPERVISOR_ROLE_NAME = 'Supervisor';

    /**
     * Handle the UserLogbookAccess "created" event.
     *
     * @param UserLogbookAccess $userLogbookAccess
     * @return void
     */
    public function created(UserLogbookAccess $userLogbookAccess): void
    {
        $this->handleSupervisorAdded($userLogbookAccess);
    }

    /**
     * Handle the UserLogbookAccess "updated" event.
     * This handles the case when a user's role is changed to Supervisor.
     *
     * @param UserLogbookAccess $userLogbookAccess
     * @return void
     */
    public function updated(UserLogbookAccess $userLogbookAccess): void
    {
        // Check if the role was changed
        if ($userLogbookAccess->isDirty('logbook_role_id')) {
            $this->handleSupervisorAdded($userLogbookAccess);
        }
    }

    /**
     * Check if the access is for a supervisor role and fire event if needed.
     *
     * @param UserLogbookAccess $userLogbookAccess
     * @return void
     */
    protected function handleSupervisorAdded(UserLogbookAccess $userLogbookAccess): void
    {
        // Load the role if not already loaded
        $userLogbookAccess->loadMissing(['logbookRole', 'user', 'logbookTemplate']);

        // Check if this is a Supervisor role
        if (!$this->isSupervisorRole($userLogbookAccess->logbookRole)) {
            return;
        }

        // Check if template has existing logbook data (efficient count check)
        $hasExistingData = LogbookData::where('template_id', $userLogbookAccess->logbook_template_id)
            ->exists();

        if (!$hasExistingData) {
            Log::debug("No existing logbook data for template, skipping verification record creation", [
                'template_id' => $userLogbookAccess->logbook_template_id,
                'supervisor_id' => $userLogbookAccess->user_id,
            ]);
            return;
        }

        Log::info("Supervisor added to template with existing data, dispatching event", [
            'supervisor_id' => $userLogbookAccess->user_id,
            'template_id' => $userLogbookAccess->logbook_template_id,
        ]);

        // Fire the event to create verification records
        event(new SupervisorAddedToTemplate(
            $userLogbookAccess->user,
            $userLogbookAccess->logbookTemplate
        ));
    }

    /**
     * Check if the given role is a Supervisor role.
     *
     * @param LogbookRole|null $role
     * @return bool
     */
    protected function isSupervisorRole(?LogbookRole $role): bool
    {
        if (!$role) {
            return false;
        }

        return strtolower($role->name) === strtolower(self::SUPERVISOR_ROLE_NAME);
    }
}
