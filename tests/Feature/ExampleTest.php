<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ExampleTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed();
    }

    public function test_the_application_returns_a_successful_response(): void
    {
        $response = $this->get('/');

        $response->assertStatus(200);
    }

    public function test_ppdb_step1_returns_successful_response(): void
    {
        $response = $this->get(route('ppdb.step1'));

        $response->assertStatus(200);
        $response->assertSee('Identitas Calon Siswa');
    }


    public function test_admin_dashboard_returns_successful_response(): void
    {
        $response = $this->get(route('admin.dashboard'));

        $response->assertStatus(200);
        $response->assertSee('Operasional administrasi dan pelaporan');
        $response->assertSee('Distribusi Kuota Jurusan');
        $response->assertSee('Ekspor XLS');
    }
}

