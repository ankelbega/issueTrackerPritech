{{-- Renders just the issues results: the table of matching issues (or an
     empty-state message) plus pagination links. Extracted into its own
     partial so IssueController::index() can return this exact same markup
     for two cases: a normal full-page load, and an AJAX search/filter
     request that only needs to replace the results area, not the whole page. --}}

{{-- $issues is the filtered, paginated query result from IssueController::index().
     isEmpty() is true both when there are no issues at all, and when the
     current filters/search term simply don't match anything. --}}
@if ($issues->isEmpty())
    {{-- Empty state shown when no issues match the current filters/search. --}}
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
                {{-- One row per issue on the current page of the filtered/searched results. --}}
                @foreach ($issues as $issue)
                    <tr style="border-bottom: 1px solid var(--color-border);">
                        <td style="padding: 0.75rem 1.5rem;">
                            <a href="{{ route('issues.show', $issue) }}" style="font-weight: 500;">{{ $issue->title }}</a>
                        </td>
                        <td style="padding: 0.75rem 1.5rem;">
                            {{-- $issue->project was eager loaded with('project') in the
                                 controller, so this never triggers an extra per-row query (no N+1). --}}
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
             current status/priority/tag_id/search filters attached across pages. --}}
        {{ $issues->links() }}
    </div>
@endif
