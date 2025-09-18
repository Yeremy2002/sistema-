<?php

namespace App\Policies;

use App\Models\Caja;
use App\Models\User;
use Illuminate\Auth\Access\Response;

class CajaPolicy
{
    /**
     * Determine whether the user can view any models.
     */
    public function viewAny(User $user): bool
    {
        return $user->hasPermissionTo('ver cajas');
    }

    /**
     * Determine whether the user can view the model.
     */
    public function view(User $user, Caja $caja): bool
    {
        // El usuario puede ver la caja si es suya o si tiene permiso para ver cajas
        return $user->id === $caja->user_id || $user->hasPermissionTo('ver cajas');
    }

    /**
     * Determine whether the user can create models.
     */
    public function create(User $user): bool
    {
        return $user->hasPermissionTo('abrir caja');
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(User $user, Caja $caja): bool
    {
        // El usuario puede actualizar (cerrar) la caja si es suya y está abierta
        // O si es Administrador con permiso de cerrar cajas (sin restricciones)
        return ($user->id === $caja->user_id && $caja->estado) || 
               $user->hasPermissionTo('cerrar caja') ||
               $user->hasRole('Administrador');
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, Caja $caja): bool
    {
        // No permitimos eliminar cajas, solo cerrarlas
        return false;
    }

    /**
     * Determine whether the user can restore the model.
     */
    public function restore(User $user, Caja $caja): bool
    {
        return false;
    }

    /**
     * Determine whether the user can permanently delete the model.
     */
    public function forceDelete(User $user, Caja $caja): bool
    {
        return false;
    }

    /**
     * Determine whether the user can create movements for the model.
     */
    public function createMovimiento(User $user, Caja $caja): bool
    {
        // El usuario puede registrar movimientos si es su caja o si tiene permiso específico
        return ($user->id === $caja->user_id && $caja->estado) || $user->hasPermissionTo('registrar movimiento');
    }

    /**
     * Determine whether the user can perform arqueo on the model.
     */
    public function realizarArqueo(User $user, Caja $caja): bool
    {
        // El usuario puede realizar arqueo si es su caja o si tiene permiso específico
        return ($user->id === $caja->user_id && $caja->estado) || $user->hasPermissionTo('realizar arqueo');
    }
}
