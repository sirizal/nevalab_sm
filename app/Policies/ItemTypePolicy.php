<?php

namespace App\Policies;

use Illuminate\Auth\Access\Response;
use App\Models\ItemType;
use App\Models\User;

class ItemTypePolicy
{
    /**
     * Determine whether the user can view any models.
     */
    public function viewAny(User $user): bool
    {
        return $user->can('view item types');
    }

    /**
     * Determine whether the user can view the model.
     */
    public function view(User $user, ItemType $itemType): bool
    {
        return $user->can('view item types');
    }

    /**
     * Determine whether the user can create models.
     */
    public function create(User $user): bool
    {
        return $user->can('create item types');
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(User $user, ItemType $itemType): bool
    {
        return $user->can('edit item types');
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, ItemType $itemType): bool
    {
        return $user->can('delete item types');
    }

    /**
     * Determine whether the user can restore the model.
     */
    public function restore(User $user, ItemType $itemType): bool
    {
        return $user->can('delete item types');
    }

    /**
     * Determine whether the user can permanently delete the model.
     */
    public function forceDelete(User $user, ItemType $itemType): bool
    {
        return $user->can('delete item types');
    }
}
