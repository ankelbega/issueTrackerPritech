<?php

namespace App\Http\Controllers;

use App\Models\Issue;
use App\Models\User;

/**
 * Handles assigning/unassigning users on an issue. This is a custom
 * controller (not a resource controller) because user assignment is a
 * pivot-table toggle action, not a CRUD resource of its own.
 */
class IssueUserController extends Controller
{
    /**
     * Assign a user to an issue. Called via AJAX from the "Assigned Users"
     * checkbox panel on the issue show page.
     *
     * @param  Issue  $issue  Resolved via route model binding from the {issue} URL segment.
     * @param  User  $user  Resolved via route model binding from the {user} URL segment.
     * @return \Illuminate\Http\JsonResponse
     */
    public function attach(Issue $issue, User $user)
    {
        // syncWithoutDetaching adds this user to the pivot table without
        // removing any other already-assigned users, and without creating a
        // duplicate row if this user happens to already be assigned.
        $issue->users()->syncWithoutDetaching([$user->id]);

        return response()->json([
            'success' => true,
        ]);
    }

    /**
     * Unassign a user from an issue (removes the issue_user pivot row).
     * Called via AJAX when a checkbox in the "Assigned Users" panel is unchecked.
     *
     * @param  Issue  $issue  Resolved via route model binding.
     * @param  User  $user  Resolved via route model binding.
     * @return \Illuminate\Http\JsonResponse
     */
    public function detach(Issue $issue, User $user)
    {
        // detach() is safe to call even if the pivot row doesn't exist
        // (it simply removes zero rows in that case).
        $issue->users()->detach($user->id);

        return response()->json([
            'success' => true,
        ]);
    }
}
