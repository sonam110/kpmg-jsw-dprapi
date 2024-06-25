<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;
use App\Models\Project;
use Str;
use Session;

class ProjectControllerTest extends TestCase
{
    public function test_projects()
    {
        $this->setupAdmin();

        $headers = [ 
            'Accept' => 'application/json',
            'Authorization' => 'Bearer $this->token'
        ];

        $payload = [
            'per_page_record' => 10
        ];

        $response = $this->json('POST', route('projects'), $payload, $headers);

        $response->assertStatus(200);
    }

    public function test_create_project()
    {
        $this->setupAdmin();

        $headers = [ 
            'Accept' => 'application/json',
            'Authorization' => 'Bearer $this->token'
        ];

        $name = Str::random(10);
        $payload = [
            'name' => strtolower($name),
            'projectId' => rand(1000000000,9999999999),
            'description' => 'Test Description'
        ];

        $response = $this->json('POST', route('project.store'), $payload, $headers);

        $response->assertStatus(201);
    }

    public function test_show_project()
    {
        $this->setupAdmin();

        $headers = [ 
            'Accept' => 'application/json',
            'Authorization' => 'Bearer $this->token'
        ];

        $lastProject = \DB::table('projects')
            ->select('id')
            ->whereNull('deleted_at')
            ->orderBy('id', 'DESC')
            ->first();

        $response = $this->json('GET', route('project.show', [$lastProject->id]), $headers);

        $response->assertStatus(200);
    }

    public function test_update_project()
    {
        $this->setupAdmin();

        $headers = [ 
            'Accept' => 'application/json',
            'Authorization' => 'Bearer $this->token'
        ];

        $lastProject = Project::orderBy('id', 'DESC')
            ->first();

        $payload = [
            'name' => $lastProject->name.'-update',
            'projectId' => rand(1000000000,9999999999),
            'description' => 'Test Description'
        ];

        $response = $this->json('PUT', route('project.update', [$lastProject->id]), $payload, $headers);

        $response->assertStatus(200);
    }

    public function test_delete_project()
    {
        $this->setupAdmin();

        $headers = [ 
            'Accept' => 'application/json',
            'Authorization' => 'Bearer $this->token'
        ];

        $lastProject = \DB::table('projects')
            ->select('id')
            ->whereNull('deleted_at')
            ->orderBy('id', 'DESC')
            ->first();

        $response = $this->json('DELETE', route('project.destroy', [$lastProject->id]), $headers);

        $response->assertStatus(200);
    }
}
