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
Route::redirect('/', '/projects');

Route::get('/dashboard', function () {
    return view('dashboard');
})->middleware(['auth', 'verified'])->name('dashboard');

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

// Full resource routes for projects: index/create/store/show/edit/update/destroy,
// all named under the 'projects.' prefix (e.g. projects.index, projects.show).
Route::resource('projects', ProjectController::class);

// Full resource routes for issues, named under the 'issues.' prefix.
Route::resource('issues', IssueController::class);

// Partial resource for tags: only listing and creating are supported.
Route::get('/tags', [TagController::class, 'index'])->name('tags.index');
Route::post('/tags', [TagController::class, 'store'])->name('tags.store');

// Comments are always created in the context of an issue.
Route::post('/issues/{issue}/comments', [CommentController::class, 'store'])->name('comments.store');

// Attaching/detaching a tag on an issue (issue_tag pivot management).
Route::post('/issues/{issue}/tags/{tag}', [IssueTagController::class, 'attach'])->name('issues.tags.attach');
Route::delete('/issues/{issue}/tags/{tag}', [IssueTagController::class, 'detach'])->name('issues.tags.detach');

// Assigning/unassigning a user on an issue (issue_user pivot management).
Route::post('/issues/{issue}/users/{user}', [IssueUserController::class, 'attach'])->name('issues.users.attach');
Route::delete('/issues/{issue}/users/{user}', [IssueUserController::class, 'detach'])->name('issues.users.detach');

require __DIR__.'/auth.php';
