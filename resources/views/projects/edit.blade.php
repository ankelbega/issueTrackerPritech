@extends('layouts.app')

@section('title', 'Edit Project')

@section('content')
    {{-- Back link to the project's own show page --}}
    <a href="{{ route('projects.show', $project) }}" style="color: var(--color-muted); font-size: 0.875rem; display: inline-block; margin-bottom: 1rem;">
        &larr; Back to Project
    </a>

    <div class="card">
        <form action="{{ route('projects.update', $project) }}" method="POST">
            @csrf
            @method('PUT')

            <div style="margin-bottom: 1.25rem;">
                <label for="name" class="form-label">Name</label>
                <input type="text" name="name" id="name" value="{{ old('name', $project->name) }}" class="form-input" required>
                @error('name')
                    <p class="form-error">{{ $message }}</p>
                @enderror
            </div>

            <div style="margin-bottom: 1.25rem;">
                <label for="description" class="form-label">Description</label>
                <textarea name="description" id="description" rows="4" class="form-input">{{ old('description', $project->description) }}</textarea>
                @error('description')
                    <p class="form-error">{{ $message }}</p>
                @enderror
            </div>

            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1.25rem; margin-bottom: 1.5rem;">
                <div>
                    <label for="start_date" class="form-label">Start Date</label>
                    <input type="date" name="start_date" id="start_date" value="{{ old('start_date', $project->start_date?->format('Y-m-d')) }}" class="form-input">
                    @error('start_date')
                        <p class="form-error">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label for="deadline" class="form-label">Deadline</label>
                    <input type="date" name="deadline" id="deadline" value="{{ old('deadline', $project->deadline?->format('Y-m-d')) }}" class="form-input">
                    @error('deadline')
                        <p class="form-error">{{ $message }}</p>
                    @enderror
                </div>
            </div>

            <button type="submit" class="btn-primary">Save Changes</button>
        </form>

        {{-- Delete form, guarded by an Alpine-powered confirm dialog before submitting.
             Kept as a sibling of the update form since forms cannot be nested in HTML. --}}
        <form
            action="{{ route('projects.destroy', $project) }}"
            method="POST"
            x-data
            @submit="if (! confirm('Delete this project? This cannot be undone.')) $event.preventDefault()"
            style="margin-top: 1rem;"
        >
            @csrf
            @method('DELETE')
            <button type="submit" class="btn-danger">Delete Project</button>
        </form>
    </div>
@endsection
