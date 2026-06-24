<?php

namespace App\Providers;

use App\Models\Project;
use App\Policies\ProjectPolicy;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\ServiceProvider;

/**
 * The application's main service provider. Used here specifically to
 * register the ProjectPolicy, since Laravel 11 no longer ships a dedicated
 * AuthServiceProvider with a $policies mapping array by default.
 */
class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     *
     * Empty — this app doesn't need to bind anything into the service
     * container beyond what Laravel registers automatically.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     *
     * Runs once, after all providers have been registered.
     */
    public function boot(): void
    {
        // Laravel 11 dropped the default AuthServiceProvider; Gate::policy() here
        // is the idiomatic replacement for the old $policies array. This tells
        // Laravel that whenever code calls $this->authorize('update', $project)
        // or auth()->user()->can('delete', $project), it should look in
        // ProjectPolicy for the answer.
        Gate::policy(Project::class, ProjectPolicy::class);
    }
}
