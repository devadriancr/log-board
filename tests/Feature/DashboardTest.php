<?php

namespace Tests\Feature;

use App\Models\User;
use Database\Seeders\RolesAndPermissionsSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class DashboardTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed(RolesAndPermissionsSeeder::class);
    }

    public function test_guests_are_redirected_to_the_login_page(): void
    {
        $response = $this->get('/dashboard');
        $response->assertRedirect('/login');
    }

    public function test_super_admins_and_admins_can_visit_the_dashboard(): void
    {
        $this->actingAs(User::factory()->superAdmin()->create())->get('/dashboard')->assertOk();
        $this->actingAs(User::factory()->admin()->create())->get('/dashboard')->assertOk();
    }

    public function test_plain_users_cannot_visit_the_dashboard(): void
    {
        $this->actingAs(User::factory()->plainUser()->create())->get('/dashboard')->assertForbidden();
    }
}
