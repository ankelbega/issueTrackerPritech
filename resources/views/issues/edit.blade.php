@extends('layouts.app')

@section('title', 'Edit Issue')

@section('content')
    <a href="{{ route('issues.show', $issue) }}" style="color: var(--color-muted); font-size: 0.875rem; display: inline-block; margin-bottom: 1rem;">
        &larr; Back to Issue
    </a>

    <div class="card">
        {{-- Submits to IssueController::update(), validated by UpdateIssueRequest. --}}
        <form action="{{ route('issues.update', $issue) }}" method="POST">
            @csrf
            {{-- HTML forms can't send PUT directly; this hidden field tells Laravel
                 to treat this POST as a PUT request, matching the resourceful route. --}}
            @method('PUT')

            <div style="margin-bottom: 1.25rem;">
                <label for="project_id" class="form-label">Project</label>
                <select name="project_id" id="project_id" class="form-input" required>
                    {{-- old('project_id', $issue->project_id): prefers the previous
                         submission if validation just failed, otherwise falls back
                         to the issue's current project — this lets the user reassign
                         the issue to a different project by simply picking a new one. --}}
                    @foreach ($projects as $project)
                        <option value="{{ $project->id }}" @selected((string) old('project_id', $issue->project_id) === (string) $project->id)>
                            {{ $project->name }}
                        </option>
                    @endforeach
                </select>
                @error('project_id')
                    <p class="form-error">{{ $message }}</p>
                @enderror
            </div>

            <div style="margin-bottom: 1.25rem;">
                <label for="title" class="form-label">Title</label>
                <input type="text" name="title" id="title" value="{{ old('title', $issue->title) }}" class="form-input" required>
                @error('title')
                    <p class="form-error">{{ $message }}</p>
                @enderror
            </div>

            <div style="margin-bottom: 1.25rem;">
                <label for="description" class="form-label">Description</label>
                <textarea name="description" id="description" rows="4" class="form-input">{{ old('description', $issue->description) }}</textarea>
                @error('description')
                    <p class="form-error">{{ $message }}</p>
                @enderror
            </div>

            <div style="display: grid; grid-template-columns: 1fr 1fr 1fr; gap: 1.25rem; margin-bottom: 1.5rem;">
                <div>
                    <label for="status" class="form-label">Status</label>
                    {{-- Pre-selects the issue's current status, falling back to it on a failed re-submission too. --}}
                    <select name="status" id="status" class="form-input" required>
                        <option value="open" @selected(old('status', $issue->status) === 'open')>Open</option>
                        <option value="in_progress" @selected(old('status', $issue->status) === 'in_progress')>In Progress</option>
                        <option value="closed" @selected(old('status', $issue->status) === 'closed')>Closed</option>
                    </select>
                    @error('status')
                        <p class="form-error">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label for="priority" class="form-label">Priority</label>
                    <select name="priority" id="priority" class="form-input" required>
                        <option value="low" @selected(old('priority', $issue->priority) === 'low')>Low</option>
                        <option value="medium" @selected(old('priority', $issue->priority) === 'medium')>Medium</option>
                        <option value="high" @selected(old('priority', $issue->priority) === 'high')>High</option>
                    </select>
                    @error('priority')
                        <p class="form-error">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label for="due_date" class="form-label">Due Date</label>
                    {{-- ?->format('Y-m-d') uses PHP's null-safe operator: if due_date
                         is null, the whole expression evaluates to null and the
                         date input is simply left blank instead of erroring. --}}
                    <input type="date" name="due_date" id="due_date" value="{{ old('due_date', $issue->due_date?->format('Y-m-d')) }}" class="form-input">
                    @error('due_date')
                        <p class="form-error">{{ $message }}</p>
                    @enderror
                </div>
            </div>

            <button type="submit" class="btn-primary">Save Changes</button>
        </form>

        {{-- Sibling delete form (forms can't be nested), guarded by an Alpine confirm dialog. --}}
        <form
            action="{{ route('issues.destroy', $issue) }}"
            method="POST"
            x-data
            {{-- Cancelling the native confirm() dialog calls preventDefault(),
                 stopping the form from submitting, so nothing is deleted. --}}
            @submit="if (! confirm('Delete this issue? This cannot be undone.')) $event.preventDefault()"
            style="margin-top: 1rem;"
        >
            @csrf
            {{-- Routes this POST as a DELETE request, matching issues.destroy. --}}
            @method('DELETE')
            <button type="submit" class="btn-danger">Delete Issue</button>
        </form>
    </div>
@endsection
