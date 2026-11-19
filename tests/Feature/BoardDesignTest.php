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

        $this->actingAs(User::factory()->create());
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
}
