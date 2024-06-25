<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;
use App\Models\WorkPackage;
use Str;
use Session;

class WorkPackageControllerTest extends TestCase
{
    public function test_workPackages()
    {
        $this->setupAdmin();

        $headers = [ 
            'Accept' => 'application/json',
            'Authorization' => 'Bearer $this->token'
        ];

        $payload = [
            'per_page_record' => 10
        ];

        $response = $this->json('POST', route('work-packages'), $payload, $headers);

        $response->assertStatus(200);
    }

    public function test_create_workPackage()
    {
        $this->setupAdmin();

        $headers = [ 
            'Accept' => 'application/json',
            'Authorization' => 'Bearer $this->token'
        ];

        $name = Str::random(10);
        $payload = [
            'name' => strtolower($name),
            'workPackId' => rand(1000000000,9999999999),
            'description' => 'Test Description',
            'unit_of_measure' => '10',
            'man_power_type' => '2'
        ];

        $response = $this->json('POST', route('work-package.store'), $payload, $headers);

        $response->assertStatus(201);
    }

    public function test_show_workPackage()
    {
        $this->setupAdmin();

        $headers = [ 
            'Accept' => 'application/json',
            'Authorization' => 'Bearer $this->token'
        ];

        $lastWorkPackage = \DB::table('work_packages')
            ->select('id')
            ->whereNull('deleted_at')
            ->orderBy('id', 'DESC')
            ->first();

        $response = $this->json('GET', route('work-package.show', [$lastWorkPackage->id]), $headers);

        $response->assertStatus(200);
    }

    public function test_update_workPackage()
    {
        $this->setupAdmin();

        $headers = [ 
            'Accept' => 'application/json',
            'Authorization' => 'Bearer $this->token'
        ];

        $lastWorkPackage = WorkPackage::orderBy('id', 'DESC')
            ->first();

        $payload = [
            'name' => $lastWorkPackage->name.'-update',
            'workPackId' => rand(1000000000,9999999999),
            'description' => 'Test Description',
            'unit_of_measure' => '10',
            'man_power_type' => '2'
        ];

        $response = $this->json('PUT', route('work-package.update', [$lastWorkPackage->id]), $payload, $headers);

        $response->assertStatus(200);
    }

    public function test_delete_workPackage()
    {
        $this->setupAdmin();

        $headers = [ 
            'Accept' => 'application/json',
            'Authorization' => 'Bearer $this->token'
        ];

        $lastWorkPackage = \DB::table('work_packages')
            ->select('id')
            ->whereNull('deleted_at')
            ->orderBy('id', 'DESC')
            ->first();

        $response = $this->json('DELETE', route('work-package.destroy', [$lastWorkPackage->id]), $headers);

        $response->assertStatus(200);
    }
}
