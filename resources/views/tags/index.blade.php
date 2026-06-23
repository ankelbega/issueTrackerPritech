@extends('layouts.app')

@section('title', 'Tags')

@section('content')
    <div class="page-header">
        <h1>Tags</h1>
    </div>

    {{-- Two-column layout: tags list on the left, create form on the right --}}
    <div style="display: grid; grid-template-columns: 1fr 360px; gap: 1.5rem; align-items: start;">
        {{-- Existing tags --}}
        <div class="card">
            @if ($tags->isEmpty())
                {{-- Empty state shown when no tags exist yet --}}
                <p style="color: var(--color-muted); text-align: center; padding: 2rem 0;">
                    No tags yet. Create your first one using the form.
                </p>
            @else
                <div style="display: flex; flex-direction: column; gap: 0.75rem;">
                    @foreach ($tags as $tag)
                        <div style="display: flex; align-items: center; justify-content: space-between;">
                            {{-- Colored pill: background = tag's own color, white text --}}
                            <span
                                style="display: inline-block; padding: 0.125rem 0.625rem; border-radius: 9999px; font-size: 0.75rem; font-weight: 500; color: #ffffff; background-color: {{ $tag->color ?? '#6b7280' }};"
                            >
                                {{ $tag->name }}
                            </span>

                            {{-- Issue count via withCount('issues') in TagController@index --}}
                            <span style="color: var(--color-muted); font-size: 0.8125rem;">
                                {{ $tag->issues_count }} {{ Str::plural('issue', $tag->issues_count) }}
                            </span>
                        </div>
                    @endforeach
                </div>
            @endif
        </div>

        {{-- Create new tag form --}}
        <div class="card">
            <h2 style="font-size: 1.0625rem; margin-bottom: 1rem;">Create New Tag</h2>

            <form action="{{ route('tags.store') }}" method="POST">
                @csrf

                <div style="margin-bottom: 1.25rem;">
                    <label for="name" class="form-label">Name</label>
                    <input type="text" name="name" id="name" value="{{ old('name') }}" class="form-input" required>
                    @error('name')
                        <p class="form-error">{{ $message }}</p>
                    @enderror
                </div>

                <div style="margin-bottom: 1.5rem;">
                    <label for="color" class="form-label">Color</label>
                    {{-- Native color picker input, defaults to the accent color --}}
                    <input type="color" name="color" id="color" value="{{ old('color', '#4F6EF7') }}" class="form-input" style="height: 2.5rem; padding: 0.25rem;">
                    @error('color')
                        <p class="form-error">{{ $message }}</p>
                    @enderror
                </div>

                <button type="submit" class="btn-primary">Add Tag</button>
            </form>
        </div>
    </div>
@endsection
