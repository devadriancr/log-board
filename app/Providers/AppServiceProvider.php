<?php

namespace App\Providers;

use App\Http\Middleware\SetLocale;
use App\Models\User;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\ServiceProvider;
use Livewire\Livewire;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        // Livewire requests (the login, the settings) must keep the language too.
        Livewire::addPersistentMiddleware([SetLocale::class]);

        // El superadministrador pasa todas las revisiones de permisos. Debe devolver null (no false)
        // para que los demás usuarios sigan con la revisión normal.
        Gate::before(fn (User $user) => $user->isSuperAdmin() ? true : null);
    }
}
