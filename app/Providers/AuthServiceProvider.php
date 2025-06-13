<?php
use Illuminate\Support\Facades\Gate;

Gate::define('eliminarPeorClasificado', function ($user) {
    return $user->role === 'admin';
});
