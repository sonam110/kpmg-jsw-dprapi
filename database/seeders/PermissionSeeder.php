<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
class PermissionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {


    	app()['cache']->forget('spatie.permission.cache');

      $permissions = [
        [ 
          'name' => 'user-browse',
          'guard_name' => 'api',
          'se_name' => 'user-browse',
          'group_name' => 'user',
          'description' => NULL,
          'belongs_to' => '1'
        ],
        [ 
          'name' => 'user-read',
          'guard_name' => 'api',
          'se_name' => 'user-read',
          'group_name' => 'user',
          'description' => NULL,
          'belongs_to' => '1'
        ],
        [ 
          'name' => 'user-add',
          'guard_name' => 'api',
          'se_name' => 'user-add',
          'group_name' => 'user',
          'description' => NULL,
          'belongs_to' => '1'
        ],
        [ 
          'name' => 'user-edit',
          'guard_name' => 'api',
          'se_name' => 'user-edit',
          'group_name' => 'user',
          'description' => NULL,
          'belongs_to' => '1'
        ],
        [ 
          'name' => 'user-delete',
          'guard_name' => 'api',
          'se_name' => 'user-delete',
          'group_name' => 'user',
          'description' => NULL,
          'belongs_to' => '1'
        ],
        [ 
          'name' => 'role-browse',
          'guard_name' => 'api',
          'se_name' => 'role-browse',
          'group_name' => 'role',
          'description' => NULL,
          'belongs_to' => '1'
        ],
        [ 
          'name' => 'role-read',
          'guard_name' => 'api',
          'se_name' => 'role-read',
          'group_name' => 'role',
          'description' => NULL,
          'belongs_to' => '1'
        ],
        [ 
          'name' => 'role-add',
          'guard_name' => 'api',
          'se_name' => 'role-add',
          'group_name' => 'role',
          'description' => NULL,
          'belongs_to' => '3'
        ],
        [ 
          'name' => 'role-edit',
          'guard_name' => 'api',
          'se_name' => 'role-edit',
          'group_name' => 'role',
          'description' => NULL,
          'belongs_to' => '1'
        ],
        [ 
          'name' => 'role-delete',
          'guard_name' => 'api',
          'se_name' => 'role-delete',
          'group_name' => 'role',
          'description' => NULL,
          'belongs_to' => '1'
        ],
        [ 
          'name' => 'dashboard-browse',
          'guard_name' => 'api',
          'se_name' => 'dashboard-browse',
          'group_name' => 'dashboard',
          'description' => NULL,
          'belongs_to' => '3'
        ],
        [ 
          'name' => 'notifications-browse',
          'guard_name' => 'api',
          'se_name' => 'notifications-browse',
          'group_name' => 'notifications',
          'description' => NULL,
          'belongs_to' => '3'
        ],
        [ 
          'name' => 'notifications-add',
          'guard_name' => 'api',
          'se_name' => 'notifications-add',
          'group_name' => 'notifications',
          'description' => NULL,
          'belongs_to' => '3'
        ],
        [ 
          'name' => 'notifications-edit',
          'guard_name' => 'api',
          'se_name' => 'notifications-edit',
          'group_name' => 'notifications',
          'description' => NULL,
          'belongs_to' => '3'
        ],
        [ 
          'name' => 'notifications-delete',
          'guard_name' => 'api',
          'se_name' => 'notifications-delete',
          'group_name' => 'notifications',
          'description' => NULL,
          'belongs_to' => '3'
        ],
        [ 
          'name' => 'project-browse',
          'guard_name' => 'api',
          'se_name' => 'project-browse',
          'group_name' => 'project',
          'description' => NULL,
          'belongs_to' => '3'
        ],
        [ 
          'name' => 'project-read',
          'guard_name' => 'api',
          'se_name' => 'project-read',
          'group_name' => 'project',
          'description' => NULL,
          'belongs_to' => '3'
        ],
        [ 
          'name' => 'project-add',
          'guard_name' => 'api',
          'se_name' => 'project-add',
          'group_name' => 'project',
          'description' => NULL,
          'belongs_to' => '3'
        ],
        [ 
          'name' => 'project-edit',
          'guard_name' => 'api',
          'se_name' => 'project-edit',
          'group_name' => 'project',
          'description' => NULL,
          'belongs_to' => '3'
        ],
        [ 
          'name' => 'project-delete',
          'guard_name' => 'api',
          'se_name' => 'project-delete',
          'group_name' => 'project',
          'description' => NULL,
          'belongs_to' => '3'
        ],
        [ 
          'name' => 'vendor-browse',
          'guard_name' => 'api',
          'se_name' => 'vendor-browse',
          'group_name' => 'vendor',
          'description' => NULL,
          'belongs_to' => '3'
        ],
        [ 
          'name' => 'vendor-read',
          'guard_name' => 'api',
          'se_name' => 'vendor-read',
          'group_name' => 'vendor',
          'description' => NULL,
          'belongs_to' => '3'
        ],
        [ 
          'name' => 'vendor-add',
          'guard_name' => 'api',
          'se_name' => 'vendor-add',
          'group_name' => 'vendor',
          'description' => NULL,
          'belongs_to' => '3'
        ],
        [ 
          'name' => 'vendor-edit',
          'guard_name' => 'api',
          'se_name' => 'vendor-edit',
          'group_name' => 'vendor',
          'description' => NULL,
          'belongs_to' => '3'
        ],
        [ 
          'name' => 'vendor-delete',
          'guard_name' => 'api',
          'se_name' => 'vendor-delete',
          'group_name' => 'vendor',
          'description' => NULL,
          'belongs_to' => '3'
        ],
        [ 
          'name' => 'workpack-browse',
          'guard_name' => 'api',
          'se_name' => 'workpack-browse',
          'group_name' => 'workpack',
          'description' => NULL,
          'belongs_to' => '3'
        ],
        [ 
          'name' => 'workpack-read',
          'guard_name' => 'api',
          'se_name' => 'workpack-read',
          'group_name' => 'workpack',
          'description' => NULL,
          'belongs_to' => '3'
        ],
        [ 
          'name' => 'workpack-add',
          'guard_name' => 'api',
          'se_name' => 'workpack-add',
          'group_name' => 'workpack',
          'description' => NULL,
          'belongs_to' => '3'
        ],
        [ 
          'name' => 'workpack-edit',
          'guard_name' => 'api',
          'se_name' => 'workpack-edit',
          'group_name' => 'workpack',
          'description' => NULL,
          'belongs_to' => '3'
        ],
        [ 
          'name' => 'workpack-delete',
          'guard_name' => 'api',
          'se_name' => 'workpack-delete',
          'group_name' => 'workpack',
          'description' => NULL,
          'belongs_to' => '3'
        ],
        [ 
          'name' => 'interface-browse',
          'guard_name' => 'api',
          'se_name' => 'interface-browse',
          'group_name' => 'interface',
          'description' => NULL,
          'belongs_to' => '3'
        ],

        [ 
          'name' => 'report-browse',
          'guard_name' => 'api',
          'se_name' => 'report-browse',
          'group_name' => 'report',
          'description' => NULL,
          'belongs_to' => '3'
        ],
        [ 
          'name' => 'profile-browse',
          'guard_name' => 'api',
          'se_name' => 'profile-browse',
          'group_name' => 'profile',
          'description' => NULL,
          'belongs_to' => '3'
        ],
        [ 
          'name' => 'profile-read',
          'guard_name' => 'api',
          'se_name' => 'profile-read',
          'group_name' => 'profile',
          'description' => NULL,
          'belongs_to' => '3'
        ],
        [ 
          'name' => 'profile-add',
          'guard_name' => 'api',
          'se_name' => 'profile-add',
          'group_name' => 'profile',
          'description' => NULL,
          'belongs_to' => '3'
        ],
        [ 
          'name' => 'profile-edit',
          'guard_name' => 'api',
          'se_name' => 'profile-edit',
          'group_name' => 'profile',
          'description' => NULL,
          'belongs_to' => '3'
        ],
        [ 
          'name' => 'profile-delete',
          'guard_name' => 'api',
          'se_name' => 'profile-delete',
          'group_name' => 'profile',
          'description' => NULL,
          'belongs_to' => '3'
        ],
        [ 
          'name' => 'app-setting-browse',
          'guard_name' => 'api',
          'se_name' => 'app-setting-browse',
          'group_name' => 'app-setting',
          'description' => NULL,
          'belongs_to' => '3'
        ],
        [ 
          'name' => 'dpr-log',
          'guard_name' => 'api',
          'se_name' => 'dpr-log',
          'group_name' => 'dpr-management',
          'description' => NULL,
          'belongs_to' => '3'
        ],
        [ 
          'name' => 'dpr-direct',
          'guard_name' => 'api',
          'se_name' => 'dpr-direct',
          'group_name' => 'interface',
          'description' => NULL,
          'belongs_to' => '3'
        ],
        [ 
          'name' => 'dpr-map',
          'guard_name' => 'api',
          'se_name' => 'dpr-map',
          'group_name' => 'dpr-management',
          'description' => NULL,
          'belongs_to' => '3'
        ],
        [ 
          'name' => 'dpr-import',
          'guard_name' => 'api',
          'se_name' => 'dpr-import',
          'group_name' => 'interface',
          'description' => NULL,
          'belongs_to' => '3'
        ],
        [ 
          'name' => 'dpr-config-browse',
          'guard_name' => 'api',
          'se_name' => 'dpr-config-browse',
          'group_name' => 'dpr-management',
          'description' => NULL,
          'belongs_to' => '1'
        ],
        [ 
          'name' => 'dpr-config',
          'guard_name' => 'api',
          'se_name' => 'dpr-config',
          'group_name' => 'dpr-management',
          'description' => NULL,
          'belongs_to' => '1'
        ],
        
        [ 
          'name' => 'item-desc-read',
          'guard_name' => 'api',
          'se_name' => 'item-desc-read',
          'group_name' => 'item-desc',
          'description' => NULL,
          'belongs_to' => '1'
        ],
        [ 
          'name' => 'item-desc-add',
          'guard_name' => 'api',
          'se_name' => 'item-desc-add',
          'group_name' => 'item-desc',
          'description' => NULL,
          'belongs_to' => '1'
        ],
        [ 
          'name' => 'item-desc-edit',
          'guard_name' => 'api',
          'se_name' => 'item-desc-edit',
          'group_name' => 'item-desc',
          'description' => NULL,
          'belongs_to' => '1'
        ],
        [ 
          'name' => 'item-desc-delete',
          'guard_name' => 'api',
          'se_name' => 'item-desc-delete',
          'group_name' => 'item-desc',
          'description' => NULL,
          'belongs_to' => '1'
        ],
        [ 
          'name' => 'item-desc-browse',
          'guard_name' => 'api',
          'se_name' => 'item-desc-browse',
          'group_name' => 'item-desc',
          'description' => NULL,
          'belongs_to' => '1'
        ],

        [ 
          'name' => 'dpr-audit-browse',
          'guard_name' => 'api',
          'se_name' => 'dpr-audit-browse',
          'group_name' => 'dpr-audit',
          'description' => NULL,
        'belongs_to' => '1']
      ];
      foreach ($permissions as $key => $permission) {
        Permission::create($permission);
      }
      
    }
}
