<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreProjectRequest;
use App\Http\Requests\UpdateProjectRequest;
use App\Models\Project;

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
     * Show a single project with its issues, each issue's tags, and a comment count per issue.
     */
    public function show(Project $project)
    {
        // Eager load issues + their tags in one query each, and add a comments_count
        // per issue via withCount, so the view never triggers per-issue queries.
        $project->load([
            'issues.tags',
            'issues' => fn ($query) => $query->withCount('comments'),
        ])->loadCount('issues');

        return view('projects.show', compact('project'));
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
