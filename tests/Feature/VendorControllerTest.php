<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;
use App\Models\Vendor;
use Str;
use Session;

class VendorControllerTest extends TestCase
{
    public function test_vendors()
    {
        $this->setupAdmin();

        $headers = [ 
            'Accept' => 'application/json',
            'Authorization' => 'Bearer $this->token'
        ];

        $payload = [
            'per_page_record' => 10
        ];

        $response = $this->json('POST', route('vendors'), $payload, $headers);

        $response->assertStatus(200);
    }

    public function test_create_vendor()
    {
        $this->setupAdmin();

        $headers = [ 
            'Accept' => 'application/json',
            'Authorization' => 'Bearer $this->token'
        ];

        $name = Str::random(10);
        $payload = [
            'name' => strtolower($name),
            'description' => 'Test Description'
        ];

        $response = $this->json('POST', route('vendor.store'), $payload, $headers);

        $response->assertStatus(201);
    }

    public function test_show_vendor()
    {
        $this->setupAdmin();

        $headers = [ 
            'Accept' => 'application/json',
            'Authorization' => 'Bearer $this->token'
        ];

        $lastVendor = \DB::table('vendors')
            ->select('id')
            ->whereNull('deleted_at')
            ->orderBy('id', 'DESC')
            ->first();

        $response = $this->json('GET', route('vendor.show', [$lastVendor->id]), $headers);

        $response->assertStatus(200);
    }

    public function test_update_vendor()
    {
        $this->setupAdmin();

        $headers = [ 
            'Accept' => 'application/json',
            'Authorization' => 'Bearer $this->token'
        ];

        $lastVendor = Vendor::orderBy('id', 'DESC')
            ->first();

        $payload = [
            'name' => $lastVendor->name.'-update',
            'description' => 'Test Description'
        ];

        $response = $this->json('PUT', route('vendor.update', [$lastVendor->id]), $payload, $headers);

        $response->assertStatus(200);
    }

    public function test_delete_vendor()
    {
        $this->setupAdmin();

        $headers = [ 
            'Accept' => 'application/json',
            'Authorization' => 'Bearer $this->token'
        ];

        $lastVendor = \DB::table('vendors')
            ->select('id')
            ->whereNull('deleted_at')
            ->orderBy('id', 'DESC')
            ->first();

        $response = $this->json('DELETE', route('vendor.destroy', [$lastVendor->id]), $headers);

        $response->assertStatus(200);
    }
}
