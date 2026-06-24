<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreTagRequest;
use App\Models\Tag;

/**
 * Handles tag listing and creation. Tags have no edit/delete UI in this
 * app (hence only index/store here, not a full resource controller).
 */
class TagController extends Controller
{
    /**
     * List every tag alphabetically by name, with a count of how many
     * issues use each one.
     *
     * @return \Illuminate\View\View
     */
    public function index()
    {
        // withCount('issues') avoids an N+1 by adding an issues_count column via a
        // subquery, instead of loading every issue for every tag just to count them.
        $tags = Tag::withCount('issues')->orderBy('name')->get();

        return view('tags.index', compact('tags'));
    }

    /**
     * Validate and persist a new tag. Supports two callers: the regular
     * "Create New Tag" form on the tags page, and any future AJAX
     * tag-picker UI that wants a JSON response instead of a redirect.
     *
     * @param  StoreTagRequest  $request  Validates name (required, unique) and color (optional hex) before this method runs.
     * @return \Illuminate\Http\JsonResponse|\Illuminate\Http\RedirectResponse
     */
    public function store(StoreTagRequest $request)
    {
        $tag = Tag::create($request->validated());

        // wantsJson() is true when the request's Accept header asks for JSON
        // (e.g. an AJAX fetch() call), as opposed to a normal browser form post.
        if ($request->wantsJson()) {
            return response()->json([
                'success' => true,
                'tag' => $tag,
            ]);
        }

        // Regular form submission: redirect back to wherever the form was
        // submitted from (the tags index page) and flash a success message.
        return redirect()
            ->back()
            ->with('success', 'Tag created successfully.');
    }
}
