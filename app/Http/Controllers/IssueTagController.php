<?php

namespace App\Http\Controllers;

use App\Models\Issue;
use App\Models\Tag;

/**
 * Handles attaching/detaching tags on an issue. This is a custom controller
 * (not a resource controller) because tag management is a pivot-table
 * toggle action, not a CRUD resource of its own.
 */
class IssueTagController extends Controller
{
    /**
     * Attach a tag to an issue if it isn't already attached, avoiding
     * duplicate pivot rows. Called via AJAX from the "Manage Tags" checkbox
     * panel on the issue show page.
     *
     * @param  Issue  $issue  Resolved via route model binding from the {issue} URL segment.
     * @param  Tag  $tag  Resolved via route model binding from the {tag} URL segment.
     * @return \Illuminate\Http\JsonResponse
     */
    public function attach(Issue $issue, Tag $tag)
    {
        // Check the pivot table directly rather than calling attach() unconditionally,
        // so toggling the same checkbox twice in a row never creates a duplicate row.
        $alreadyAttached = $issue->tags()->where('tags.id', $tag->id)->exists();

        if (! $alreadyAttached) {
            $issue->tags()->attach($tag->id);
        }

        return response()->json([
            'success' => true,
            // Lets the frontend know whether anything actually changed.
            'attached' => ! $alreadyAttached,
        ]);
    }

    /**
     * Detach a tag from an issue (removes the issue_tag pivot row). Called
     * via AJAX when a checkbox in the "Manage Tags" panel is unchecked.
     *
     * @param  Issue  $issue  Resolved via route model binding.
     * @param  Tag  $tag  Resolved via route model binding.
     * @return \Illuminate\Http\JsonResponse
     */
    public function detach(Issue $issue, Tag $tag)
    {
        // detach() is safe to call even if the pivot row doesn't exist
        // (it simply removes zero rows in that case).
        $issue->tags()->detach($tag->id);

        return response()->json([
            'success' => true,
        ]);
    }
}
