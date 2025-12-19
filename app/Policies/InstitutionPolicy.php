<?php

namespace App\Policies;

use App\Models\Institution;
use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;

class InstitutionPolicy
{
    use HandlesAuthorization;

    /**
     * Determine if the user can view any institutions.
     */
    public function viewAny(User $user): bool
    {
        return $user->can('institutions.view.all') || $user->can('institutions.view.own');
    }

    /**
     * Determine if the user can view the institution.
     */
    public function view(User $user, Institution $institution): bool
    {
        return $user->can('institutions.view.all')
            || ($user->can('institutions.view.own') && $user->institution_id === $institution->id);
    }

    /**
     * Determine if the user can create institutions.
     */
    public function create(User $user): bool
    {
        return $user->can('institutions.create');
    }

    /**
     * Determine if the user can update the institution.
     */
    public function update(User $user, Institution $institution): bool
    {
        return $user->can('institutions.update.all')
            || ($user->can('institutions.update.own') && $user->institution_id === $institution->id);
    }

    /**
     * Determine if the user can delete the institution.
     */
    public function delete(User $user, Institution $institution): bool
    {
        // Cannot delete own institution
        if ($user->institution_id === $institution->id) {
            return false;
        }

        return $user->can('institutions.delete');
    }

    /**
     * Determine if the user can view members of the institution.
     */
    public function viewMembers(User $user, Institution $institution): bool
    {
        return $user->can('institution.view-members')
            && ($user->can('institutions.view.all') || $user->institution_id === $institution->id);
    }

    /**
     * Determine if the user can manage members of the institution.
     */
    public function manageMembers(User $user, Institution $institution): bool
    {
        return $user->can('institution.manage-members')
            && ($user->can('institutions.manage') || $user->institution_id === $institution->id);
    }
}
