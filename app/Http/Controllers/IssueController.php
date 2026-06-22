<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreIssueRequest;
use App\Http\Requests\UpdateIssueRequest;
use App\Models\Issue;
use App\Models\Project;
use App\Models\Tag;
use Illuminate\Http\Request;

class IssueController extends Controller
{
    /**
     * List issues, optionally filtered by status, priority, or tag, eager loading
     * the parent project and tags so the view never re-queries per row.
     */
    public function index(Request $request)
    {
        $issues = Issue::query()
            ->with(['project', 'tags'])
            // when() only applies the where clause if the query param was actually sent.
            ->when($request->filled('status'), fn ($query) => $query->where('status', $request->input('status')))
            ->when($request->filled('priority'), fn ($query) => $query->where('priority', $request->input('priority')))
            ->when($request->filled('tag_id'), fn ($query) => $query->whereHas('tags', fn ($q) => $q->where('tags.id', $request->input('tag_id'))))
            ->paginate(15);

        return view('issues.index', compact('issues'));
    }

    /**
     * Show the create issue form, optionally pre-selecting a project via ?project_id=.
     */
    public function create(Request $request)
    {
        $projects = Project::all();
        $tags = Tag::all();
        $selectedProjectId = $request->query('project_id');

        return view('issues.create', compact('projects', 'tags', 'selectedProjectId'));
    }

    /**
     * Store a newly created issue. project_id comes from the request input directly
     * since it isn't part of the StoreIssueRequest's field-level validation rules.
     */
    public function store(StoreIssueRequest $request)
    {
        $issue = Issue::create([
            ...$request->validated(),
            'project_id' => $request->input('project_id'),
        ]);

        return redirect()
            ->route('issues.show', $issue)
            ->with('success', 'Issue created successfully.');
    }

    /**
     * Show a single issue with its project, tags, paginated latest comments, and assigned users.
     */
    public function show(Issue $issue)
    {
        $issue->load([
            'project',
            'tags',
            'comments' => fn ($query) => $query->latest()->paginate(10),
            'users',
        ]);

        return view('issues.show', compact('issue'));
    }

    /**
     * Show the edit issue form, with all projects and tags available for re-assignment.
     */
    public function edit(Issue $issue)
    {
        $projects = Project::all();
        $tags = Tag::all();

        return view('issues.edit', compact('issue', 'projects', 'tags'));
    }

    /**
     * Update an existing issue.
     */
    public function update(UpdateIssueRequest $request, Issue $issue)
    {
        $issue->update($request->validated());

        return redirect()
            ->route('issues.show', $issue)
            ->with('success', 'Issue updated successfully.');
    }

    /**
     * Delete an issue and return to its parent project (comments/pivots cascade-delete).
     */
    public function destroy(Issue $issue)
    {
        $project = $issue->project;

        $issue->delete();

        return redirect()
            ->route('projects.show', $project)
            ->with('success', 'Issue deleted successfully.');
    }
}
