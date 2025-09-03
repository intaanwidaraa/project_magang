<?php

namespace App\Policies;

use App\Models\StockRequisition;
use App\Models\User;
use Illuminate\Auth\Access\Response;

class StockRequisitionPolicy
{
    /**
     * Determine whether the user can view any models.
     */
    public function viewAny(User $user): bool
    {
        // Keduanya bisa melihat daftar permintaan barang
        return $user->hasRole(['Administrator', 'Staf Gudang']);
    }

    /**
     * Determine whether the user can view the model.
     */
    public function view(User $user, StockRequisition $stockRequisition): bool
    {
        // Keduanya bisa melihat detail permintaan barang
        return $user->hasRole(['Administrator', 'Staf Gudang']);
    }

    /**
     * Determine whether the user can create models.
     */
    public function create(User $user): bool
    {
        // Keduanya bisa membuat permintaan barang baru
        return $user->hasRole(['Administrator', 'Staf Gudang']);
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(User $user, StockRequisition $stockRequisition): bool
    {
        // Keduanya bisa update (dibutuhkan untuk tombol aksi)
        return $user->hasRole(['Administrator', 'Staf Gudang']);
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, StockRequisition $stockRequisition): bool
    {
        // HANYA Admin yang boleh menghapus data
        return $user->hasRole('Administrator');
    }

    /**
     * Determine whether the user can restore the model.
     */
    public function restore(User $user, StockRequisition $stockRequisition): bool
    {
        return $user->hasRole('Administrator');
    }

    /**
     * Determine whether the user can permanently delete the model.
     */
    public function forceDelete(User $user, StockRequisition $stockRequisition): bool
    {
        return $user->hasRole('Administrator');
    }
}
