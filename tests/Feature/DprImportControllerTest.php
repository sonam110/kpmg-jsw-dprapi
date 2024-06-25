<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;
use App\Models\DprConfig;
use App\Models\Project;
use App\Models\Vendor;
use App\Models\WorkPackage;
use App\Models\DprImport;
use Str;
use Session;
use File;

class DprImportControllerTest extends TestCase
{
    public function test_dpr_import_list()
    {
        $this->setupUser();

        $headers = [ 
            'Accept' => 'application/json',
            'Authorization' => 'Bearer $this->token'
        ];

        $payload = [
            'per_page_record' => 10
        ];

        $response = $this->json('POST', route('dpr-import-list'), $payload, $headers);

        $response->assertStatus(200);
    }

    public function test_dpr_import()
    {
        $this->setupUser();

        $headers = [ 
            'Accept' => 'multipart/form-data',
            'Authorization' => 'Bearer $this->token'
        ];

        $path = storage_path('app/public/dpr_import_sample.xlsx');
        $file = File::get($path);
        $payload = [
            'file' => $path,
            'dpr_config_id' => 34
        ];

        $response = $this->json('POST', route('dpr-import'), $payload, $headers);

        $response->assertStatus(201);
    }

    public function test_dpr_store()
    {
        $this->setupUser();

        $headers = [ 
            'Accept' => 'application/json',
            'Authorization' => 'Bearer $this->token'
        ];


        $payload = [
            'dpr_config_id' => DprConfig::latest()->first()->id,
            // "dpr_config_id" => "34",
            "data_date" => "09-05-2023",
            "total_scope" => "10",
            "actual_till_date" => "09-05-2023",
            "plan_ftm" => "gjgjj",
            "actual_ftm" => "vghg",
            "today" => "1",
            "dwg_avail" => "htff",
            "manpower" => "4",
            "change_reason_for_plan_ftm" => "change_reason_for_plan_ftm",
            "work_item" => "work_item"
        ];

        $response = $this->json('POST', route('dpr-import-store'), $payload, $headers);

        $response->assertStatus(201);
    }
}
