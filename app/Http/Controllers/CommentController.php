<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreCommentRequest;
use App\Models\Issue;

class CommentController extends Controller
{
    /**
     * Create a new comment on the given issue and return it as JSON
     * so the frontend can append it to the comment list without a full reload.
     */
    public function store(StoreCommentRequest $request, Issue $issue)
    {
        $comment = $issue->comments()->create($request->validated());

        return response()->json([
            'id' => $comment->id,
            'author_name' => $comment->author_name,
            'body' => $comment->body,
            'created_at' => $comment->created_at->format('M d, Y'),
        ]);
    }
}
