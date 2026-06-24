@extends('layouts.app')

{{-- Uses the project's own name as the browser tab title. --}}
@section('title', $project->name)

@section('content')
    {{-- Project name + Edit/Delete actions, visible only to the project's owner.
         The destroy route itself is still policy-protected regardless of this check. --}}
    <div class="page-header">
        <h1>{{ $project->name }}</h1>
        {{-- @can checks ProjectPolicy::update() for the current user against
             this specific $project, hiding Edit/Delete entirely for non-owners. --}}
        @can('update', $project)
            <div style="display: flex; gap: 0.5rem;">
                <a href="{{ route('projects.edit', $project) }}" class="btn-secondary">Edit Project</a>
                {{-- x-data with no value just opts this form into Alpine so the
                     @submit confirm-dialog guard below can run. --}}
                <form
                    action="{{ route('projects.destroy', $project) }}"
                    method="POST"
                    x-data
                    @submit="if (! confirm('Delete this project? This cannot be undone.')) $event.preventDefault()"
                >
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="btn-danger">Delete Project</button>
                </form>
            </div>
        @endcan
    </div>

    {{-- Project details card: description and date range --}}
    <div class="card" style="margin-bottom: 1.5rem;">
        {{-- ?: is the "Elvis operator" — falls back to placeholder text if
             description is empty/null, since it's an optional field. --}}
        <p style="color: var(--color-muted);">{{ $project->description ?: 'No description provided.' }}</p>

        <div style="display: flex; gap: 2rem; margin-top: 1rem; font-size: 0.875rem;">
            <div>
                <span class="form-label" style="display: inline;">Start Date:</span>
                {{-- start_date is a nullable Carbon date; only call ->format() if it's set. --}}
                {{ $project->start_date ? $project->start_date->format('M d, Y') : '—' }}
            </div>
            <div>
                <span class="form-label" style="display: inline;">Deadline:</span>
                {{ $project->deadline ? $project->deadline->format('M d, Y') : '—' }}
            </div>
        </div>
    </div>

    {{-- Issues section header + "New Issue" action, pre-selecting this project --}}
    <div class="page-header">
        {{-- issues_count comes from $project->loadCount('issues') in the controller. --}}
        <h2 style="font-size: 1.125rem;">Issues ({{ $project->issues_count }})</h2>
        {{-- Passes ?project_id={id} as a query string so IssueController::create()
             can pre-select this project in the new issue's "Project" dropdown. --}}
        <a href="{{ route('issues.create', ['project_id' => $project->id]) }}" class="btn-primary">New Issue</a>
    </div>

    {{-- Filter bar: GET form so the filtered state is shareable/bookmarkable via the URL --}}
    <form method="GET" action="{{ route('projects.show', $project) }}" class="card" style="display: flex; gap: 1rem; margin-bottom: 1.5rem; padding: 1rem 1.5rem;">
        <div style="flex: 1;">
            <label for="status" class="form-label">Status</label>
            {{-- onchange="this.form.submit()" immediately re-submits the GET form
                 whenever a different option is picked, so filtering needs no extra
                 "Apply" button. @selected marks the option matching the current
                 ?status= query param so the dropdown reflects the active filter
                 after the page reloads. --}}
            <select name="status" id="status" class="form-input" onchange="this.form.submit()">
                <option value="">All</option>
                <option value="open" @selected(request('status') === 'open')>Open</option>
                <option value="in_progress" @selected(request('status') === 'in_progress')>In Progress</option>
                <option value="closed" @selected(request('status') === 'closed')>Closed</option>
            </select>
        </div>

        <div style="flex: 1;">
            <label for="priority" class="form-label">Priority</label>
            <select name="priority" id="priority" class="form-input" onchange="this.form.submit()">
                <option value="">All</option>
                <option value="low" @selected(request('priority') === 'low')>Low</option>
                <option value="medium" @selected(request('priority') === 'medium')>Medium</option>
                <option value="high" @selected(request('priority') === 'high')>High</option>
            </select>
        </div>
    </form>

    {{-- $issues is the filtered, paginated query result from ProjectController::show().
         isEmpty() is true both when the project has no issues at all, and when
         the current status/priority filters simply don't match anything. --}}
    @if ($issues->isEmpty())
        {{-- Empty state shown when the project has no issues (or none match the filters) --}}
        <div class="card" style="text-align: center; padding: 3rem;">
            <p style="color: var(--color-muted);">No issues found.</p>
        </div>
    @else
        <div class="card" style="padding: 0;">
            <table style="width: 100%; border-collapse: collapse;">
                <thead>
                    <tr style="text-align: left; border-bottom: 1px solid var(--color-border);">
                        <th style="padding: 0.75rem 1.5rem; font-size: 0.8125rem; color: var(--color-muted); font-weight: 500;">Title</th>
                        <th style="padding: 0.75rem 1.5rem; font-size: 0.8125rem; color: var(--color-muted); font-weight: 500;">Status</th>
                        <th style="padding: 0.75rem 1.5rem; font-size: 0.8125rem; color: var(--color-muted); font-weight: 500;">Priority</th>
                        <th style="padding: 0.75rem 1.5rem; font-size: 0.8125rem; color: var(--color-muted); font-weight: 500;">Due Date</th>
                        <th style="padding: 0.75rem 1.5rem; font-size: 0.8125rem; color: var(--color-muted); font-weight: 500;">Tags</th>
                        <th style="padding: 0.75rem 1.5rem; font-size: 0.8125rem; color: var(--color-muted); font-weight: 500;">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    {{-- One row per issue on the current page of the filtered results. --}}
                    @foreach ($issues as $issue)
                        <tr style="border-bottom: 1px solid var(--color-border);">
                            <td style="padding: 0.75rem 1.5rem;">
                                <a href="{{ route('issues.show', $issue) }}" style="font-weight: 500;">{{ $issue->title }}</a>
                            </td>
                            <td style="padding: 0.75rem 1.5rem;">
                                {{-- badge-status-{open|in_progress|closed} picks the matching
                                     CSS color from app.css; headline() turns "in_progress" into "In Progress". --}}
                                <span class="badge-status-{{ $issue->status }}">{{ str($issue->status)->headline() }}</span>
                            </td>
                            <td style="padding: 0.75rem 1.5rem;">
                                <span class="badge-priority-{{ $issue->priority }}">{{ str($issue->priority)->headline() }}</span>
                            </td>
                            <td style="padding: 0.75rem 1.5rem; font-size: 0.875rem; color: var(--color-muted);">
                                {{ $issue->due_date ? $issue->due_date->format('M d, Y') : '—' }}
                            </td>
                            <td style="padding: 0.75rem 1.5rem;">
                                {{-- $issue->tags was eager loaded with('tags') in the
                                     controller, so this nested loop never triggers an
                                     extra query per issue (no N+1). --}}
                                @foreach ($issue->tags as $tag)
                                    {{-- Falls back to a neutral gray if the tag has no color set. --}}
                                    <span class="badge-status-open" style="background-color: {{ $tag->color ?? '#f3f4f6' }}; color: #1f2937; margin-right: 0.25rem;">
                                        {{ $tag->name }}
                                    </span>
                                @endforeach
                            </td>
                            <td style="padding: 0.75rem 1.5rem; white-space: nowrap;">
                                <a href="{{ route('issues.show', $issue) }}" class="btn-secondary">View</a>
                                <a href="{{ route('issues.edit', $issue) }}" class="btn-secondary">Edit</a>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>

        <div style="margin-top: 1.5rem;">
            {{-- Pagination links; ->withQueryString() in the controller means
                 these links keep the current status/priority filters attached. --}}
            {{ $issues->links() }}
        </div>
    @endif
@endsection
