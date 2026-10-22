<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class RegistrationTest extends TestCase
{
    use RefreshDatabase;

    public function test_registration_step1_saves_data_in_session(): void
    {
        $response = $this->post('/register/step1', [
            'name' => 'テストユーザー',
            'email' => 'test@example.com',
            'password' => 'password123',
        ]);

        $response->assertRedirect('/register/step2');

        $this->assertTrue(session()->has('register_data_step1'));
        $this->assertSame(
            'test@example.com',
            session('register_data_step1.email')
        );
    }

    public function test_registration_step2_creates_user_and_weight_records(): void
    {
        $this->post('/register/step1', [
            'name' => 'テストユーザー',
            'email' => 'test@example.com',
            'password' => 'password123',
        ]);

        $response = $this->post('/register/step2', [
            'weight' => 65.0,
            'target_weight' => 55.0,
        ]);

        $response->assertRedirect('/weight_logs');

        $this->assertDatabaseHas('users', [
            'email' => 'test@example.com',
        ]);

        $this->assertDatabaseHas('weight_targets', [
            'target_weight' => 55.0,
        ]);

        $this->assertDatabaseHas('weight_logs', [
            'weight' => 65.0,
        ]);

        $this->assertAuthenticated();
        $this->assertFalse(session()->has('register_data_step1'));
    }
}
