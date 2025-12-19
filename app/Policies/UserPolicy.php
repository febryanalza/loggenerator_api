<?php

namespace App\Policies;

use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;

class UserPolicy
{
    use HandlesAuthorization;

    /**
     * Determine if the user can view any users.
     */
    public function viewAny(User $user): bool
    {
        return $user->can('users.view.all') || $user->can('users.view.institution');
    }

    /**
     * Determine if the user can view the model.
     */
    public function view(User $user, User $model): bool
    {
        // Can view if:
        // 1. Has view all permission
        // 2. Has view institution permission and same institution
        // 3. Is viewing own profile
        return $user->can('users.view.all')
            || ($user->can('users.view.institution') && $user->institution_id === $model->institution_id)
            || $user->id === $model->id;
    }

    /**
     * Determine if the user can create users.
     */
    public function create(User $user): bool
    {
        return $user->can('users.create');
    }

    /**
     * Determine if the user can update the model.
     */
    public function update(User $user, User $model): bool
    {
        // Can update if:
        // 1. Has update all permission
        // 2. Has update institution permission and same institution
        // 3. Has update own permission and updating self
        return $user->can('users.update.all')
            || ($user->can('users.update.institution') && $user->institution_id === $model->institution_id)
            || ($user->can('users.update.own') && $user->id === $model->id);
    }

    /**
     * Determine if the user can delete the model.
     */
    public function delete(User $user, User $model): bool
    {
        // Cannot delete self
        if ($user->id === $model->id) {
            return false;
        }

        // Super Admin can delete anyone
        if ($user->can('users.delete.any')) {
            return true;
        }

        // Basic delete permission - cannot delete Super Admin or Admin
        if ($user->can('users.delete')) {
            return !$model->hasRole(['Super Admin', 'Admin']);
        }

        return false;
    }

    /**
     * Determine if the user can assign roles.
     */
    public function assignRole(User $user, string $roleName): bool
    {
        // Can assign any role including Admin
        if ($user->can('users.assign-role.any')) {
            return true;
        }

        // Can assign basic roles only (not Super Admin or Admin)
        if ($user->can('users.assign-role.basic')) {
            return !in_array($roleName, ['Super Admin', 'Admin']);
        }

        return false;
    }

    /**
     * Determine if the user can search users.
     */
    public function search(User $user): bool
    {
        return $user->can('users.search');
    }

    /**
     * Determine if the user can export users.
     */
    public function export(User $user): bool
    {
        return $user->can('users.export');
    }
}
