<?php

namespace App\Http\Controllers;

use App\Models\Issue;
use App\Models\User;
use Illuminate\Http\Request;

class IssueUserController extends Controller
{
    /**
     * Assign a user to an issue. syncWithoutDetaching avoids duplicate pivot rows
     * for a user that's already assigned.
     */
    public function attach(Request $request, Issue $issue)
    {
        $user = User::findOrFail($request->input('user_id'));

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
