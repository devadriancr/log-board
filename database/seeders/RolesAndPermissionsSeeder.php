<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;

class RolesAndPermissionsSeeder extends Seeder
{
    /**
     * Secciones con los cuatro permisos: ver (la lista), crear, editar y eliminar.
     */
    private const RESOURCES = ['boards', 'cards', 'links', 'applications', 'users'];

    private const ACTIONS = ['view', 'create', 'update', 'delete'];

    /**
     * Crea los permisos y los tres roles. Se puede ejecutar varias veces:
     * deja cada rol con exactamente los permisos de esta lista.
     */
    public function run(): void
    {
        app(PermissionRegistrar::class)->forgetCachedPermissions();

        $permissions = collect(self::RESOURCES)
            ->flatMap(fn (string $resource) => collect(self::ACTIONS)->map(fn (string $action) => "$resource.$action"))
            ->push('dashboard.view', 'design.view', 'design.update', 'activity.view');

        $permissions->each(fn (string $name) => Permission::findOrCreate($name));

        // Puede todo. (Además AppServiceProvider le da paso libre, así que los permisos nuevos le llegan solos.)
        Role::findOrCreate('super-admin')->syncPermissions($permissions->all());

        // Ve el dashboard, el diseño del tablero y la vista pública; crea tableros, tarjetas, enlaces y aplicaciones.
        Role::findOrCreate('admin')->syncPermissions([
            'dashboard.view',
            'design.view',
            'design.update',
            'boards.create',
            'cards.create',
            'links.create',
            'applications.create',
        ]);

        // Solo ve la vista pública y su perfil: no necesita permisos.
        Role::findOrCreate('user')->syncPermissions([]);

        // Si todavía nadie es superadministrador, lo es la primera cuenta que existe.
        if (User::role('super-admin')->doesntExist()) {
            User::orderBy('id')->first()?->assignRole('super-admin');
        }
    }
}
