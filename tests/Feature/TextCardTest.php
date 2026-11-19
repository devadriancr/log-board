<?php

namespace Tests\Feature;

use App\Models\Application;
use App\Models\Board;
use App\Models\Card;
use App\Models\Link;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class TextCardTest extends TestCase
{
    use RefreshDatabase;

    private Board $board;

    protected function setUp(): void
    {
        parent::setUp();

        $this->board = Board::create(['name' => 'Home', 'slug' => 'home', 'is_home' => true]);
        $this->actingAs(User::factory()->create());
    }

    private function payload(array $extra = []): array
    {
        return $extra + [
            'board_id' => $this->board->id,
            'type' => 'text',
            'grid_w' => 6,
            'grid_h' => 2,
            'content' => '<p style="text-align:center">Hola <strong>mundo</strong></p>',
        ];
    }

    public function test_creates_a_text_card_without_title_and_cleans_the_html(): void
    {
        $this->post(route('admin.cards.store'), $this->payload([
            'content' => '<p>Hola <strong>mundo</strong></p><span style="font-size: 18px; position: fixed">grande</span><script>alert(1)</script><img src="/storage/a.png" width="200" class="img-left x" onerror="alert(1)"><a href="javascript:alert(1)">x</a>',
        ]))->assertRedirect();

        $card = Card::first();

        $this->assertTrue($card->isText());
        $this->assertStringStartsWith('Hola mundo', $card->title); // taken from the content
        $this->assertStringContainsString('<span style="font-size:18px;">grande</span>', $card->content);
        $this->assertStringContainsString('width="200"', $card->content);
        $this->assertStringContainsString('<strong>mundo</strong>', $card->content);
        $this->assertStringContainsString('class="img-left"', $card->content);
        $this->assertStringNotContainsString('script', $card->content);
        $this->assertStringNotContainsString('onerror', $card->content);
        $this->assertStringNotContainsString('javascript:', $card->content);
    }

    public function test_a_text_card_needs_content_and_a_links_card_needs_a_title_and_a_hex_colour(): void
    {
        $this->post(route('admin.cards.store'), $this->payload(['content' => '']))->assertSessionHasErrors('content');
        $this->post(route('admin.cards.store'), $this->payload(['type' => 'links', 'title' => 'T', 'color' => '#e3322c']))->assertSessionHasErrors('color');
        $this->post(route('admin.cards.store'), $this->payload(['type' => 'links']))->assertSessionHasErrors('title');
    }

    public function test_the_type_of_a_card_cannot_be_changed_when_editing(): void
    {
        $card = Card::create(['board_id' => $this->board->id, 'title' => 'A', 'type' => 'links', 'grid_x' => 0, 'grid_y' => 0, 'grid_w' => 6, 'grid_h' => 2]);

        $this->put(route('admin.cards.update', $card), $this->payload(['type' => 'text', 'color' => '']))->assertRedirect();

        $this->assertSame('links', $card->fresh()->type);
        $this->assertNull($card->fresh()->color);
    }

    public function test_uploads_images_but_not_svg(): void
    {
        Storage::fake('public');

        $url = $this->post(route('admin.uploads.image'), ['image' => UploadedFile::fake()->image('foto.png')], ['Accept' => 'application/json'])
            ->assertOk()
            ->json('url');

        $this->assertStringStartsWith('/storage/card-images/', $url);
        Storage::disk('public')->assertExists(str_replace('/storage/', '', $url));

        $this->post(route('admin.uploads.image'), ['image' => UploadedFile::fake()->create('x.svg', 1, 'image/svg+xml')], ['Accept' => 'application/json'])
            ->assertUnprocessable();
    }

    public function test_public_board_shows_text_cards_without_header_and_keeps_the_card_size(): void
    {
        Card::create(['board_id' => $this->board->id, 'type' => 'text', 'content' => '<p>Hola equipo</p>', 'grid_x' => 0, 'grid_y' => 0, 'grid_w' => 6, 'grid_h' => 3]);
        Card::create(['board_id' => $this->board->id, 'title' => 'Vacia', 'type' => 'links', 'grid_x' => 6, 'grid_y' => 0, 'grid_w' => 6, 'grid_h' => 2]);

        $this->get(route('home'))
            ->assertOk()
            ->assertSee('Hola equipo', false)
            ->assertSee('--w: 6; --y: 1; --h: 3', false)
            ->assertDontSee('<h2', false)
            ->assertDontSee('Vacia');
    }

    public function test_a_link_cannot_be_added_to_a_text_card(): void
    {
        $card = Card::create(['board_id' => $this->board->id, 'type' => 'text', 'content' => '<p>x</p>', 'grid_x' => 0, 'grid_y' => 0, 'grid_w' => 6, 'grid_h' => 2]);
        $app = Application::create(['name' => 'App', 'base_url' => 'https://example.com']);

        $this->post(route('admin.links.store'), ['card_id' => $card->id, 'application_id' => $app->id, 'label' => 'L', 'path' => '/'])
            ->assertSessionHasErrors('card_id');
    }

    public function test_designer_and_forms_render(): void
    {
        $card = Card::create(['board_id' => $this->board->id, 'type' => 'text', 'content' => '<p>x</p>', 'grid_x' => 0, 'grid_y' => 0, 'grid_w' => 6, 'grid_h' => 2]);

        $this->get(route('admin.boards.design', $this->board))->assertOk()->assertSee('gs-id="'.$card->id.'"', false);
        $this->get(route('admin.cards.edit', $card))->assertOk()->assertSee('id="rich-editor"', false);
        $this->get(route('admin.cards.create', ['type' => 'text']))->assertOk()->assertSee('id="rich-editor"', false);
        $this->get(route('admin.cards.create'))->assertOk()->assertDontSee('id="rich-editor"', false);
        $this->get(route('admin.cards.index'))->assertOk();
    }

    public function test_a_links_card_takes_a_palette_colour_that_reaches_the_public_board_and_the_designer(): void
    {
        $this->post(route('admin.cards.store'), ['board_id' => $this->board->id, 'title' => 'Sistemas', 'type' => 'links', 'color' => 'green', 'is_active' => 1, 'grid_w' => 6, 'grid_h' => 2])->assertSessionHasNoErrors();
        $this->post(route('admin.cards.store'), ['board_id' => $this->board->id, 'title' => 'X', 'type' => 'links', 'color' => 'pink', 'grid_w' => 6, 'grid_h' => 2])->assertSessionHasErrors('color');

        $card = Card::first();
        $link = Link::create(['card_id' => $card->id, 'application_id' => Application::create(['name' => 'App', 'base_url' => 'https://example.com'])->id, 'label' => 'L', 'sort_order' => 1, 'grid_x' => 0, 'grid_y' => 0, 'grid_w' => 12]);

        $this->get(route('home'))->assertOk()->assertSee('data-card-color="green"', false);
        $this->get(route('admin.boards.design', $this->board))->assertOk()->assertSee('data-card-color="green"', false);
    }
}
