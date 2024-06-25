<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use App\Models\User;
use App\Models\Module;
use App\Models\AppSetting;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        

        $adminUser = new User();
        $adminUser->role_id                 = '1';
        $adminUser->name                    = 'admin';
        $adminUser->email                   = 'in-fmpivotsupport@kpmg.com';
        $adminUser->password                = \Hash::make(',.%{]p#e3MA802,');
        $adminUser->save();
        $admin = $adminUser;

        $appSetting = new AppSetting();
        $appSetting->id                      = '1';
        $appSetting->app_name                = 'Dpr Management';
        $appSetting->description             = 'Dpr Management';
        $appSetting->email                   = 'admin@gmail.com';
        $appSetting->mobile_no               = '45465767';
        $appSetting->save();

        $adminRole = Role::where('id','1')->first();
        $vendorRole = Role::where('id','2')->first();
        $user = User::first();
        $user->assignRole($adminRole);

        $adminPermissions = [
            'user-browse',
            'user-read',
            'user-add',
            'user-edit',
            'user-delete',
            'role-browse',
            'role-read',
            'role-add',
            'role-edit',
            'role-delete',
            'dashboard-browse',
            'notifications-browse',
            'notifications-add',
            'notifications-edit',
            'notifications-delete',
            'project-browse',
            'project-read',
            'project-add',
            'project-edit',
            'project-delete',
            'vendor-browse',
            'vendor-read',
            'vendor-add',
            'vendor-edit',
            'vendor-delete',
            'workpack-browse',
            'workpack-read',
            'workpack-add',
            'workpack-edit',
            'workpack-delete',
            'report-browse',
            'profile-browse',
            'profile-read',
            'profile-add',
            'profile-edit',
            'profile-delete',
            'app-setting-browse',
            'interface-browse',
            'dpr-log',
            'dpr-direct',
            'dpr-map',
            'dpr-import',
            'dpr-config-browse',
            'dpr-config',
            'dpr-audit-browse',
            'item-desc-add',
            'item-desc-edit',
            'item-desc-read',
            'item-desc-delete',
            'item-desc-browse',
        ];
        foreach ($adminPermissions as $key => $permission) {
            $adminRole->givePermissionTo($permission);
            $user->givePermissionTo($permission);
        }

        $userPermissions = [
            'dashboard-browse',
            'notifications-browse',
            'notifications-add',
            'notifications-edit',
            'notifications-delete',
            'project-browse',
            'project-read',
            'project-add',
            'project-edit',
            'project-delete',
            'vendor-browse',
            'vendor-read',
            'vendor-add',
            'vendor-edit',
            'vendor-delete',
            'workpack-browse',
            'workpack-read',
            'workpack-add',
            'workpack-edit',
            'workpack-delete',
            'report-browse',
            'profile-browse',
            'profile-read',
            'profile-add',
            'profile-edit',
            'profile-delete',
            'dpr-log',
            'dpr-direct',
            'dpr-map',
            'dpr-import',
            'dpr-config',
            'dpr-audit-browse',
            
        ];
        foreach ($userPermissions as $key => $permission) {
            $vendorRole->givePermissionTo($permission);
        }


    }
}
