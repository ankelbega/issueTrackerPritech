<?php

use App\Http\Controllers\CommentController;
use App\Http\Controllers\IssueController;
use App\Http\Controllers\IssueTagController;
use App\Http\Controllers\IssueUserController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\ProjectController;
use App\Http\Controllers\TagController;
use Illuminate\Support\Facades\Route;

// Root redirects straight to the projects index, the app's main landing page.
// A plain Route::redirect avoids needing a controller/view just to bounce
// visitors to the real homepage; it issues a 302 to /projects.
Route::redirect('/', '/projects');

// Breeze's default authenticated landing page. Kept even though this app's
// real "home" is the projects index, since it's still wired into the
// Breeze login flow and tests. 'auth' requires a logged-in session;
// 'verified' additionally requires the user's email to be confirmed.
Route::get('/dashboard', function () {
    return view('dashboard');
})->middleware(['auth', 'verified'])->name('dashboard');

// Every issue-tracker route below requires an authenticated session. Without
// this group they were all publicly reachable, including destructive actions
// like deleting a project or an issue.
Route::middleware('auth')->group(function () {
    // Breeze's profile management routes (edit name/email, change password,
    // delete account). GET shows the form, PATCH saves changes, DELETE
    // removes the account — standard REST-ish verb usage for a single resource.
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');

    // Full resource routes for projects: index/create/store/show/edit/update/destroy,
    // all named under the 'projects.' prefix (e.g. projects.index, projects.show).
    // Route::resource() generates all seven RESTful routes in one line:
    //   GET    /projects             projects.index    -> ProjectController@index
    //   GET    /projects/create      projects.create   -> ProjectController@create
    //   POST   /projects             projects.store     -> ProjectController@store
    //   GET    /projects/{project}   projects.show      -> ProjectController@show
    //   GET    /projects/{project}/edit projects.edit   -> ProjectController@edit
    //   PUT/PATCH /projects/{project}   projects.update -> ProjectController@update
    //   DELETE /projects/{project}   projects.destroy   -> ProjectController@destroy
    Route::resource('projects', ProjectController::class);

    // Full resource routes for issues, named under the 'issues.' prefix.
    // Same seven routes as above, generated for IssueController instead
    // (issues.index, issues.create, issues.store, issues.show, issues.edit,
    // issues.update, issues.destroy).
    Route::resource('issues', IssueController::class);

    // Partial resource for tags: only listing and creating are supported —
    // there's no UI for editing or deleting an individual tag, so a full
    // Route::resource() would generate unused routes.
    Route::get('/tags', [TagController::class, 'index'])->name('tags.index'); // Show the tags list + create form.
    Route::post('/tags', [TagController::class, 'store'])->name('tags.store'); // Create a new tag.

    // Comments are always created in the context of an issue, hence the
    // nested /issues/{issue}/comments path rather than a top-level /comments
    // resource. Only a "store" action exists — comments aren't edited/deleted
    // through the UI in this app.
    Route::post('/issues/{issue}/comments', [CommentController::class, 'store'])->name('comments.store');

    // Attaching/detaching a tag on an issue (issue_tag pivot management).
    // POST attaches the given tag to the issue; DELETE removes it. Both are
    // called via AJAX from the "Manage Tags" checkbox panel on the issue
    // show page, so the page never has to fully reload.
    Route::post('/issues/{issue}/tags/{tag}', [IssueTagController::class, 'attach'])->name('issues.tags.attach');
    Route::delete('/issues/{issue}/tags/{tag}', [IssueTagController::class, 'detach'])->name('issues.tags.detach');

    // Assigning/unassigning a user on an issue (issue_user pivot management).
    // Same AJAX pattern as the tag routes above, but for the "Assigned Users"
    // checkbox panel.
    Route::post('/issues/{issue}/users/{user}', [IssueUserController::class, 'attach'])->name('issues.users.attach');
    Route::delete('/issues/{issue}/users/{user}', [IssueUserController::class, 'detach'])->name('issues.users.detach');
});

// Pulls in Breeze's pre-built authentication routes: login, register,
// logout, password reset, and email verification. Kept separate from this
// file so Breeze's scaffolding stays untouched and easy to compare against
// future Breeze updates.
require __DIR__.'/auth.php';
