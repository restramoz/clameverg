<?php

namespace Tests\Feature;

use App\Models\Article;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class AdminAuthTest extends TestCase
{
    use RefreshDatabase;

    private function admin(): User
    {
        return User::factory()->create([
            'email'    => 'admin@k3.test',
            'password' => bcrypt('password'),
            'is_admin' => true,
        ]);
    }

    private function regularUser(): User
    {
        return User::factory()->create([
            'email'    => 'user@k3.test',
            'password' => bcrypt('password'),
            'is_admin' => false,
        ]);
    }

    // ─── Login Page ───────────────────────────────────────────────────────────

    public function test_login_page_loads(): void
    {
        $this->get(route('login'))
            ->assertOk()
            ->assertSee('Masuk');
    }

    public function test_admin_can_login(): void
    {
        $admin = $this->admin();

        $this->post(route('login'), [
            'email'    => $admin->email,
            'password' => 'password',
        ])->assertRedirect(route('admin.articles.index'));
    }

    public function test_regular_user_cannot_login_as_admin(): void
    {
        $user = $this->regularUser();

        $response = $this->post(route('login'), [
            'email'    => $user->email,
            'password' => 'password',
        ]);

        // Non-admin is logged out and sent back with an error
        $response->assertSessionHasErrors('email');
        $this->assertFalse(auth()->check());
    }

    public function test_invalid_credentials_rejected(): void
    {
        $response = $this->post(route('login'), [
            'email'    => 'nobody@k3.test',
            'password' => 'wrongpassword',
        ]);

        $response->assertSessionHasErrors('email');
        $this->assertFalse(auth()->check());
    }

    // ─── Admin Panel Protection ───────────────────────────────────────────────

    public function test_guest_cannot_access_admin_panel(): void
    {
        $this->get(route('admin.articles.index'))
            ->assertRedirect(route('login'));
    }

    public function test_regular_user_cannot_access_admin_panel(): void
    {
        $user = $this->regularUser();

        $this->actingAs($user)
            ->get(route('admin.articles.index'))
            ->assertRedirect(route('login'));
    }

    public function test_admin_can_access_admin_panel(): void
    {
        $admin = $this->admin();

        $this->actingAs($admin)
            ->get(route('admin.articles.index'))
            ->assertOk();
    }

    public function test_admin_can_logout(): void
    {
        $admin = $this->admin();

        $this->actingAs($admin)
            ->post(route('logout'))
            ->assertRedirect(route('login'));
    }

    // ─── Rate Limiting ────────────────────────────────────────────────────────

    public function test_login_throttles_after_five_failures(): void
    {
        // Hit 5 times (the limit per throttle:5,1)
        for ($i = 0; $i < 5; $i++) {
            $this->post(route('login'), [
                'email'    => 'brute@k3.test',
                'password' => 'wrong',
            ]);
        }

        // 6th attempt should be throttled
        $this->post(route('login'), [
            'email'    => 'brute@k3.test',
            'password' => 'wrong',
        ])->assertStatus(429);
    }
}
