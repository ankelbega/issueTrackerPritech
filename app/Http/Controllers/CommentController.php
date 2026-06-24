<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreCommentRequest;
use App\Models\Issue;

/**
 * Handles posting comments on an issue. Only a store() action exists since
 * comments in this app are never edited or deleted through the UI.
 */
class CommentController extends Controller
{
    /**
     * Create a new comment on the given issue and return it as JSON so the
     * frontend (issues/show.blade.php's Alpine component) can prepend it to
     * the visible comment list without reloading the page.
     *
     * @param  StoreCommentRequest  $request  Validates author_name and body before this method runs.
     * @param  Issue  $issue  Resolved via route model binding from the {issue} URL segment; the comment is created against this issue.
     * @return \Illuminate\Http\JsonResponse
     */
    public function store(StoreCommentRequest $request, Issue $issue)
    {
        // $issue->comments()->create(...) automatically sets issue_id to this
        // issue, so it doesn't need to be included in the validated data.
        $comment = $issue->comments()->create($request->validated());

        // Returns just the fields the frontend needs to render the new comment,
        // with created_at pre-formatted so the JS doesn't need a date library.
        return response()->json([
            'id' => $comment->id,
            'author_name' => $comment->author_name,
            'body' => $comment->body,
            'created_at' => $comment->created_at->format('M d, Y'),
        ]);
    }
}
