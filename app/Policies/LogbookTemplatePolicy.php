<?php

namespace App\Policies;

use App\Models\LogbookTemplate;
use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;

class LogbookTemplatePolicy
{
    use HandlesAuthorization;

    /**
     * Determine if the user can view any templates.
     */
    public function viewAny(User $user): bool
    {
        return true; // All authenticated users can view templates
    }

    /**
     * Determine if the user can view the template.
     */
    public function view(User $user, LogbookTemplate $template): bool
    {
        // Can view if:
        // 1. Has view all permission
        // 2. Has view institution permission and same institution
        // 3. Is the owner
        return $user->can('logbooks.view.all')
            || ($user->can('logbooks.view.institution') && $user->institution_id === $template->institution_id)
            || $template->user_id === $user->id;
    }

    /**
     * Determine if the user can create templates.
     */
    public function create(User $user): bool
    {
        return $user->can('logbooks.create');
    }

    /**
     * Determine if the user can update the template.
     */
    public function update(User $user, LogbookTemplate $template): bool
    {
        // Can update if:
        // 1. Has update all permission
        // 2. Is the owner with update own permission
        return $user->can('logbooks.update.all')
            || ($user->can('logbooks.update.own') && $template->user_id === $user->id);
    }

    /**
     * Determine if the user can delete the template.
     */
    public function delete(User $user, LogbookTemplate $template): bool
    {
        // Can delete if:
        // 1. Has delete all permission
        // 2. Is the owner with delete own permission
        return $user->can('logbooks.delete.all')
            || ($user->can('logbooks.delete.own') && $template->user_id === $user->id);
    }

    /**
     * Determine if the user can verify logbook entries.
     */
    public function verify(User $user): bool
    {
        return $user->can('logbooks.verify');
    }

    /**
     * Determine if the user can export logbook data.
     */
    public function export(User $user): bool
    {
        return $user->can('logbooks.export');
    }
}
