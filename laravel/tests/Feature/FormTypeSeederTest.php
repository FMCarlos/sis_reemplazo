<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class FormTypeSeederTest extends TestCase
{
    use RefreshDatabase;

    public function test_form_type_seeder_creates_expected_form_types(): void
    {
        $this->seed(\Database\Seeders\FormTypeSeeder::class);

        $this->assertDatabaseHas('form_types', [
            'code' => 'replacement_request',
            'active' => true,
        ]);

        $this->assertDatabaseHas('form_types', [
            'code' => 'overtime_request',
            'active' => false,
        ]);

        $this->assertDatabaseHas('form_types', [
            'code' => 'event_notification',
            'active' => false,
        ]);
    }
}
