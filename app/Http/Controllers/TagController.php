<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreTagRequest;
use App\Models\Tag;

class TagController extends Controller
{
    /**
     * List all tags alphabetically by name.
     */
    public function index()
    {
        $tags = Tag::orderBy('name')->get();

        return view('tags.index', compact('tags'));
    }

    /**
     * Create a new tag and return it as JSON for AJAX tag-picker UIs.
     */
    public function store(StoreTagRequest $request)
    {
        $tag = Tag::create($request->validated());

        return response()->json([
            'success' => true,
            'tag' => $tag,
        ]);
    }
}
