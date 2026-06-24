{{-- Extends the shared app shell (sidebar + main content area). --}}
@extends('layouts.app')

{{-- Sets the browser tab title via @yield('title') in the layout. --}}
@section('title', 'Projects')

@section('content')
    {{-- Title + "New Project" action button --}}
    <div class="page-header">
        <h1>Projects</h1>
        <a href="{{ route('projects.create') }}" class="btn-primary">New Project</a>
    </div>

    {{-- $projects is a paginator (see ProjectController::index()). isEmpty()
         checks whether the current page has zero results — true both when
         there are truly no projects, and (in this single-page case) when none exist. --}}
    @if ($projects->isEmpty())
        {{-- Empty state shown when there are no projects at all yet --}}
        <div class="card" style="text-align: center; padding: 3rem;">
            <p style="color: var(--color-muted); margin-bottom: 1rem;">No projects yet. Create your first one.</p>
            <a href="{{ route('projects.create') }}" class="btn-primary">New Project</a>
        </div>
    @else
        {{-- Projects grid: 2 columns --}}
        <div style="display: grid; grid-template-columns: repeat(2, 1fr); gap: 1.5rem;">
            {{-- Loops over every project on the current page of the paginator,
                 rendering one card per project. --}}
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
                            {{-- start_date/deadline are cast to Carbon dates on the Project
                                 model, but can be null, so each is ternary-checked before
                                 calling ->format() to avoid a "call on null" error. --}}
                            {{ $project->start_date ? $project->start_date->format('M d, Y') : '—' }}
                            &ndash;
                            {{ $project->deadline ? $project->deadline->format('M d, Y') : '—' }}
                        </span>
                        {{-- Neutral badge: this is a count, not an "open" status, so it shouldn't borrow that badge's green color. --}}
                        {{-- issues_count comes from withCount('issues') in the controller;
                             Str::plural adds an "s" automatically unless the count is exactly 1. --}}
                        <span class="badge-status-closed">{{ $project->issues_count }} {{ Str::plural('issue', $project->issues_count) }}</span>
                    </div>

                    {{-- Edit/Delete are only visible to the project's owner; the
                         destroy route itself is still policy-protected regardless. --}}
                    {{-- @can checks the ProjectPolicy::update() rule (current user's id
                         matches the project's user_id) and only renders this block if it passes. --}}
                    @can('update', $project)
                        <div style="display: flex; gap: 0.5rem; margin-top: 0.75rem;">
                            <a href="{{ route('projects.edit', $project) }}" class="btn-secondary">Edit</a>
                            {{-- x-data (with no value) opts this <form> into Alpine so the
                                 @submit listener below can run; it has no local state of its own. --}}
                            <form
                                action="{{ route('projects.destroy', $project) }}"
                                method="POST"
                                x-data
                                @submit="if (! confirm('Delete this project? This cannot be undone.')) $event.preventDefault()"
                            >
                                {{-- @csrf is required on every state-changing (POST/PUT/PATCH/DELETE) form. --}}
                                @csrf
                                {{-- HTML forms only support GET/POST natively; @method('DELETE')
                                     adds a hidden _method field so Laravel routes this as a DELETE request. --}}
                                @method('DELETE')
                                <button type="submit" class="btn-danger">Delete</button>
                            </form>
                        </div>
                    @endcan
                </div>
            @endforeach
        </div>

        <div style="margin-top: 2rem;">
            {{-- Renders Laravel's default pagination links (Previous/Next +
                 page numbers), automatically preserving the current page state. --}}
            {{ $projects->links() }}
        </div>
    @endif
@endsection
