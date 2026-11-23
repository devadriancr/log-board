<?php

namespace Tests\Feature;

use App\Models\Board;
use App\Models\User;
use Database\Seeders\RolesAndPermissionsSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Activitylog\Models\Activity;
use Tests\TestCase;

class PermissionsTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed(RolesAndPermissionsSeeder::class);
    }

    private function boardData(array $extra = []): array
    {
        return $extra + ['name' => 'Planta', 'slug' => 'planta', 'link_style' => 'button', 'sort_order' => 1, 'is_active' => 1];
    }

    public function test_a_plain_user_cannot_enter_the_admin_area(): void
    {
        $board = Board::create(['name' => 'Home', 'slug' => 'home']);

        $this->actingAs(User::factory()->plainUser()->create());

        $this->get(route('admin.boards.index'))->assertForbidden();
        $this->get(route('admin.boards.design', $board))->assertForbidden();
        $this->get(route('admin.users.index'))->assertForbidden();
        $this->get(route('admin.activity.index'))->assertForbidden();
        $this->get(route('home'))->assertOk();
        $this->get(route('settings.profile'))->assertOk();
    }

    public function test_an_admin_sees_the_designer_and_creates_but_nothing_else(): void
    {
        $board = Board::create(['name' => 'Home', 'slug' => 'home']);

        $this->actingAs(User::factory()->admin()->create());

        $this->get(route('admin.boards.design', $board))->assertOk();
        $this->get(route('admin.boards.index'))->assertForbidden();
        $this->post(route('admin.boards.store'), $this->boardData(['from' => 'design']))->assertRedirect();
        $this->assertDatabaseHas('boards', ['slug' => 'planta']);

        $this->put(route('admin.boards.update', $board), $this->boardData(['slug' => 'home']))->assertForbidden();
        $this->delete(route('admin.boards.destroy', $board))->assertForbidden();
        $this->get(route('admin.users.index'))->assertForbidden();
        $this->get(route('admin.activity.index'))->assertForbidden();
    }

    public function test_a_super_admin_can_do_everything(): void
    {
        $board = Board::create(['name' => 'Home', 'slug' => 'home']);

        $this->actingAs(User::factory()->superAdmin()->create());

        $this->get(route('admin.boards.index'))->assertOk();
        $this->get(route('admin.users.index'))->assertOk();
        $this->get(route('admin.activity.index'))->assertOk();
        $this->delete(route('admin.boards.destroy', $board))->assertRedirect();
        $this->assertDatabaseMissing('boards', ['id' => $board->id]);
    }

    public function test_the_log_records_who_created_edited_and_deleted(): void
    {
        $user = User::factory()->superAdmin()->create();
        $this->actingAs($user);

        $this->post(route('admin.boards.store'), $this->boardData())->assertRedirect();
        $board = Board::where('slug', 'planta')->firstOrFail();

        $this->put(route('admin.boards.update', $board), $this->boardData(['name' => 'Planta 2']))->assertRedirect();
        $this->delete(route('admin.boards.destroy', $board))->assertRedirect();

        $events = Activity::where('log_name', 'boards')->orderBy('id')->pluck('event')->all();

        $this->assertSame(['created', 'updated', 'deleted'], $events);
        $this->assertSame($user->id, Activity::where('log_name', 'boards')->first()->causer_id);
    }

    public function test_nobody_removes_their_own_access(): void
    {
        $super = User::factory()->superAdmin()->create();
        $this->actingAs($super);

        $this->put(route('admin.users.update', $super), [
            'name' => $super->name,
            'email' => $super->email,
            'role' => 'user',
            'is_active' => 1,
        ])->assertSessionHas('error');

        $this->assertTrue($super->fresh()->isSuperAdmin());
        $this->delete(route('admin.users.destroy', $super))->assertSessionHas('error');
        $this->assertDatabaseHas('users', ['id' => $super->id]);
    }

    public function test_only_a_super_admin_can_create_super_admins(): void
    {
        $data = ['name' => 'Nuevo', 'email' => 'nuevo@example.com', 'password' => 'password1', 'password_confirmation' => 'password1', 'is_active' => 1];

        // Even with the permission to create users, an admin cannot hand out the super admin role.
        $admin = User::factory()->admin()->create();
        $admin->givePermissionTo('users.create');
        $this->actingAs($admin);

        $this->post(route('admin.users.store'), $data + ['role' => 'super-admin'])->assertSessionHasErrors('role');
        $this->assertDatabaseMissing('users', ['email' => 'nuevo@example.com']);

        $this->actingAs(User::factory()->superAdmin()->create());

        $this->post(route('admin.users.store'), $data + ['role' => 'super-admin'])->assertSessionHasNoErrors();
        $this->assertTrue(User::where('email', 'nuevo@example.com')->firstOrFail()->isSuperAdmin());
    }
}
