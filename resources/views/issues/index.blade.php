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
                @foreach ($tags as $tag)
                    <option value="{{ $tag->id }}" @selected((string) request('tag_id') === (string) $tag->id)>{{ $tag->name }}</option>
                @endforeach
            </select>
        </div>

        {{-- Reset clears all filters by just linking back to the bare index route. --}}
        <a href="{{ route('issues.index') }}" style="color: var(--color-muted); font-size: 0.875rem; padding-bottom: 0.5rem;">Reset</a>
    </form>

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
                    @foreach ($issues as $issue)
                        <tr style="border-bottom: 1px solid var(--color-border);">
                            <td style="padding: 0.75rem 1.5rem;">
                                <a href="{{ route('issues.show', $issue) }}" style="font-weight: 500;">{{ $issue->title }}</a>
                            </td>
                            <td style="padding: 0.75rem 1.5rem;">
                                <a href="{{ route('projects.show', $issue->project) }}" style="color: var(--color-muted);">{{ $issue->project->name }}</a>
                            </td>
                            <td style="padding: 0.75rem 1.5rem;">
                                <span class="badge-status-{{ $issue->status }}">{{ str($issue->status)->headline() }}</span>
                            </td>
                            <td style="padding: 0.75rem 1.5rem;">
                                <span class="badge-priority-{{ $issue->priority }}">{{ str($issue->priority)->headline() }}</span>
                            </td>
                            <td style="padding: 0.75rem 1.5rem; font-size: 0.875rem; color: var(--color-muted);">
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
            {{ $issues->links() }}
        </div>
    @endif
@endsection
