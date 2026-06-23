@extends('layouts.app')

@section('title', 'Projects')

@section('content')
    {{-- Title + "New Project" action button --}}
    <div class="page-header">
        <h1>Projects</h1>
        <a href="{{ route('projects.create') }}" class="btn-primary">New Project</a>
    </div>

    @if ($projects->isEmpty())
        {{-- Empty state shown when there are no projects at all yet --}}
        <div class="card" style="text-align: center; padding: 3rem;">
            <p style="color: var(--color-muted); margin-bottom: 1rem;">No projects yet. Create your first one.</p>
            <a href="{{ route('projects.create') }}" class="btn-primary">New Project</a>
        </div>
    @else
        {{-- Projects grid: 2 columns --}}
        <div style="display: grid; grid-template-columns: repeat(2, 1fr); gap: 1.5rem;">
            @foreach ($projects as $project)
                <div class="card">
                    <a href="{{ route('projects.show', $project) }}" style="font-weight: 600; font-size: 1.05rem;">
                        {{ $project->name }}
                    </a>

                    {{-- Description truncated to 2 lines via line-clamp --}}
                    <p style="color: var(--color-muted); margin: 0.5rem 0; display: -webkit-box; -webkit-line-clamp: 2; -webkit-box-orient: vertical; overflow: hidden;">
                        {{ $project->description }}
                    </p>

                    <div style="display: flex; justify-content: space-between; align-items: center; margin-top: 1rem; font-size: 0.8125rem; color: var(--color-muted);">
                        <span>
                            {{ $project->start_date ? $project->start_date->format('M d, Y') : '—' }}
                            &ndash;
                            {{ $project->deadline ? $project->deadline->format('M d, Y') : '—' }}
                        </span>
                        {{-- Neutral badge: this is a count, not an "open" status, so it shouldn't borrow that badge's green color. --}}
                        <span class="badge-status-closed">{{ $project->issues_count }} {{ Str::plural('issue', $project->issues_count) }}</span>
                    </div>
                </div>
            @endforeach
        </div>

        <div style="margin-top: 2rem;">
            {{ $projects->links() }}
        </div>
    @endif
@endsection
