<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreProjectRequest;
use App\Http\Requests\UpdateProjectRequest;
use App\Models\Project;
use Illuminate\Http\Request;

/**
 * Handles all CRUD actions for projects: listing, creating, viewing,
 * editing, and deleting. Edit/update/delete are additionally gated by the
 * ProjectPolicy so only the project's owner can perform them.
 */
class ProjectController extends Controller
{
    /**
     * Display a paginated list of every project, each annotated with its
     * issue count.
     *
     * @return \Illuminate\View\View
     */
    public function index()
    {
        // withCount('issues') avoids an N+1 by adding an issues_count column via a subquery
        // instead of loading every issue for every project just to count them.
        $projects = Project::withCount('issues')
            ->paginate(10); // 10 projects per page, rendered with pagination links in the view.

        // Hand the paginator straight to the view; Blade reads $projects->isEmpty(),
        // foreach's over it, and calls $projects->links() to render pagination.
        return view('projects.index', compact('projects'));
    }

    /**
     * Show the blank "create project" form.
     *
     * @return \Illuminate\View\View
     */
    public function create()
    {
        return view('projects.create');
    }

    /**
     * Validate and persist a new project, owned by whoever is currently
     * logged in.
     *
     * @param  StoreProjectRequest  $request  Validates name/description/start_date/deadline before this method runs.
     * @return \Illuminate\Http\RedirectResponse
     */
    public function store(StoreProjectRequest $request)
    {
        $project = Project::create([
            // $request->validated() only contains the fields that passed validation
            // (name, description, start_date, deadline) — never raw, unchecked input.
            ...$request->validated(),
            // user_id is set here, not taken from form input, so a user can never
            // submit a project "as" someone else.
            'user_id' => auth()->id(),
        ]);

        // Redirect to the new project's own page and flash a success banner
        // (rendered by layouts/partials/flash.blade.php on the next request).
        return redirect()
            ->route('projects.show', $project)
            ->with('success', 'Project created successfully.');
    }

    /**
     * Show a single project, including its issues (optionally filtered by
     * status/priority) and each issue's tags.
     *
     * @param  Request  $request  Used to read the optional ?status= and ?priority= filter query params.
     * @param  Project  $project  Resolved automatically by Laravel's route model binding from the {project} URL segment.
     * @return \Illuminate\View\View
     */
    public function show(Request $request, Project $project)
    {
        // Eager load tags for the issues table below, so it never triggers a
        // per-row query. when() only applies a filter if it was sent. No
        // comment count is loaded here since projects/show.blade.php doesn't display one.
        $issues = $project->issues()
            ->with('tags') // Loads every matching issue's tags in one extra query instead of one query per issue.
            // Only adds a WHERE status = ... clause if the status filter was actually submitted.
            ->when($request->filled('status'), fn ($query) => $query->where('status', $request->input('status')))
            // Only adds a WHERE priority = ... clause if the priority filter was actually submitted.
            ->when($request->filled('priority'), fn ($query) => $query->where('priority', $request->input('priority')))
            ->paginate(10)
            // Keeps the current ?status=&priority= query string attached to the
            // pagination links, so paging through results doesn't lose the filters.
            ->withQueryString();

        // Adds an issues_count property to $project itself (used in the page
        // heading "Issues (N)"), separate from the filtered/paginated $issues above.
        $project->loadCount('issues');

        return view('projects.show', compact('project', 'issues'));
    }

    /**
     * Show the "edit project" form, pre-filled with the project's current data.
     *
     * @param  Project  $project  Resolved via route model binding.
     * @return \Illuminate\View\View
     */
    public function edit(Project $project)
    {
        // Throws a 403 response automatically if the logged-in user isn't
        // this project's owner (see ProjectPolicy::update()).
        $this->authorize('update', $project);

        return view('projects.edit', compact('project'));
    }

    /**
     * Validate and persist changes to an existing project.
     *
     * @param  UpdateProjectRequest  $request  Validates the submitted fields before this method runs.
     * @param  Project  $project  Resolved via route model binding.
     * @return \Illuminate\Http\RedirectResponse
     */
    public function update(UpdateProjectRequest $request, Project $project)
    {
        // Same ownership check as edit(); also prevents a non-owner from
        // bypassing the UI and POSTing directly to the update route.
        $this->authorize('update', $project);

        $project->update($request->validated());

        return redirect()
            ->route('projects.show', $project)
            ->with('success', 'Project updated successfully.');
    }

    /**
     * Delete a project. Its issues/comments/tag and user pivot rows
     * cascade-delete automatically via the foreign key constraints defined
     * in the migrations.
     *
     * @param  Project  $project  Resolved via route model binding.
     * @return \Illuminate\Http\RedirectResponse
     */
    public function destroy(Project $project)
    {
        // Throws a 403 response automatically if the logged-in user isn't
        // this project's owner (see ProjectPolicy::delete()).
        $this->authorize('delete', $project);

        $project->delete();

        return redirect()
            ->route('projects.index')
            ->with('success', 'Project deleted successfully.');
    }
}
