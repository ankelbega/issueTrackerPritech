<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreTagRequest;
use App\Models\Tag;

class TagController extends Controller
{
    /**
     * List all tags alphabetically by name, with a count of issues using each one
     * (withCount avoids an N+1 by adding an issues_count column via a subquery).
     */
    public function index()
    {
        $tags = Tag::withCount('issues')->orderBy('name')->get();

        return view('tags.index', compact('tags'));
    }

    /**
     * Create a new tag. Regular form submissions redirect back with a flash
     * message; AJAX tag-picker UIs that send Accept: application/json get JSON back.
     */
    public function store(StoreTagRequest $request)
    {
        $tag = Tag::create($request->validated());

        if ($request->wantsJson()) {
            return response()->json([
                'success' => true,
                'tag' => $tag,
            ]);
        }

        return redirect()
            ->back()
            ->with('success', 'Tag created successfully.');
    }
}
