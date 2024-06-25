<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;
use App\Models\DprConfig;
use App\Models\Project;
use App\Models\Vendor;
use App\Models\WorkPackage;
use Str;
use Session;

class DprConfigControllerTest extends TestCase
{
    public function test_dpr_configs()
    {
        $this->setupUser();

        $headers = [ 
            'Accept' => 'application/json',
            'Authorization' => 'Bearer $this->token'
        ];

        $payload = [
            'per_page_record' => 10
        ];

        $response = $this->json('POST', route('dpr-configs'), $payload, $headers);

        $response->assertStatus(200);
    }

    public function test_create_dprConfig()
    {
        $this->setupUser();

        $headers = [ 
            'Accept' => 'application/json',
            'Authorization' => 'Bearer $this->token'
        ];

        $name = Str::random(10);
        $description = 'test desc';

        //create vendor
        $vendor = new Vendor;
        $vendor->name = $name;
        $vendor->description = $description;
        $vendor->status = 1;
        $vendor->save();

        //create project
        $project = new Project;
        $project->projectId = rand(1000,9999);
        $project->name = $name;
        $project->description = $description;
        $project->user_id = auth()->id();
        $project->status = 1;
        $project->save();

        //create work pack
        $workPackage = new WorkPackage;
        $workPackage->workPackId = rand(1000,9999);
        $workPackage->name = $name;
        $workPackage->description = $description;
        $workPackage->unit_of_measure = rand(0,9);
        $workPackage->man_power_type = rand(1,2);
        $workPackage->user_id = auth()->id();
        $workPackage->status = 1;
        $workPackage->save();


        $payload = [
            'profile_name' => strtolower($name),
            'project_id' => $project->id ,
            'vendor_id' => $vendor->id,
            'work_pack_id' => $workPackage->id
        ];

        $response = $this->json('POST', route('dpr-config.store'), $payload, $headers);

        $response->assertStatus(201);
    }

    public function test_show_dprConfig()
    {
        $this->setupUser();

        $headers = [ 
            'Accept' => 'application/json',
            'Authorization' => 'Bearer $this->token'
        ];

        $lastDprConfig = \DB::table('dpr_configs')
            ->select('id')
            ->whereNull('deleted_at')
            ->orderBy('id', 'DESC')
            ->first();

        $response = $this->json('GET', route('dpr-config.show', [$lastDprConfig->id]), $headers);

        $response->assertStatus(200);
    }

    public function test_update_dprConfig()
    {
        $this->setupUser();

        $headers = [ 
            'Accept' => 'application/json',
            'Authorization' => 'Bearer $this->token'
        ];

        $lastDprConfig = DprConfig::orderBy('id', 'DESC')
            ->first();

        $payload = [
            'profile_name' => $lastDprConfig->profile_name.'-update',
            'project_id' => Project::orderBy('id', 'DESC')->first()->id ,
            'vendor_id' => Vendor::orderBy('id', 'DESC')->first()->id,
            'work_pack_id' => WorkPackage::orderBy('id', 'DESC')->first()->id
        ];

        $response = $this->json('PUT', route('dpr-config.update', [$lastDprConfig->id]), $payload, $headers);

        $response->assertStatus(200);
    }

    public function test_delete_dprConfig()
    {
        $this->setupUser();

        $headers = [ 
            'Accept' => 'application/json',
            'Authorization' => 'Bearer $this->token'
        ];

        $lastDprConfig = \DB::table('dpr_configs')
            ->select('id')
            ->whereNull('deleted_at')
            ->orderBy('id', 'DESC')
            ->first();

        $response = $this->json('DELETE', route('dpr-config.destroy', [$lastDprConfig->id]), $headers);

        $response->assertStatus(200);
    }
}
