<?php

namespace App\Http\Controllers;

use App\Models\Issue;
use App\Models\Tag;
use Illuminate\Http\Request;

class IssueTagController extends Controller
{
    /**
     * Attach a tag to an issue if it isn't already attached, avoiding duplicate pivot rows.
     */
    public function attach(Request $request, Issue $issue)
    {
        $tag = Tag::findOrFail($request->input('tag_id'));

        $alreadyAttached = $issue->tags()->where('tags.id', $tag->id)->exists();

        if (! $alreadyAttached) {
            $issue->tags()->attach($tag->id);
        }

        return response()->json([
            'success' => true,
            'attached' => ! $alreadyAttached,
        ]);
    }

    /**
     * Detach a tag from an issue (removes the issue_tag pivot row).
     */
    public function detach(Issue $issue, Tag $tag)
    {
        $issue->tags()->detach($tag->id);

        return response()->json([
            'success' => true,
        ]);
    }
}
