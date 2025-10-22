<?php

use Illuminate\Support\Facades\Broadcast;

/*
|--------------------------------------------------------------------------
| Broadcast Channels
|--------------------------------------------------------------------------
|
| Here you may register all of the event broadcasting channels that your
| application supports. The given channel authorization callbacks are
| used to check if an authenticated user can listen to the channel.
|
*/

// Private channel for individual users to receive notifications
Broadcast::channel('user.{id}', function ($user, $id) {
    return (int) $user->id === (int) $id;
});

// Channel for logbook template owners and users with access
Broadcast::channel('logbook.template.{templateId}', function ($user, $templateId) {
    // Check if user is owner of the template or has access to it
    $template = \App\Models\LogbookTemplate::find($templateId);
    
    if (!$template) {
        return false;
    }
    
    // Allow if user is the template owner
    if ($template->user_id === $user->id) {
        return true;
    }
    
    // Allow if user has access to this template
    $hasAccess = \App\Models\UserLogbookAccess::where('user_id', $user->id)
                                            ->where('logbook_template_id', $templateId)
                                            ->exists();
    
    return $hasAccess;
});

// Channel for institution-wide notifications
Broadcast::channel('institution.{institutionId}', function ($user, $institutionId) {
    return $user->institution_id === $institutionId;
});

// Channel for role-based notifications
Broadcast::channel('role.{roleName}', function ($user, $roleName) {
    return $user->hasRole($roleName);
});