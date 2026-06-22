<?php

namespace App\Http\Controllers;

use App\Models\Issue;
use App\Models\User;

class IssueUserController extends Controller
{
    /**
     * Assign a user to an issue. Both issue and user are resolved straight from the
     * route via model binding. syncWithoutDetaching avoids duplicate pivot rows for
     * a user that's already assigned.
     */
    public function attach(Issue $issue, User $user)
    {
        $issue->users()->syncWithoutDetaching([$user->id]);

        return response()->json([
            'success' => true,
        ]);
    }

    /**
     * Unassign a user from an issue (removes the issue_user pivot row).
     */
    public function detach(Issue $issue, User $user)
    {
        $issue->users()->detach($user->id);

        return response()->json([
            'success' => true,
        ]);
    }
}
