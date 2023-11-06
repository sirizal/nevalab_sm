<?php

namespace App\Policies;

use Illuminate\Auth\Access\Response;
use App\Models\StorageType;
use App\Models\User;

class StorageTypePolicy
{
    /**
     * Determine whether the user can view any models.
     */
    public function viewAny(User $user): bool
    {
        return $user->can('view storage types');
    }

    /**
     * Determine whether the user can view the model.
     */
    public function view(User $user, StorageType $storageType): bool
    {
        return $user->can('view storage types');
    }

    /**
     * Determine whether the user can create models.
     */
    public function create(User $user): bool
    {
        return $user->can('create storage types');
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(User $user, StorageType $storageType): bool
    {
        return $user->can('edit storage types');
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, StorageType $storageType): bool
    {
        return $user->can('delete storage types');
    }

    /**
     * Determine whether the user can restore the model.
     */
    public function restore(User $user, StorageType $storageType): bool
    {
        return $user->can('delete storage types');
    }

    /**
     * Determine whether the user can permanently delete the model.
     */
    public function forceDelete(User $user, StorageType $storageType): bool
    {
        return $user->can('delete storage types');
    }
}
