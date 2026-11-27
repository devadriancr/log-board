<?php

namespace Tests\Feature;

use App\Models\Application;
use App\Models\Board;
use App\Models\Card;
use App\Models\Link;
use App\Models\LinkOption;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class LocaleTest extends TestCase
{
    use RefreshDatabase;

    private Board $board;

    private Card $links;

    protected function setUp(): void
    {
        parent::setUp();

        $this->board = Board::create(['name' => 'Producción', 'name_en' => 'Production', 'slug' => 'prod', 'is_home' => true]);
        $this->links = Card::create(['board_id' => $this->board->id, 'title' => 'Sistemas', 'title_en' => 'Systems', 'type' => 'links', 'grid_x' => 0, 'grid_y' => 0, 'grid_w' => 6, 'grid_h' => 2]);

        $link = Link::create([
            'card_id' => $this->links->id,
            'application_id' => Application::create(['name' => 'App', 'base_url' => 'https://example.com'])->id,
            'label' => 'Reporte diario',
            'label_en' => 'Daily report',
            'label_ja' => '日報',
            'description' => 'Producción del día',
            'show_description' => true,
            'sort_order' => 1, 'grid_x' => 0, 'grid_y' => 0, 'grid_w' => 12,
        ]);
        LinkOption::create(['link_id' => $link->id, 'value' => 'a', 'label' => 'Línea A', 'label_ja' => 'ラインA', 'sort_order' => 0]);

        Card::create(['board_id' => $this->board->id, 'type' => 'text', 'content' => ['<p>Hola</p>'], 'content_en' => ['<p>Hello</p>'], 'grid_x' => 6, 'grid_y' => 0, 'grid_w' => 6, 'grid_h' => 2]);
    }

    public function test_spanish_is_the_main_language_and_shows_the_spanish_texts(): void
    {
        $this->get(route('home'))->assertOk()
            ->assertSee('Reporte diario')->assertSee('Producción del día')->assertSee('Línea A')->assertSee('Hola')
            ->assertSee('Iniciar sesión')->assertSee('lang="es"', false);
    }

    public function test_the_visitor_can_choose_english_or_japanese_and_it_is_kept(): void
    {
        $this->from(route('home'))->get(route('locale', 'en'))->assertRedirect(route('home'));

        $this->get(route('home'))->assertOk()
            ->assertSee('Daily report')->assertSee('Production')->assertSee('Systems')->assertSee('Hello')
            ->assertSee('Log in')->assertSee('lang="en"', false)
            ->assertSee('Producción del día'); // no English description: the Spanish one is shown
    }

    public function test_an_untranslated_option_and_text_fall_back_to_spanish(): void
    {
        $this->get(route('locale', 'en'));

        $this->get(route('home'))->assertSee('Línea A');

        $this->get(route('locale', 'ja'))->assertRedirect();
        $this->get(route('home'))->assertOk()
            ->assertSee('日報')->assertSee('ラインA')->assertSee('ログイン')->assertSee('lang="ja"', false)
            ->assertSee('Hola') // no Japanese text for the card: Spanish
            ->assertSee('Producción'); // nor for the board name
    }

    public function test_an_unknown_language_is_not_accepted(): void
    {
        $this->get('/locale/fr')->assertNotFound();
    }

    public function test_every_admin_page_renders_in_every_language(): void
    {
        $this->actingAs(User::factory()->superAdmin()->create());

        foreach (['es', 'en', 'ja'] as $locale) {
            $this->get(route('locale', $locale));

            foreach (['dashboard', 'admin.boards.index', 'admin.cards.index', 'admin.links.index', 'admin.applications.index', 'admin.users.index', 'admin.activity.index', 'admin.boards.create', 'admin.cards.create', 'admin.links.create', 'admin.applications.create', 'admin.users.create', 'settings.profile', 'settings.password', 'settings.appearance'] as $name) {
                $this->get(route($name))->assertOk()->assertDontSee('cards.fields.', false)->assertDontSee('common.', false);
            }

            $this->get(route('admin.cards.create', ['type' => 'text']))->assertOk()->assertSee('name="content_ja[]"', false);
            // the language links are always visible in the admin sidebar, also while designing
            $this->get(route('admin.boards.design', $this->board))->assertOk()->assertSee(route('locale', 'ja'), false)->assertSee('aria-current="true"', false);
        }
    }

    public function test_the_login_and_password_pages_are_translated_and_have_no_english_left_in_spanish(): void
    {
        $this->get(route('password.request'))->assertOk()->assertSee('Olvidé mi contraseña')->assertSee('Correo electrónico')->assertDontSee('Forgot password');

        $this->get(route('locale', 'ja'));
        $this->get(route('password.request'))->assertOk()->assertSee('パスワードをお忘れの方')->assertSee('ログイン');
        $this->get(route('login'))->assertOk()->assertSee('ログイン');

        $this->get(route('locale', 'en'));
        $this->get(route('password.request'))->assertOk()->assertSee('Forgot password');
    }

    public function test_translations_are_saved_with_the_forms(): void
    {
        $this->actingAs(User::factory()->superAdmin()->create());

        $this->post(route('admin.boards.store'), ['name' => 'Calidad', 'name_en' => 'Quality', 'name_ja' => '品質', 'slug' => 'calidad', 'link_style' => 'button', 'sort_order' => 1])->assertSessionHasNoErrors();
        $this->assertSame('品質', Board::where('slug', 'calidad')->first()->name_ja);

        $this->post(route('admin.cards.store'), ['board_id' => $this->board->id, 'type' => 'text', 'content' => ['<p>Hola</p>'], 'content_en' => ['<p>Hi <b>all</b></p><script>x</script>'], 'content_ja' => ['<p>こんにちは</p>'], 'slide_seconds' => 8, 'grid_w' => 6, 'grid_h' => 2])->assertSessionHasNoErrors();
        $card = Card::where('type', 'text')->latest('id')->first();
        $this->assertSame(['<p>こんにちは</p>'], $card->content_ja);
        $this->assertSame(['<p>Hi <b>all</b></p>'], $card->content_en);

        $this->post(route('admin.links.store'), [
            'card_id' => $this->links->id, 'application_id' => Application::first()->id,
            'label' => 'Otro', 'label_en' => 'Other', 'label_ja' => '他', 'description_en' => 'Desc', 'path' => '/x/{param}',
            'options' => [['value' => '1', 'label' => 'Uno', 'label_en' => 'One', 'label_ja' => '一']],
        ])->assertSessionHasNoErrors();

        $link = Link::where('label', 'Otro')->first();
        $this->assertSame('Other', $link->label_en);
        $this->assertSame('Desc', $link->description_en);
        $this->assertSame('一', $link->options()->first()->label_ja);
    }
}
