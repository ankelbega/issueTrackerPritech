@extends('layouts.app')

@section('title', 'New Issue')

@section('content')
    <a href="{{ route('issues.index') }}" style="color: var(--color-muted); font-size: 0.875rem; display: inline-block; margin-bottom: 1rem;">
        &larr; Back to Issues
    </a>

    <div class="card">
        <form action="{{ route('issues.store') }}" method="POST">
            @csrf

            <div style="margin-bottom: 1.25rem;">
                <label for="project_id" class="form-label">Project</label>
                <select name="project_id" id="project_id" class="form-input" required>
                    <option value="">Select a project&hellip;</option>
                    @foreach ($projects as $project)
                        {{-- Pre-select the project passed via ?project_id=, falling back to old() on validation failure --}}
                        <option value="{{ $project->id }}" @selected((string) old('project_id', $selectedProjectId) === (string) $project->id)>
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
                <input type="text" name="title" id="title" value="{{ old('title') }}" class="form-input" required>
                @error('title')
                    <p class="form-error">{{ $message }}</p>
                @enderror
            </div>

            <div style="margin-bottom: 1.25rem;">
                <label for="description" class="form-label">Description</label>
                <textarea name="description" id="description" rows="4" class="form-input">{{ old('description') }}</textarea>
                @error('description')
                    <p class="form-error">{{ $message }}</p>
                @enderror
            </div>

            <div style="display: grid; grid-template-columns: 1fr 1fr 1fr; gap: 1.25rem; margin-bottom: 1.5rem;">
                <div>
                    <label for="status" class="form-label">Status</label>
                    <select name="status" id="status" class="form-input" required>
                        <option value="open" @selected(old('status', 'open') === 'open')>Open</option>
                        <option value="in_progress" @selected(old('status') === 'in_progress')>In Progress</option>
                        <option value="closed" @selected(old('status') === 'closed')>Closed</option>
                    </select>
                    @error('status')
                        <p class="form-error">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label for="priority" class="form-label">Priority</label>
                    <select name="priority" id="priority" class="form-input" required>
                        <option value="low" @selected(old('priority') === 'low')>Low</option>
                        <option value="medium" @selected(old('priority', 'medium') === 'medium')>Medium</option>
                        <option value="high" @selected(old('priority') === 'high')>High</option>
                    </select>
                    @error('priority')
                        <p class="form-error">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label for="due_date" class="form-label">Due Date</label>
                    <input type="date" name="due_date" id="due_date" value="{{ old('due_date') }}" class="form-input">
                    @error('due_date')
                        <p class="form-error">{{ $message }}</p>
                    @enderror
                </div>
            </div>

            <button type="submit" class="btn-primary">Create Issue</button>
        </form>
    </div>
@endsection
