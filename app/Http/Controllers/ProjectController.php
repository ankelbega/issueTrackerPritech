<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreProjectRequest;
use App\Http\Requests\UpdateProjectRequest;
use App\Models\Project;
use Illuminate\Http\Request;

class ProjectController extends Controller
{
    /**
     * List all projects with their issue count, paginated.
     */
    public function index()
    {
        // withCount('issues') avoids an N+1 by adding an issues_count column via a subquery
        // instead of loading every issue for every project just to count them.
        $projects = Project::withCount('issues')
            ->paginate(10);

        return view('projects.index', compact('projects'));
    }

    /**
     * Show the create project form.
     */
    public function create()
    {
        return view('projects.create');
    }

    /**
     * Store a newly created project.
     */
    public function store(StoreProjectRequest $request)
    {
        $project = Project::create($request->validated());

        return redirect()
            ->route('projects.show', $project)
            ->with('success', 'Project created successfully.');
    }

    /**
     * Show a single project with its issues (optionally filtered by status/priority),
     * each issue's tags, and a comment count per issue.
     */
    public function show(Request $request, Project $project)
    {
        // Eager load tags and a comments_count per issue, so the table below never
        // triggers per-row queries. when() only applies a filter if it was sent.
        $issues = $project->issues()
            ->with('tags')
            ->withCount('comments')
            ->when($request->filled('status'), fn ($query) => $query->where('status', $request->input('status')))
            ->when($request->filled('priority'), fn ($query) => $query->where('priority', $request->input('priority')))
            ->paginate(10)
            ->withQueryString();

        $project->loadCount('issues');

        return view('projects.show', compact('project', 'issues'));
    }

    /**
     * Show the edit project form.
     */
    public function edit(Project $project)
    {
        return view('projects.edit', compact('project'));
    }

    /**
     * Update an existing project.
     */
    public function update(UpdateProjectRequest $request, Project $project)
    {
        $project->update($request->validated());

        return redirect()
            ->route('projects.show', $project)
            ->with('success', 'Project updated successfully.');
    }

    /**
     * Delete a project (its issues/comments/pivots cascade-delete via FK constraints).
     */
    public function destroy(Project $project)
    {
        $project->delete();

        return redirect()
            ->route('projects.index')
            ->with('success', 'Project deleted successfully.');
    }
}
