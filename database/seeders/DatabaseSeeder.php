<?php

namespace Database\Seeders;

// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use File;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     *
     * @return void
     */
    public function run()
    {

        File::ensureDirectoryExists('public/excel');
        File::ensureDirectoryExists('public/export');
        File::ensureDirectoryExists('public/fonts');
        File::ensureDirectoryExists('public/import');
        File::ensureDirectoryExists('public/pdf');
        File::ensureDirectoryExists('public/uploads');
        File::ensureDirectoryExists('public/user');
        File::ensureDirectoryExists('public/user/import');
        
        $this->call(RoleSeeder::class);
        $this->call(PermissionSeeder::class);
        $this->call(UserSeeder::class);
        
        // \App\Models\User::factory(10)->create();

        // \App\Models\User::factory()->create([
        //     'name' => 'Test User',
        //     'email' => 'test@example.com',
        // ]);
    }
}
