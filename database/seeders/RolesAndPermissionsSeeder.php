<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;

class RolesAndPermissionsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
                // Reset cached roles and permissions
                app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();

                // Crea permisos (opcional, si los necesitas ya)
               /*   Permission::create(['name' => 'Mensaje inicio']); */

        
                // Crea roles
                Role::firstOrCreate(['name' => 'admin']);
                Role::firstOrCreate(['name' => 'traige']);
                Role::firstOrCreate(['name' => 'closer']);
                Role::firstOrCreate(['name' => 'cms']);
        
                // Asigna permisos a los roles (ejemplo)
                $adminRole = Role::where('name', 'admin')->first();
                if ($adminRole) {
                    $adminRole->givePermissionTo(['Mensaje inicio']);
                }
                // $editorRole->givePermissionTo(['edit articles', 'publish articles']);
    }
}
