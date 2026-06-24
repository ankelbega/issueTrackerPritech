<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreIssueRequest;
use App\Http\Requests\UpdateIssueRequest;
use App\Models\Issue;
use App\Models\Project;
use App\Models\Tag;
use App\Models\User;
use Illuminate\Http\Request;

/**
 * Handles all CRUD actions for issues: listing/filtering, creating,
 * viewing (including its tags/comments/assignees), editing, and deleting.
 */
class IssueController extends Controller
{
    /**
     * Display a paginated, filterable, searchable list of every issue
     * across all projects, with each issue's parent project eager loaded.
     *
     * The optional ?search= query param does a case-insensitive partial
     * match against both the issue's title and its description, combined
     * with the existing ?status=, ?priority=, and ?tag_id= filters. When
     * the request carries an X-Requested-With: XMLHttpRequest header (sent
     * by the search box's debounced fetch() call in issues/index.blade.php),
     * only the results table partial is returned instead of the full page,
     * so the JS can swap it in without a reload.
     *
     * @param  Request  $request  Used to read the optional ?status=, ?priority=, ?tag_id=, and ?search= filter query params, and to detect AJAX requests.
     * @return \Illuminate\View\View
     */
    public function index(Request $request)
    {
        $issues = Issue::query()
            // Loads the parent project for every matching issue in one extra
            // query instead of one query per issue. Tags aren't eager loaded
            // here since issues/index.blade.php doesn't display them.
            ->with('project')
            // when() only applies the where clause if the query param was actually sent.
            ->when($request->filled('status'), fn ($query) => $query->where('status', $request->input('status')))
            ->when($request->filled('priority'), fn ($query) => $query->where('priority', $request->input('priority')))
            // whereHas filters to only issues that have at least one tag matching
            // the selected tag_id, via a sub-query against the issue_tag pivot table.
            ->when($request->filled('tag_id'), fn ($query) => $query->whereHas('tags', fn ($q) => $q->where('tags.id', $request->input('tag_id'))))
            // when() only adds the search clause at all if ?search= was actually
            // submitted (a falsy/empty value skips this entirely, so a blank
            // search box doesn't add a no-op WHERE clause to the query).
            ->when($request->search, fn ($q) => $q->where(
                // The inner where(fn ($q) => ...) groups both LIKE checks inside
                // their own parentheses in the generated SQL ("AND (title LIKE
                // ... OR description LIKE ...)"), so this OR doesn't accidentally
                // swallow the status/priority/tag_id AND conditions built above.
                fn ($q) => $q->where('title', 'like', "%{$request->search}%")
                    // orWhere matches issues whose description (not just title)
                    // contains the search term, so a search hits either field.
                    ->orWhere('description', 'like', "%{$request->search}%")
            ))
            ->paginate(15)
            // Keeps the current ?status=&priority=&tag_id=&search= query string
            // attached to the pagination links, so paging through results
            // doesn't lose any of the active filters or the search term.
            ->withQueryString();

        // ajax() is true only when the request carries the X-Requested-With:
        // XMLHttpRequest header. Browsers never add that header on their own —
        // it's set explicitly by the search box's fetch() call below — so a
        // normal full-page visit to this route always falls through to the
        // regular branch further down instead.
        if ($request->ajax()) {
            // Returns only the results table/empty-state/pagination markup
            // (no sidebar, no filter bar) so the JS can drop it straight into
            // the results container without re-rendering the whole page.
            return view('issues.partials.table', compact('issues'));
        }

        // All tags for the filter dropdown (so the user can pick any tag, not just
        // ones already represented in the current filtered/paginated results).
        $tags = Tag::orderBy('name')->get();

        return view('issues.index', compact('issues', 'tags'));
    }

    /**
     * Show the blank "create issue" form, optionally pre-selecting a
     * project if the request arrived with a ?project_id= query parameter
     * (e.g. clicking "New Issue" from a project's page).
     *
     * @param  Request  $request  Used to read the optional ?project_id= query param.
     * @return \Illuminate\View\View
     */
    public function create(Request $request)
    {
        $projects = Project::all(); // Every project, for the "Project" select dropdown.
        $selectedProjectId = $request->query('project_id'); // Pre-selects this project in the dropdown if present.

        return view('issues.create', compact('projects', 'selectedProjectId'));
    }

    /**
     * Validate and persist a new issue.
     *
     * @param  StoreIssueRequest  $request  Validates project_id/title/description/status/priority/due_date before this method runs.
     * @return \Illuminate\Http\RedirectResponse
     */
    public function store(StoreIssueRequest $request)
    {
        // $request->validated() includes project_id since it's part of
        // StoreIssueRequest's rules, so no extra merging is needed here.
        $issue = Issue::create($request->validated());

        return redirect()
            ->route('issues.show', $issue)
            ->with('success', 'Issue created successfully.');
    }

    /**
     * Show a single issue with its project, tags, assigned users, and a
     * paginated list of its most recent comments. Also serves as a JSON
     * endpoint for the "Load more" comments button on this same page.
     *
     * @param  Request  $request  Used to detect AJAX/JSON requests (for "Load more") and to read the ?page= param the paginator uses.
     * @param  Issue  $issue  Resolved automatically via route model binding from the {issue} URL segment.
     * @return \Illuminate\View\View|\Illuminate\Http\JsonResponse
     */
    public function show(Request $request, Issue $issue)
    {
        // Loads the parent project, attached tags, and assigned users in three
        // extra queries total, so the view never triggers per-relation queries.
        $issue->load(['project', 'tags', 'users']);

        // Comments are paginated separately (not via the eager load above) because
        // paginate() inside a relation closure on a single model silently collapses
        // back into a plain Collection and loses its pagination metadata.
        $comments = $issue->comments()->latest()->paginate(10)->withQueryString();

        // The "Load more" button fetches subsequent pages via AJAX; respond with
        // JSON for that case instead of re-rendering the whole page.
        if ($request->wantsJson()) {
            return response()->json([
                // Reshape each comment into just the fields the frontend needs,
                // with created_at pre-formatted so the JS doesn't need a date library.
                'data' => $comments->map(fn ($comment) => [
                    'id' => $comment->id,
                    'author_name' => $comment->author_name,
                    'body' => $comment->body,
                    'created_at' => $comment->created_at->format('M d, Y'),
                ]),
                // Tells the frontend whether there's another page to fetch (null if not).
                'next_page_url' => $comments->nextPageUrl(),
            ]);
        }

        // All tags and users for the "manage tags"/"assign users" dropdown panels
        // (the full lists, so the user can attach/assign ones not yet attached).
        $tags = Tag::orderBy('name')->get();
        $users = User::orderBy('name')->get();

        return view('issues.show', compact('issue', 'comments', 'tags', 'users'));
    }

    /**
     * Show the "edit issue" form, pre-filled with the issue's current data
     * and with every project available for re-assignment.
     *
     * @param  Issue  $issue  Resolved via route model binding.
     * @return \Illuminate\View\View
     */
    public function edit(Issue $issue)
    {
        $projects = Project::all(); // Lets the user move this issue to a different project.

        return view('issues.edit', compact('issue', 'projects'));
    }

    /**
     * Validate and persist changes to an existing issue.
     *
     * @param  UpdateIssueRequest  $request  Validates the submitted fields before this method runs.
     * @param  Issue  $issue  Resolved via route model binding.
     * @return \Illuminate\Http\RedirectResponse
     */
    public function update(UpdateIssueRequest $request, Issue $issue)
    {
        $issue->update($request->validated());

        return redirect()
            ->route('issues.show', $issue)
            ->with('success', 'Issue updated successfully.');
    }

    /**
     * Delete an issue and return the user to its parent project. The
     * issue's comments and tag/user pivot rows cascade-delete automatically
     * via the foreign key constraints defined in the migrations.
     *
     * @param  Issue  $issue  Resolved via route model binding.
     * @return \Illuminate\Http\RedirectResponse
     */
    public function destroy(Issue $issue)
    {
        // Captured before delete() so we still have it for the redirect below
        // (accessing $issue->project after deletion would still work since the
        // model instance stays in memory, but grabbing it first keeps intent clear).
        $project = $issue->project;

        $issue->delete();

        return redirect()
            ->route('projects.show', $project)
            ->with('success', 'Issue deleted successfully.');
    }
}
