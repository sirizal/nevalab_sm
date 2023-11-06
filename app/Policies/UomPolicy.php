<?php

namespace App\Policies;

use Illuminate\Auth\Access\Response;
use App\Models\Uom;
use App\Models\User;

class UomPolicy
{
    /**
     * Determine whether the user can view any models.
     */
    public function viewAny(User $user): bool
    {
        return $user->can('view uoms');
    }

    /**
     * Determine whether the user can view the model.
     */
    public function view(User $user, Uom $uom): bool
    {
        return $user->can('view uoms');
    }

    /**
     * Determine whether the user can create models.
     */
    public function create(User $user): bool
    {
        return $user->can('create uoms');
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(User $user, Uom $uom): bool
    {
        return $user->can('edit uoms');
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, Uom $uom): bool
    {
        return $user->can('delete uoms');
    }

    /**
     * Determine whether the user can restore the model.
     */
    public function restore(User $user, Uom $uom): bool
    {
        return $user->can('delete uoms');
    }

    /**
     * Determine whether the user can permanently delete the model.
     */
    public function forceDelete(User $user, Uom $uom): bool
    {
        return $user->can('delete uoms');
    }
}
