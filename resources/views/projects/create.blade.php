@extends('layouts.app')

@section('title', 'New Project')

@section('content')
    {{-- Back link to the projects list --}}
    <a href="{{ route('projects.index') }}" style="color: var(--color-muted); font-size: 0.875rem; display: inline-block; margin-bottom: 1rem;">
        &larr; Back to Projects
    </a>

    <div class="card">
        {{-- Submits to ProjectController::store(), validated by StoreProjectRequest. --}}
        <form action="{{ route('projects.store') }}" method="POST">
            {{-- Required on every POST/PUT/PATCH/DELETE form for CSRF protection. --}}
            @csrf

            <div style="margin-bottom: 1.25rem;">
                <label for="name" class="form-label">Name</label>
                {{-- old('name') re-fills this field with whatever the user previously
                     typed, if validation failed and they were redirected back here. --}}
                <input type="text" name="name" id="name" value="{{ old('name') }}" class="form-input" required>
                {{-- @error renders its contents only if the 'name' field failed
                     validation; $message is the specific error text for this field. --}}
                @error('name')
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

            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1.25rem; margin-bottom: 1.5rem;">
                <div>
                    <label for="start_date" class="form-label">Start Date</label>
                    <input type="date" name="start_date" id="start_date" value="{{ old('start_date') }}" class="form-input">
                    @error('start_date')
                        <p class="form-error">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label for="deadline" class="form-label">Deadline</label>
                    <input type="date" name="deadline" id="deadline" value="{{ old('deadline') }}" class="form-input">
                    {{-- Shows the "deadline must be on/after start_date" message here
                         if that StoreProjectRequest rule (after_or_equal:start_date) fails. --}}
                    @error('deadline')
                        <p class="form-error">{{ $message }}</p>
                    @enderror
                </div>
            </div>

            <button type="submit" class="btn-primary">Create Project</button>
        </form>
    </div>
@endsection
