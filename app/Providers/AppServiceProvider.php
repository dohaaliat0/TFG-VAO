<?php

namespace App\Providers;

use App\Models\Horario;
use App\Policies\HorarioPolicy;
use Illuminate\Foundation\Support\Providers\AuthServiceProvider as ServiceProvider;
use Illuminate\Support\Facades\Gate;

class AuthServiceProvider extends ServiceProvider
{
    /**
     * The policy mappings for the application.
     *
     * @var array
     */
    protected $policies = [
        // Otras políticas...
        Horario::class => HorarioPolicy::class,
    ];

    /**
     * Register any authentication / authorization services.
     */
    public function boot(): void
    {
        $this->registerPolicies();

        // Definir gates para horarios
        Gate::define('create-horario', function ($user) {
            return $user->role === 'admin';
        });

        Gate::define('update-horario', function ($user) {
            return $user->role === 'admin';
        });

        Gate::define('delete-horario', function ($user) {
            return $user->role === 'admin';
        });

        Gate::define('asignar-partidos', function ($user) {
            return $user->role === 'admin';
        });

        // Otros gates...
    }
}
