<?php

namespace App\Policies;

use App\Models\Project;
use App\Models\User;

/**
 * Authorization rules for the Project model. Only update() and delete()
 * are actually used by the app right now (called from ProjectController
 * via $this->authorize(...)), enforcing that only a project's owner can
 * edit or delete it. The other methods are part of Laravel's standard
 * policy template and are left as explicit "deny" stubs since this app
 * doesn't currently restrict viewing or creating projects.
 */
class ProjectPolicy
{
    /**
     * Determine whether the user can view any models (i.e. a project listing).
     *
     * Not used by the app — project listing isn't restricted — kept as the
     * default generated stub.
     */
    public function viewAny(User $user): bool
    {
        return false;
    }

    /**
     * Determine whether the user can view an individual project.
     *
     * Not used by the app — viewing a single project isn't restricted to
     * its owner — kept as the default generated stub.
     */
    public function view(User $user, Project $project): bool
    {
        return false;
    }

    /**
     * Determine whether the user can create projects.
     *
     * Not used by the app — any authenticated user can create a project —
     * kept as the default generated stub.
     */
    public function create(User $user): bool
    {
        return false;
    }

    /**
     * Determine whether the user can update the given project.
     *
     * Only the project's owner can update it. This is checked in
     * ProjectController::edit() and ::update() via
     * $this->authorize('update', $project), which throws a 403 response
     * automatically if this returns false.
     */
    public function update(User $user, Project $project): bool
    {
        return $user->id === $project->user_id;
    }

    /**
     * Determine whether the user can delete the given project.
     *
     * Only the project's owner can delete it. Checked in
     * ProjectController::destroy() via $this->authorize('delete', $project).
     */
    public function delete(User $user, Project $project): bool
    {
        return $user->id === $project->user_id;
    }

    /**
     * Determine whether the user can restore a soft-deleted project.
     *
     * Not applicable — the Project model doesn't use soft deletes — kept
     * as the default generated stub.
     */
    public function restore(User $user, Project $project): bool
    {
        return false;
    }

    /**
     * Determine whether the user can permanently delete a project.
     *
     * Not applicable — the Project model doesn't use soft deletes, so
     * there's no separate "permanent" delete step — kept as the default
     * generated stub.
     */
    public function forceDelete(User $user, Project $project): bool
    {
        return false;
    }
}
