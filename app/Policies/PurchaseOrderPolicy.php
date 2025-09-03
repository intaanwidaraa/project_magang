<?php

namespace App\Policies;

use App\Models\PurchaseOrder;
use App\Models\User;
use Illuminate\Auth\Access\Response;

class PurchaseOrderPolicy
{
    /**
     * Determine whether the user can view any models.
     */
    public function viewAny(User $user): bool
    {
        // Admin dan Staf bisa melihat daftar PO
        return $user->hasRole(['Administrator', 'Staf Gudang']);
    }

    /**
     * Determine whether the user can view the model.
     */
    public function view(User $user, PurchaseOrder $purchaseOrder): bool
    {
        // Admin dan Staf bisa melihat detail PO
        return $user->hasRole(['Administrator', 'Staf Gudang']);
    }

    /**
     * Determine whether the user can create models.
     */
    public function create(User $user): bool
    {
        // HANYA Admin yang boleh membuat PO baru
        return $user->hasRole('Administrator');
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(User $user, PurchaseOrder $purchaseOrder): bool
    {
        // Admin dan Staf bisa update (Staf butuh ini untuk tombol aksi)
        return $user->hasRole(['Administrator', 'Staf Gudang']);
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, PurchaseOrder $purchaseOrder): bool
    {
        // HANYA Admin yang boleh menghapus PO
        return $user->hasRole('Administrator');
    }

    /**
     * Determine whether the user can restore the model.
     */
    public function restore(User $user, PurchaseOrder $purchaseOrder): bool
    {
        return $user->hasRole('Administrator');
    }

    /**
     * Determine whether the user can permanently delete the model.
     */
    public function forceDelete(User $user, PurchaseOrder $purchaseOrder): bool
    {
        return $user->hasRole('Administrator');
    }
}
