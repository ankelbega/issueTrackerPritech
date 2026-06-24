@extends('layouts.app')

@section('title', 'Issues')

@section('content')
    <div class="page-header">
        <h1>Issues</h1>
    </div>

    {{-- Filter bar: GET form so filters live in the URL and survive pagination. --}}
    <form method="GET" action="{{ route('issues.index') }}" class="card" style="display: flex; gap: 1rem; align-items: flex-end; margin-bottom: 1.5rem; padding: 1rem 1.5rem;">
        <div style="flex: 1;">
            <label for="status" class="form-label">Status</label>
            {{-- Auto-submits the GET form on change, so picking a filter applies
                 it immediately without a separate "Apply" button. @selected marks
                 whichever option matches the current ?status= query param. --}}
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

        <div style="flex: 1;">
            <label for="tag_id" class="form-label">Tag</label>
            <select name="tag_id" id="tag_id" class="form-input" onchange="this.form.submit()">
                <option value="">All</option>
                {{-- $tags is the full alphabetical tag list passed from
                     IssueController::index(), so every tag is selectable
                     regardless of whether it appears in the current results. --}}
                @foreach ($tags as $tag)
                    {{-- (string) casts on both sides because request('tag_id') is a
                         string from the URL, while $tag->id is an integer — without
                         the cast, "1" == 1 would still work in PHP's loose comparison,
                         but the explicit cast keeps this exact-match check unambiguous. --}}
                    <option value="{{ $tag->id }}" @selected((string) request('tag_id') === (string) $tag->id)>{{ $tag->name }}</option>
                @endforeach
            </select>
        </div>

        {{-- Reset clears all filters by just linking back to the bare index route. --}}
        <a href="{{ route('issues.index') }}" style="color: var(--color-muted); font-size: 0.875rem; padding-bottom: 0.5rem;">Reset</a>
    </form>

    {{-- $issues is the filtered, paginated query result from IssueController::index().
         isEmpty() is true both when there are no issues at all, and when the
         current filters simply don't match anything. --}}
    @if ($issues->isEmpty())
        {{-- Empty state shown when no issues match the current filters --}}
        <div class="card" style="text-align: center; padding: 3rem;">
            <p style="color: var(--color-muted);">No issues match these filters.</p>
        </div>
    @else
        <div class="card" style="padding: 0;">
            <table style="width: 100%; border-collapse: collapse;">
                <thead>
                    <tr style="text-align: left; border-bottom: 1px solid var(--color-border);">
                        <th style="padding: 0.75rem 1.5rem; font-size: 0.8125rem; color: var(--color-muted); font-weight: 500;">Title</th>
                        <th style="padding: 0.75rem 1.5rem; font-size: 0.8125rem; color: var(--color-muted); font-weight: 500;">Project</th>
                        <th style="padding: 0.75rem 1.5rem; font-size: 0.8125rem; color: var(--color-muted); font-weight: 500;">Status</th>
                        <th style="padding: 0.75rem 1.5rem; font-size: 0.8125rem; color: var(--color-muted); font-weight: 500;">Priority</th>
                        <th style="padding: 0.75rem 1.5rem; font-size: 0.8125rem; color: var(--color-muted); font-weight: 500;">Due Date</th>
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
                                {{-- $issue->project was eager loaded with(['project', 'tags'])
                                     in the controller, so this never triggers an extra
                                     per-row query (no N+1). --}}
                                <a href="{{ route('projects.show', $issue->project) }}" style="color: var(--color-muted);">{{ $issue->project->name }}</a>
                            </td>
                            <td style="padding: 0.75rem 1.5rem;">
                                {{-- headline() turns "in_progress" into "In Progress" for display. --}}
                                <span class="badge-status-{{ $issue->status }}">{{ str($issue->status)->headline() }}</span>
                            </td>
                            <td style="padding: 0.75rem 1.5rem;">
                                <span class="badge-priority-{{ $issue->priority }}">{{ str($issue->priority)->headline() }}</span>
                            </td>
                            <td style="padding: 0.75rem 1.5rem; font-size: 0.875rem; color: var(--color-muted);">
                                {{-- due_date is a nullable Carbon date; only format it if set. --}}
                                {{ $issue->due_date ? $issue->due_date->format('M d, Y') : '—' }}
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
            {{-- Pagination links; ->withQueryString() in the controller keeps the
                 current status/priority/tag_id filters attached across pages. --}}
            {{ $issues->links() }}
        </div>
    @endif
@endsection
