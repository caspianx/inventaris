<?php

namespace Tests\Feature;

use App\Models\RolePermission;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ProfileAccessTest extends TestCase
{
    use RefreshDatabase;

    public function test_profile_edit_requires_profile_edit_permission(): void
    {
        /** @var User $user */
        $user = User::factory()->create([
            'name' => 'Staff User',
            'email' => 'staff@example.com',
            'role' => 'staff',
        ]);

        $response = $this->actingAs($user)->get(route('profile.edit'));

        $response->assertForbidden();
    }

    public function test_profile_edit_allows_users_with_profile_edit_permission(): void
    {
        /** @var User $user */
        $user = User::factory()->create([
            'name' => 'Staff User With Access',
            'email' => 'staff-access@example.com',
            'role' => 'staff',
        ]);

        RolePermission::create([
            'role' => 'staff',
            'permission' => 'profile.edit',
        ]);

        $response = $this->actingAs($user)->get(route('profile.edit'));

        $response->assertOk();
    }
}
