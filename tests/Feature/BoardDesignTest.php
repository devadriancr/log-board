<?php

namespace Tests\Feature;

use App\Models\Application;
use App\Models\Board;
use App\Models\Card;
use App\Models\Link;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class BoardDesignTest extends TestCase
{
    use RefreshDatabase;

    private Board $board;

    private Card $card;

    /** @var \Illuminate\Support\Collection<int, Link> */
    private $links;

    protected function setUp(): void
    {
        parent::setUp();

        $this->board = Board::create(['name' => 'Home', 'slug' => 'home']);
        $this->card = Card::create(['board_id' => $this->board->id, 'title' => 'A', 'grid_x' => 0, 'grid_y' => 0, 'grid_w' => 6, 'grid_h' => 1]);
        $app = Application::create(['name' => 'App', 'base_url' => 'https://example.com']);

        $this->links = collect(range(1, 3))->map(fn ($n) => Link::create([
            'card_id' => $this->card->id,
            'application_id' => $app->id,
            'label' => "L$n",
            'sort_order' => $n,
            'grid_y' => $n - 1,
        ]));

        $this->actingAs(User::factory()->superAdmin()->create());
    }

    public function test_saves_a_card_one_row_tall_and_the_position_of_its_links(): void
    {
        // L3 goes to the top row next to L1: the order becomes L1, L3 (same row), L2.
        $this->putJson(route('admin.boards.design.update', $this->board), [
            'cards' => [['id' => $this->card->id, 'x' => 0, 'y' => 0, 'w' => 1, 'h' => 1]],
            'links' => [
                ['id' => $this->links[0]->id, 'x' => 0, 'y' => 0, 'w' => 6],
                ['id' => $this->links[2]->id, 'x' => 6, 'y' => 0, 'w' => 6],
                ['id' => $this->links[1]->id, 'x' => 0, 'y' => 1, 'w' => 12],
            ],
        ])->assertOk();

        $this->assertSame(1, $this->card->fresh()->grid_w);
        $this->assertSame(1, $this->card->fresh()->grid_h);
        $this->assertSame(
            ['grid_x' => 6, 'grid_y' => 0, 'grid_w' => 6, 'sort_order' => 2],
            $this->links[2]->fresh()->only('grid_x', 'grid_y', 'grid_w', 'sort_order'),
        );
        $this->assertSame(3, $this->links[1]->fresh()->sort_order);
    }

    public function test_rejects_a_link_that_goes_past_the_12th_column(): void
    {
        $this->putJson(route('admin.boards.design.update', $this->board), [
            'cards' => [['id' => $this->card->id, 'x' => 0, 'y' => 0, 'w' => 6, 'h' => 2]],
            'links' => [['id' => $this->links[0]->id, 'x' => 8, 'y' => 0, 'w' => 6]],
        ])->assertUnprocessable();
    }

    public function test_rejects_a_link_narrower_than_4_columns(): void
    {
        $this->putJson(route('admin.boards.design.update', $this->board), [
            'cards' => [['id' => $this->card->id, 'x' => 0, 'y' => 0, 'w' => 6, 'h' => 2]],
            'links' => [['id' => $this->links[0]->id, 'x' => 0, 'y' => 0, 'w' => 3]],
        ])->assertUnprocessable();
    }

    public function test_places_a_new_link_on_its_own_row_at_the_end_of_the_card(): void
    {
        $this->post(route('admin.links.store'), [
            'card_id' => $this->card->id,
            'application_id' => Application::first()->id,
            'label' => 'New',
            'path' => '/',
        ])->assertRedirect();

        $this->assertSame(
            ['grid_x' => 0, 'grid_y' => 3, 'grid_w' => 12, 'sort_order' => 4],
            Link::where('label', 'New')->first()->only('grid_x', 'grid_y', 'grid_w', 'sort_order'),
        );
    }

    public function test_designer_shows_the_new_application_and_board_settings_and_delete_modals(): void
    {
        $this->get(route('admin.boards.design', $this->board))
            ->assertOk()
            ->assertSee(e(route('admin.applications.create', ['board' => $this->board->id, 'from' => 'design'])), false)
            ->assertSee(__('design.board_settings'))
            ->assertSee('delete-card-'.$this->card->id);
    }

    public function test_application_created_from_the_designer_goes_back_to_it(): void
    {
        $this->get(route('admin.applications.create', ['board' => $this->board->id, 'from' => 'design']))
            ->assertOk()
            ->assertSee('name="board" value="'.$this->board->id.'"', false);

        $this->post(route('admin.applications.store'), [
            'name' => 'Nueva', 'base_url' => 'https://nueva.test', 'from' => 'design', 'board' => $this->board->id,
        ])->assertRedirect(route('admin.boards.design', $this->board));

        $this->assertDatabaseHas('applications', ['name' => 'Nueva']);
    }

    public function test_board_settings_are_saved_from_the_designer_and_keep_home_and_order(): void
    {
        $this->board->update(['is_home' => true, 'sort_order' => 5]);

        $this->put(route('admin.boards.update', $this->board), [
            'from' => 'design', 'name' => 'Inicio', 'slug' => 'inicio', 'link_style' => 'link',
            'sort_order' => 5, 'is_home' => 1,
        ])->assertRedirect(route('admin.boards.design', $this->board));

        $board = $this->board->fresh();
        $this->assertSame(['Inicio', 'inicio', true, false, 5], [$board->name, $board->slug, $board->is_home, $board->is_active, $board->sort_order]);
    }

    public function test_deleting_a_card_from_the_designer_returns_to_it(): void
    {
        $empty = Card::create(['board_id' => $this->board->id, 'title' => 'B', 'grid_x' => 6, 'grid_y' => 0, 'grid_w' => 6, 'grid_h' => 1]);

        $this->delete(route('admin.cards.destroy', $empty), ['from' => 'design'])
            ->assertRedirect(route('admin.boards.design', $this->board));
        $this->assertModelMissing($empty);

        // A card with links is kept, and the error is also shown in the designer.
        $this->delete(route('admin.cards.destroy', $this->card), ['from' => 'design'])
            ->assertRedirect(route('admin.boards.design', $this->board))
            ->assertSessionHas('error');
        $this->assertModelExists($this->card);
    }

    public function test_creates_a_board_from_the_designer_and_opens_it(): void
    {
        $this->post(route('admin.boards.store'), [
            'from' => 'design', 'name' => 'Ventas', 'slug' => 'ventas', 'link_style' => 'button', 'sort_order' => 2, 'is_active' => 1,
        ]);

        $new = Board::where('slug', 'ventas')->firstOrFail();

        $this->assertFalse($new->is_home);
        $this->get(route('admin.boards.design', $new))->assertOk();
    }

    public function test_new_board_modal_locks_the_home_option_while_a_home_board_exists(): void
    {
        $this->get(route('admin.boards.design', $this->board))->assertDontSee(__('design.home_taken'));

        $this->board->update(['is_home' => true]);

        $this->get(route('admin.boards.design', $this->board))
            ->assertSee(__('design.home_taken'));
    }

    public function test_deletes_an_empty_board_from_the_designer_and_opens_another(): void
    {
        $empty = Board::create(['name' => 'Vacio', 'slug' => 'vacio', 'sort_order' => 9]);

        $this->get(route('admin.boards.design', $empty))->assertOk()->assertSee(__('design.delete_board'));

        $this->delete(route('admin.boards.destroy', $empty), ['from' => 'design'])
            ->assertRedirect(route('admin.boards.design', $this->board));
        $this->assertModelMissing($empty);

        // A board with cards is kept and the user stays in the designer.
        $this->from(route('admin.boards.design', $this->board))
            ->delete(route('admin.boards.destroy', $this->board), ['from' => 'design'])
            ->assertRedirect(route('admin.boards.design', $this->board))
            ->assertSessionHas('error');
        $this->assertModelExists($this->board);
    }
}
