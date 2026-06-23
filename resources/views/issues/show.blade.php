@extends('layouts.app')

@section('title', $issue->title)

@php
    // Pre-shape the first page of comments into plain arrays so Alpine can manage
    // them client-side (append on "Load more", prepend on new comment) without
    // ever needing to re-render the page.
    $initialComments = $comments->getCollection()->map(fn ($comment) => [
        'id' => $comment->id,
        'author_name' => $comment->author_name,
        'body' => $comment->body,
        'created_at' => $comment->created_at->format('M d, Y'),
    ]);

    // URL templates built from the named routes (not hand-typed strings), with a
    // placeholder swapped out client-side for the actual tag/user id being toggled.
    $tagActionUrlTemplate = route('issues.tags.attach', ['issue' => $issue->id, 'tag' => '__ID__']);
    $userActionUrlTemplate = route('issues.users.attach', ['issue' => $issue->id, 'user' => '__ID__']);
@endphp

@section('content')
    {{-- Issue title, parent project link, and the edit action --}}
    <div class="page-header">
        <div>
            <h1>{{ $issue->title }}</h1>
            <a href="{{ route('projects.show', $issue->project) }}" style="color: var(--color-muted); font-size: 0.875rem;">
                {{ $issue->project->name }}
            </a>
        </div>
        <a href="{{ route('issues.edit', $issue) }}" class="btn-secondary">Edit Issue</a>
    </div>

    {{-- Details card: status, priority, due date, description --}}
    <div class="card" style="margin-bottom: 1.5rem;">
        <div style="display: flex; gap: 0.75rem; margin-bottom: 1rem;">
            <span class="badge-status-{{ $issue->status }}">{{ str($issue->status)->headline() }}</span>
            <span class="badge-priority-{{ $issue->priority }}">{{ str($issue->priority)->headline() }}</span>
            <span style="color: var(--color-muted); font-size: 0.875rem;">
                Due: {{ $issue->due_date ? $issue->due_date->format('M d, Y') : '—' }}
            </span>
        </div>
        <p style="color: var(--color-muted);">{{ $issue->description ?: 'No description provided.' }}</p>
    </div>

    {{-- Tags + assigned users + comments all share one Alpine scope so toggling a
         tag/user or posting a comment never requires a full page reload. --}}
    <div
        x-data="{
            allTags: @json($tags->map(fn ($tag) => ['id' => $tag->id, 'name' => $tag->name, 'color' => $tag->color])),
            attachedTags: @json($issue->tags->map(fn ($tag) => ['id' => $tag->id, 'name' => $tag->name, 'color' => $tag->color])),
            showTagPanel: false,
            tagError: '',

            allUsers: @json($users->map(fn ($user) => ['id' => $user->id, 'name' => $user->name])),
            assignedUsers: @json($issue->users->map(fn ($user) => ['id' => $user->id, 'name' => $user->name])),
            showUserPanel: false,
            userError: '',

            comments: @json($initialComments),
            nextPageUrl: @json($comments->nextPageUrl()),
            loadingMore: false,
            commentsLoadError: '',

            newComment: { author_name: '', body: '' },
            commentErrors: {},
            commentSubmitError: '',
            commentSuccess: false,
            submittingComment: false,

            csrfToken() {
                return document.querySelector('meta[name=csrf-token]').content;
            },

            isTagAttached(tagId) {
                return this.attachedTags.some(t => t.id === tagId);
            },

            // Attach/detach a tag via AJAX against issues.tags.attach / issues.tags.detach.
            // The UI only updates once the server confirms success; on failure (network
            // error or non-2xx response) the checkbox state is left untouched and an
            // inline error message is shown instead of silently pretending it worked.
            toggleTag(tag) {
                const attached = this.isTagAttached(tag.id);
                this.tagError = '';

                fetch('{{ $tagActionUrlTemplate }}'.replace('__ID__', tag.id), {
                    method: attached ? 'DELETE' : 'POST',
                    headers: { 'X-CSRF-TOKEN': this.csrfToken(), 'Accept': 'application/json' },
                }).then(response => {
                    if (! response.ok) throw new Error('Request failed');

                    this.attachedTags = attached
                        ? this.attachedTags.filter(t => t.id !== tag.id)
                        : [...this.attachedTags, tag];
                }).catch(() => {
                    this.tagError = `Couldn't update tags. Please try again.`;
                });
            },

            isUserAssigned(userId) {
                return this.assignedUsers.some(u => u.id === userId);
            },

            // Assign/unassign a user via AJAX against issues.users.attach / issues.users.detach.
            // Same fail-safe pattern as toggleTag(): no optimistic update without a 2xx response.
            toggleUser(user) {
                const assigned = this.isUserAssigned(user.id);
                this.userError = '';

                fetch('{{ $userActionUrlTemplate }}'.replace('__ID__', user.id), {
                    method: assigned ? 'DELETE' : 'POST',
                    headers: { 'X-CSRF-TOKEN': this.csrfToken(), 'Accept': 'application/json' },
                }).then(response => {
                    if (! response.ok) throw new Error('Request failed');

                    this.assignedUsers = assigned
                        ? this.assignedUsers.filter(u => u.id !== user.id)
                        : [...this.assignedUsers, user];
                }).catch(() => {
                    this.userError = `Couldn't update assignees. Please try again.`;
                });
            },

            initials(name) {
                return name.split(' ').map(part => part[0]).join('').toUpperCase().slice(0, 2);
            },

            // Fetches the next page of comments as JSON (same route, Accept: application/json)
            // and appends them below the existing list.
            loadMoreComments() {
                if (! this.nextPageUrl) return;

                this.loadingMore = true;
                this.commentsLoadError = '';

                fetch(this.nextPageUrl, { headers: { 'Accept': 'application/json' } })
                    .then(response => {
                        if (! response.ok) throw new Error('Request failed');
                        return response.json();
                    })
                    .then(json => {
                        this.comments.push(...json.data);
                        this.nextPageUrl = json.next_page_url;
                    })
                    .catch(() => {
                        this.commentsLoadError = `Couldn't load more comments. Please try again.`;
                    })
                    .finally(() => {
                        this.loadingMore = false;
                    });
            },

            // Submits the new comment via AJAX, prepends it on success, surfaces
            // validation errors inline (no alert box), and shows a distinct message
            // for network/server failures so a failed post is never silently dropped.
            submitComment() {
                this.submittingComment = true;
                this.commentErrors = {};
                this.commentSubmitError = '';

                fetch('{{ route('comments.store', $issue) }}', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': this.csrfToken(),
                        'Accept': 'application/json',
                    },
                    body: JSON.stringify(this.newComment),
                }).then(async (response) => {
                    if (response.status === 422) {
                        const json = await response.json();
                        this.commentErrors = json.errors;
                        return;
                    }

                    if (! response.ok) throw new Error('Request failed');

                    const comment = await response.json();
                    this.comments.unshift(comment);
                    this.newComment = { author_name: '', body: '' };
                    this.commentSuccess = true;
                    setTimeout(() => this.commentSuccess = false, 3000);
                }).catch(() => {
                    this.commentSubmitError = `Couldn't post your comment. Please try again.`;
                }).finally(() => {
                    this.submittingComment = false;
                });
            },
        }"
    >
        {{-- Tags section --}}
        <div class="card" style="margin-bottom: 1.5rem; position: relative;">
            <div class="page-header" style="margin-bottom: 1rem;">
                <h2 style="font-size: 1.0625rem;">Tags</h2>
                <button type="button" class="btn-secondary" @click="showTagPanel = ! showTagPanel">Manage Tags</button>
            </div>

            {{-- Attached tags rendered as colored pills using each tag's own color --}}
            <div style="display: flex; flex-wrap: wrap; gap: 0.5rem;">
                <template x-for="tag in attachedTags" :key="tag.id">
                    <span
                        style="display: inline-block; padding: 0.125rem 0.625rem; border-radius: 9999px; font-size: 0.75rem; font-weight: 500; color: #ffffff;"
                        :style="{ backgroundColor: tag.color || '#6b7280' }"
                        x-text="tag.name"
                    ></span>
                </template>
                <span x-show="attachedTags.length === 0" style="color: var(--color-muted); font-size: 0.875rem;">No tags attached.</span>
            </div>

            {{-- Visible failure feedback for a failed attach/detach (network or server error) --}}
            <p class="form-error" x-show="tagError" x-text="tagError" x-cloak></p>

            {{-- Dropdown panel: checkbox list of every tag, checked if already attached --}}
            <div
                x-show="showTagPanel"
                @click.outside="showTagPanel = false"
                x-cloak
                class="card"
                style="position: absolute; top: 4rem; right: 1.5rem; z-index: 10; width: 220px; box-shadow: 0 4px 12px rgba(0,0,0,0.12);"
            >
                <template x-for="tag in allTags" :key="tag.id">
                    <label style="display: flex; align-items: center; gap: 0.5rem; padding: 0.375rem 0; font-size: 0.875rem;">
                        <input type="checkbox" :checked="isTagAttached(tag.id)" @change="toggleTag(tag)">
                        <span x-text="tag.name"></span>
                    </label>
                </template>
            </div>
        </div>

        {{-- Assigned users section --}}
        <div class="card" style="margin-bottom: 1.5rem; position: relative;">
            <div class="page-header" style="margin-bottom: 1rem;">
                <h2 style="font-size: 1.0625rem;">Assigned Users</h2>
                <button type="button" class="btn-secondary" @click="showUserPanel = ! showUserPanel">+</button>
            </div>

            {{-- Assigned users shown as small initials avatars --}}
            <div style="display: flex; flex-wrap: wrap; gap: 0.5rem;">
                <template x-for="user in assignedUsers" :key="user.id">
                    <span
                        :title="user.name"
                        style="display: inline-flex; align-items: center; justify-content: center; width: 2rem; height: 2rem; border-radius: 9999px; background-color: var(--color-accent); color: #ffffff; font-size: 0.75rem; font-weight: 600;"
                        x-text="initials(user.name)"
                    ></span>
                </template>
                <span x-show="assignedUsers.length === 0" style="color: var(--color-muted); font-size: 0.875rem;">No users assigned.</span>
            </div>

            {{-- Visible failure feedback for a failed assign/unassign (network or server error) --}}
            <p class="form-error" x-show="userError" x-text="userError" x-cloak></p>

            {{-- Dropdown panel: checkbox list of every user, checked if already assigned --}}
            <div
                x-show="showUserPanel"
                @click.outside="showUserPanel = false"
                x-cloak
                class="card"
                style="position: absolute; top: 4rem; right: 1.5rem; z-index: 10; width: 220px; box-shadow: 0 4px 12px rgba(0,0,0,0.12);"
            >
                <template x-for="user in allUsers" :key="user.id">
                    <label style="display: flex; align-items: center; gap: 0.5rem; padding: 0.375rem 0; font-size: 0.875rem;">
                        <input type="checkbox" :checked="isUserAssigned(user.id)" @change="toggleUser(user)">
                        <span x-text="user.name"></span>
                    </label>
                </template>
            </div>
        </div>

        {{-- Comments section --}}
        <div class="card">
            <h2 style="font-size: 1.0625rem; margin-bottom: 1rem;">Comments</h2>

            <div style="display: flex; flex-direction: column; gap: 1rem; margin-bottom: 1rem;">
                <template x-for="comment in comments" :key="comment.id">
                    <div style="border-bottom: 1px solid var(--color-border); padding-bottom: 0.75rem;">
                        <div style="display: flex; justify-content: space-between; font-size: 0.8125rem; color: var(--color-muted); margin-bottom: 0.25rem;">
                            <span style="font-weight: 500;" x-text="comment.author_name"></span>
                            <span x-text="comment.created_at"></span>
                        </div>
                        <p style="margin: 0;" x-text="comment.body"></p>
                    </div>
                </template>
                <p x-show="comments.length === 0" style="color: var(--color-muted); font-size: 0.875rem;">No comments yet.</p>
            </div>

            {{-- "Load more" only shows up while another page of comments exists --}}
            <button
                type="button"
                class="btn-secondary"
                x-show="nextPageUrl"
                x-cloak
                @click="loadMoreComments()"
                :disabled="loadingMore"
                style="margin-bottom: 1.5rem;"
            >
                <span x-show="! loadingMore">Load more</span>
                <span x-show="loadingMore">Loading&hellip;</span>
            </button>

            {{-- Visible failure feedback if fetching the next page fails --}}
            <p class="form-error" x-show="commentsLoadError" x-text="commentsLoadError" x-cloak style="margin-top: -1rem; margin-bottom: 1rem;"></p>

            {{-- Add comment form, submitted via AJAX so the page never reloads --}}
            <form @submit.prevent="submitComment()" style="border-top: 1px solid var(--color-border); padding-top: 1.25rem;">
                <div style="margin-bottom: 1rem;">
                    <label for="author_name" class="form-label">Name</label>
                    <input type="text" id="author_name" x-model="newComment.author_name" class="form-input">
                    <p class="form-error" x-show="commentErrors.author_name" x-text="commentErrors.author_name?.[0]"></p>
                </div>

                <div style="margin-bottom: 1rem;">
                    <label for="body" class="form-label">Comment</label>
                    <textarea id="body" x-model="newComment.body" rows="3" class="form-input"></textarea>
                    <p class="form-error" x-show="commentErrors.body" x-text="commentErrors.body?.[0]"></p>
                </div>

                <div style="display: flex; align-items: center; gap: 1rem;">
                    <button type="submit" class="btn-primary" :disabled="submittingComment">
                        <span x-show="! submittingComment">Add Comment</span>
                        <span x-show="submittingComment">Posting&hellip;</span>
                    </button>
                    <span x-show="commentSuccess" x-cloak style="color: #16a34a; font-size: 0.875rem;">Comment added.</span>
                    <span x-show="commentSubmitError" x-cloak style="color: #dc2626; font-size: 0.875rem;" x-text="commentSubmitError"></span>
                </div>
            </form>
        </div>
    </div>
@endsection
