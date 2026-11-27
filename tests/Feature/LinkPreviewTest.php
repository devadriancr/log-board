<?php

namespace Tests\Feature;

use App\Models\Application;
use App\Models\Board;
use App\Models\Card;
use App\Models\Link;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class LinkPreviewTest extends TestCase
{
    use RefreshDatabase;

    private Card $card;

    private Application $application;

    protected function setUp(): void
    {
        parent::setUp();

        $board = Board::create(['name' => 'Producción', 'slug' => 'prod', 'is_home' => true]);
        $this->card = Card::create(['board_id' => $board->id, 'title' => 'IOT', 'type' => 'links', 'grid_x' => 0, 'grid_y' => 0, 'grid_w' => 9, 'grid_h' => 5]);
        $this->application = Application::create(['name' => 'IOT', 'base_url' => 'http://iot.test']);
    }

    private function save(array $data = [])
    {
        return $this->actingAs(User::factory()->superAdmin()->create())->post(route('admin.links.store'), $data + [
            'card_id' => $this->card->id,
            'application_id' => $this->application->id,
            'label' => 'Avance',
            'path' => '/',
            'is_active' => '1',
        ]);
    }

    public function test_the_preview_and_its_size_are_saved_and_shown_on_the_board(): void
    {
        $this->save(['show_preview' => '1', 'preview_size' => 'md'])->assertSessionHasNoErrors();

        $link = Link::sole();
        $this->assertTrue($link->show_preview);
        $this->assertSame('md', $link->preview_size);

        $this->get(route('home'))->assertOk()
            ->assertSee('data-size="md"', false)
            ->assertSee('data-url="http://iot.test/"', false)
            ->assertSee('Cargando vista previa…')
            ->assertDontSee('board-links-fill', false);
    }

    public function test_a_preview_that_fits_the_card_takes_the_free_rows(): void
    {
        $this->save(['label' => 'Normal'])->assertSessionHasNoErrors();
        $this->save(['label' => 'Con vista', 'show_preview' => '1'])->assertSessionHasNoErrors();

        $this->assertSame('fill', Link::where('label', 'Con vista')->sole()->preview_size);

        $this->get(route('home'))->assertOk()->assertSee('--rows-placed: auto 1fr; --rows-stacked: auto 1fr', false);
    }

    public function test_a_link_without_preview_does_not_load_the_page(): void
    {
        $this->save()->assertSessionHasNoErrors();

        $this->get(route('home'))->assertOk()->assertSee('Avance')->assertDontSee('linkPreview', false);
    }

    public function test_a_link_with_options_previews_the_chosen_option(): void
    {
        $this->save(['path' => '/r/{param}', 'show_preview' => '1', 'options' => [['value' => 'a b', 'label' => 'Uno']]])->assertSessionHasNoErrors();

        $this->assertTrue(Link::sole()->show_preview);
        $this->get(route('home'))->assertOk()
            ->assertSee('data-url="http://iot.test/r/a%20b"', false)
            ->assertSee('x-bind:data-url="thumbnail"', false)
            ->assertSee('--rows-placed: 1fr', false);
    }

    public function test_the_thumbnail_can_load_through_another_address_while_the_link_stays_the_same(): void
    {
        $this->actingAs(User::factory()->superAdmin()->create());
        $update = fn (string $address) => $this->put(route('admin.applications.update', $this->application), [
            'name' => 'IOT', 'base_url' => 'http://iot.test', 'preview_url' => $address, 'is_active' => '1',
        ]);

        $update('javascript:alert(1)')->assertSessionHasErrors('preview_url');
        $update('http://board.test:9090/')->assertSessionHasNoErrors();
        $this->assertSame('http://board.test:9090', $this->application->fresh()->preview_url);

        $this->save(['show_preview' => '1'])->assertSessionHasNoErrors();

        $this->get(route('home'))->assertOk()
            ->assertSee('data-url="http://board.test:9090/"', false)
            ->assertSee('href="http://iot.test/"', false);
    }

    public function test_an_unknown_size_is_rejected_and_the_form_offers_the_sizes(): void
    {
        $this->save(['show_preview' => '1', 'preview_size' => 'huge'])->assertSessionHasErrors('preview_size');

        $this->get(route('admin.links.create'))->assertOk()
            ->assertSee('name="show_preview"', false)
            ->assertSee('name="preview_size"', false)
            ->assertSee('Ajustar a la tarjeta');
    }
}
