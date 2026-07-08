<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class AdminPasswordTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_can_change_password_after_confirming_current_password(): void
    {
        $this->seed();
        $admin = User::where('role', 'admin')->firstOrFail();

        $this->actingAs($admin, 'web')->get('/admin/password')
            ->assertOk()
            ->assertSee('Ganti Password Admin')
            ->assertSee('Tips Keamanan Admin');

        $this->actingAs($admin, 'web')->put('/admin/password', [
            'current_password' => 'password',
            'password' => 'AdminBaru123',
            'password_confirmation' => 'AdminBaru123',
        ])->assertRedirect()
            ->assertSessionHas('message', 'Password admin berhasil diperbarui.');

        $admin->refresh();
        $this->assertTrue(Hash::check('AdminBaru123', $admin->password));
        $this->assertFalse(Hash::check('password', $admin->password));
    }

    public function test_admin_password_change_requires_valid_current_password(): void
    {
        $this->seed();
        $admin = User::where('role', 'admin')->firstOrFail();

        $this->actingAs($admin, 'web')->put('/admin/password', [
            'current_password' => 'salah-password',
            'password' => 'AdminBaru123',
            'password_confirmation' => 'AdminBaru123',
        ])->assertSessionHasErrors('current_password');

        $this->assertTrue(Hash::check('password', $admin->fresh()->password));
    }

    public function test_only_admin_can_open_password_page(): void
    {
        $this->seed();
        $user = User::factory()->create([
            'role' => 'user',
            'password' => 'password123',
        ]);

        $this->get('/admin/password')->assertRedirect('/login');
        $this->actingAs($user, 'web')->get('/admin/password')->assertForbidden();
        $this->actingAs($user, 'web')->put('/admin/password', [
            'current_password' => 'password123',
            'password' => 'AdminBaru123',
            'password_confirmation' => 'AdminBaru123',
        ])->assertForbidden();
    }
}
