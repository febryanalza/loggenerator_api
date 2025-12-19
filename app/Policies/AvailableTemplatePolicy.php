<?php

namespace App\Policies;

use App\Models\AvailableTemplate;
use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;

class AvailableTemplatePolicy
{
    use HandlesAuthorization;

    /**
     * Determine if the user can view any templates.
     */
    public function viewAny(User $user): bool
    {
        return true; // All authenticated users can view available templates
    }

    /**
     * Determine if the user can view the template.
     */
    public function view(User $user, AvailableTemplate $template): bool
    {
        return $user->can('templates.view.all')
            || ($user->can('templates.view.institution') && $user->institution_id === $template->institution_id)
            || true; // All can view active templates
    }

    /**
     * Determine if the user can create templates.
     */
    public function create(User $user): bool
    {
        return $user->can('templates.create.any') || $user->can('templates.create.institution');
    }

    /**
     * Determine if the user can create template for specific institution.
     */
    public function createForInstitution(User $user, int $institutionId): bool
    {
        // Can create for any institution
        if ($user->can('templates.create.any')) {
            return true;
        }

        // Can create only for own institution
        if ($user->can('templates.create.institution')) {
            return $user->institution_id === $institutionId;
        }

        return false;
    }

    /**
     * Determine if the user can update the template.
     */
    public function update(User $user, AvailableTemplate $template): bool
    {
        // Can update any template
        if ($user->can('templates.update.any')) {
            return true;
        }

        // Can update only own institution's templates
        if ($user->can('templates.update.institution')) {
            return $user->institution_id === $template->institution_id;
        }

        return false;
    }

    /**
     * Determine if the user can delete the template.
     */
    public function delete(User $user, AvailableTemplate $template): bool
    {
        // Can delete any template
        if ($user->can('templates.delete.any')) {
            return true;
        }

        // Can delete only own institution's templates
        if ($user->can('templates.delete.institution')) {
            return $user->institution_id === $template->institution_id;
        }

        return false;
    }

    /**
     * Determine if the user can toggle template active status.
     */
    public function toggle(User $user, AvailableTemplate $template): bool
    {
        // Can toggle any template
        if ($user->can('templates.toggle.any')) {
            return true;
        }

        // Can toggle only own institution's templates
        if ($user->can('templates.toggle.institution')) {
            return $user->institution_id === $template->institution_id;
        }

        return false;
    }
}
