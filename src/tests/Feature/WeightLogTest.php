<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class WeightLogTest extends TestCase
{
    use RefreshDatabase;

    public function test_user_can_create_weight_log(): void
    {
        $user = User::create([
            'name' => 'テストユーザー',
            'email' => 'weight@example.com',
            'password' => bcrypt('password123'),
        ]);

        $response = $this->actingAs($user)->post('/weight_logs/create', [
            'date' => '2026-10-22',
            'weight' => '65.0',
            'calories' => 1800,
            'exercise_time' => '00:30:00',
            'exercise_content' => 'ウォーキング',
        ]);

        $response->assertRedirect(route('weight_logs.index'));

        $this->assertDatabaseHas('weight_logs', [
            'user_id' => $user->id,
            'date' => '2026-10-22',
            'weight' => 65.0,
            'calories' => 1800,
            'exercise_content' => 'ウォーキング',
        ]);
    }

    public function test_user_can_update_weight_log(): void
    {
        $user = User::create([
            'name' => 'テストユーザー',
            'email' => 'update@example.com',
            'password' => bcrypt('password123'),
        ]);

        $log = $user->weightLogs()->create([
            'date' => '2026-10-20',
            'weight' => 65.0,
            'calories' => 1800,
            'exercise_time' => '00:30:00',
            'exercise_content' => 'ウォーキング',
        ]);

        $response = $this->actingAs($user)
            ->post("/weight_logs/{$log->id}/update", [
                'date' => '2026-10-21',
                'weight' => '64.5',
                'calories' => 1700,
                'exercise_time' => '01:00:00',
                'exercise_content' => '水泳',
            ]);

        $response->assertRedirect(route('weight_logs.index'));

        $this->assertDatabaseHas('weight_logs', [
            'id' => $log->id,
            'weight' => 64.5,
            'calories' => 1700,
            'exercise_content' => '水泳',
        ]);
    }

    public function test_user_can_delete_weight_log(): void
    {
        $user = User::create([
            'name' => 'テストユーザー',
            'email' => 'delete@example.com',
            'password' => bcrypt('password123'),
        ]);

        $log = $user->weightLogs()->create([
            'date' => '2026-10-20',
            'weight' => 65.0,
            'calories' => 1800,
            'exercise_time' => '00:30:00',
            'exercise_content' => 'ウォーキング',
        ]);

        $response = $this->actingAs($user)
            ->post("/weight_logs/{$log->id}/delete");

        $response->assertRedirect(route('weight_logs.index'));

        $this->assertDatabaseMissing('weight_logs', [
            'id' => $log->id,
        ]);
    }

    public function test_user_cannot_delete_another_users_weight_log(): void
    {
        $owner = User::create([
            'name' => '所有者',
            'email' => 'owner@example.com',
            'password' => bcrypt('password123'),
        ]);

        $other = User::create([
            'name' => '別のユーザー',
            'email' => 'other@example.com',
            'password' => bcrypt('password123'),
        ]);

        $log = $owner->weightLogs()->create([
            'date' => '2026-10-20',
            'weight' => 65.0,
            'calories' => 1800,
            'exercise_time' => '00:30:00',
            'exercise_content' => 'ウォーキング',
        ]);

        $response = $this->actingAs($other)
            ->post("/weight_logs/{$log->id}/delete");

        $response->assertForbidden();

        $this->assertDatabaseHas('weight_logs', [
            'id' => $log->id,
            'user_id' => $owner->id,
        ]);
    }

    public function test_user_cannot_update_another_users_weight_log(): void
    {
        $owner = User::create([
            'name' => '所有者',
            'email' => 'update-owner@example.com',
            'password' => bcrypt('password123'),
        ]);

        $other = User::create([
            'name' => '別のユーザー',
            'email' => 'update-other@example.com',
            'password' => bcrypt('password123'),
        ]);

        $log = $owner->weightLogs()->create([
            'date' => '2026-10-20',
            'weight' => 65.0,
            'calories' => 1800,
            'exercise_time' => '00:30:00',
            'exercise_content' => 'ウォーキング',
        ]);

        $response = $this->actingAs($other)
            ->post("/weight_logs/{$log->id}/update", [
                'date' => '2026-10-21',
                'weight' => '50.0',
                'calories' => 1500,
                'exercise_time' => '01:00:00',
                'exercise_content' => '水泳',
            ]);

        $response->assertForbidden();

        $this->assertDatabaseHas('weight_logs', [
            'id' => $log->id,
            'user_id' => $owner->id,
            'weight' => 65.0,
        ]);
    }

    public function test_user_can_view_weight_log_list(): void
    {
        $user = User::create([
            'name' => 'テストユーザー',
            'email' => 'list@example.com',
            'password' => bcrypt('password123'),
        ]);

        $user->weightLogs()->create([
            'date' => '2026-10-20',
            'weight' => 65.0,
            'calories' => 1800,
            'exercise_time' => '00:30:00',
            'exercise_content' => 'ウォーキング',
        ]);

        $response = $this->actingAs($user)->get('/weight_logs');

        $response->assertOk();
        $response->assertSee('2026/10/20');
        $response->assertSee('1800kcal');
    }

    public function test_user_can_update_target_weight(): void
    {
        $user = User::create([
            'name' => 'テストユーザー',
            'email' => 'target@example.com',
            'password' => bcrypt('password123'),
        ]);

        $user->weightTarget()->create([
            'target_weight' => 55.0,
        ]);

        $response = $this->actingAs($user)
            ->post('/weight_logs/goal_setting', [
                'target_weight' => '52.5',
            ]);

        $response->assertRedirect(route('weight_logs.index'));

        $this->assertDatabaseHas('weight_targets', [
            'user_id' => $user->id,
            'target_weight' => 52.5,
        ]);

        $this->assertDatabaseCount('weight_targets', 1);
    }

    public function test_user_can_search_weight_logs_by_date(): void
    {
        $user = User::create([
            'name' => 'テストユーザー',
            'email' => 'search@example.com',
            'password' => bcrypt('password123'),
        ]);

        foreach (['2026-10-10', '2026-10-15', '2026-10-20'] as $date) {
            $user->weightLogs()->create([
                'date' => $date,
                'weight' => 65.0,
                'calories' => 1800,
                'exercise_time' => '00:30:00',
                'exercise_content' => 'ウォーキング',
            ]);
        }

        $response = $this->actingAs($user)->get(
            '/weight_logs/search?start_date=2026-10-12&end_date=2026-10-18'
        );

        $response->assertOk();
        $response->assertSee('2026/10/15');
        $response->assertDontSee('2026/10/10');
        $response->assertDontSee('2026/10/20');
    }

    public function test_user_can_view_weight_log_list_when_empty(): void
    {
        $user = User::create([
            'name' => 'テストユーザー',
            'email' => 'empty@example.com',
            'password' => bcrypt('password123'),
        ]);

        $response = $this->actingAs($user)->get('/weight_logs');

        $response->assertOk();
        $response->assertSee('—');
    }
}
