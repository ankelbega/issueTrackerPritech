@extends('layouts.app')

@section('title', 'Issues')

@section('content')
    <div class="page-header">
        <h1>Issues</h1>
    </div>

    {{-- This single Alpine scope wraps the search box, the filter form, and the
         results container, since the debounced search needs to read the filter
         form's current values (so search combines with status/priority/tag_id)
         and needs a target element to swap the freshly fetched HTML into. --}}
    <div
        x-data="{
            // Holds the setTimeout() id for the pending debounced search, so a
            // new keystroke can cancel the previous timer before it fires.
            debounceTimer: null,

            // True while the AJAX search request is in flight; toggles the
            // small 'Searching...' indicator and is unused for anything else.
            loading: false,

            // Called on every keystroke in the search input via @input below.
            onSearchInput() {
                // Cancels any previously scheduled search. Without this, typing
                // 'bug' would fire three separate searches (for 'b', 'bu', 'bug')
                // instead of just one, 400ms after the last keystroke.
                clearTimeout(this.debounceTimer);

                // Schedules performSearch() to run 400ms from now. If the user
                // types again before it fires, the clearTimeout() above cancels
                // it and a fresh 400ms timer starts over.
                this.debounceTimer = setTimeout(() => this.performSearch(), 400);
            },

            // Builds the current filter/search query string and fetches just
            // the results table HTML, then swaps it into the page in place.
            performSearch() {
                this.loading = true;

                // FormData reads every field's current value out of the filter
                // form (status, priority, tag_id, search) in one step, so the
                // AJAX search always combines with whatever filters are active.
                const formData = new FormData(this.$refs.filterForm);

                // URLSearchParams turns those form fields into a query string
                // like 'status=open&search=bug', skipping empty fields cleanly.
                const params = new URLSearchParams(formData);

                // Hits the same issues.index route the filter dropdowns submit
                // to, but with the X-Requested-With header set explicitly
                // (fetch() never adds it on its own) so IssueController::index()
                // detects this as AJAX and returns only the results table
                // partial instead of the full HTML page.
                fetch('{{ route("issues.index") }}?' + params.toString(), {
                    headers: { 'X-Requested-With': 'XMLHttpRequest' },
                })
                    .then(response => {
                        // Treats any non-2xx response as a failure rather than
                        // trying to read and render an error page as if it were the table.
                        if (! response.ok) throw new Error('Request failed');

                        // The response body is plain HTML (the table partial),
                        // not JSON, so it's read as text.
                        return response.text();
                    })
                    .then(html => {
                        // Replaces the old results markup with the freshly
                        // fetched table/empty-state/pagination in one step.
                        this.$refs.resultsContainer.innerHTML = html;
                    })
                    .catch(() => {
                        // On failure, the previous results are simply left on
                        // screen rather than being cleared out, so a transient
                        // network error never leaves the user looking at a blank page.
                    })
                    .finally(() => {
                        // Always runs, whether the search succeeded or failed,
                        // so the loading indicator never gets stuck on.
                        this.loading = false;
                    });
            },
        }"
    >
        {{-- Filter bar: GET form so filters live in the URL and survive pagination
             on a normal (non-AJAX) page load. x-ref="filterForm" lets performSearch()
             above read this form's current field values via FormData. --}}
        <form method="GET" action="{{ route('issues.index') }}" x-ref="filterForm" class="card" style="display: flex; gap: 1rem; align-items: flex-end; margin-bottom: 1.5rem; padding: 1rem 1.5rem;">
            <div style="flex: 2;">
                <label for="search" class="form-label">Search</label>
                {{-- value="{{ request('search') }}" re-fills the box with the
                     active search term after a normal page load (e.g. following
                     a status/priority/tag change, which still does a full submit).
                     @input fires on every keystroke and triggers the debounce logic above. --}}
                <input
                    type="text"
                    name="search"
                    id="search"
                    class="form-input"
                    placeholder="Search issues by title or description..."
                    value="{{ request('search') }}"
                    @input="onSearchInput()"
                >
            </div>

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

            {{-- Reset clears all filters (including search) by just linking back to the bare index route. --}}
            <a href="{{ route('issues.index') }}" style="color: var(--color-muted); font-size: 0.875rem; padding-bottom: 0.5rem;">Reset</a>
        </form>

        {{-- Subtle loading indicator, shown only while performSearch()'s fetch
             is in flight. x-cloak prevents it from flashing visible before
             Alpine finishes initializing on page load. --}}
        <p x-show="loading" x-cloak style="color: var(--color-muted); font-size: 0.8125rem; margin: -0.5rem 0 1rem;">Searching&hellip;</p>

        {{-- x-ref="resultsContainer" is the element performSearch() replaces
             the contents of. It starts out holding the normal server-rendered
             results, using the exact same partial the AJAX endpoint returns,
             so the initial page load and every subsequent search look identical. --}}
        <div x-ref="resultsContainer">
            @include('issues.partials.table', ['issues' => $issues])
        </div>
    </div>
@endsection
