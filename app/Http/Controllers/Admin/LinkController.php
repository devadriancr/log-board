<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\LinkRequest;
use App\Models\Application;
use App\Models\Card;
use App\Models\Link;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Routing\Controllers\HasMiddleware;
use Illuminate\Routing\Controllers\Middleware;
use Illuminate\View\View;

class LinkController extends Controller implements HasMiddleware
{
    /**
     * Each action asks for its own permission.
     *
     * @return array<int, Middleware>
     */
    public static function middleware(): array
    {
        return [
            new Middleware('can:links.view', only: ['index']),
            new Middleware('can:links.create', only: ['create', 'store']),
            new Middleware('can:links.update', only: ['edit', 'update']),
            new Middleware('can:links.delete', only: ['destroy']),
        ];
    }

    /**
     * Display a list of the links.
     */
    public function index(): View
    {
        $links = Link::with(['card.board', 'application'])
            ->withCount('options')
            ->get()
            ->sortBy([
                fn (Link $a, Link $b) => [$a->card->board->sort_order, $a->card->board_id, $a->card->grid_y, $a->card->grid_x, $a->card_id, $a->sort_order]
                    <=> [$b->card->board->sort_order, $b->card->board_id, $b->card->grid_y, $b->card->grid_x, $b->card_id, $b->sort_order],
            ])
            ->values();

        return view('admin.links.index', compact('links'));
    }

    /**
     * Show the form for creating a new link.
     */
    public function create(Request $request): View
    {
        $cardId = $request->integer('card') ?: null;

        $link = new Link([
            'card_id' => $cardId,
            'path' => '/',
            'show_description' => false,
            'open_in_new_tab' => true,
            'is_active' => true,
        ]);

        return view('admin.links.create', $this->formData($link));
    }

    /**
     * Store a newly created link.
     */
    public function store(LinkRequest $request): RedirectResponse
    {
        $link = new Link();
        $this->save($link, $request);

        return $this->redirectAfterSave($request, $link, __('links.messages.created'));
    }

    /**
     * Show the form for editing the link.
     */
    public function edit(Link $link): View
    {
        return view('admin.links.edit', $this->formData($link));
    }

    /**
     * Update the link.
     */
    public function update(LinkRequest $request, Link $link): RedirectResponse
    {
        $this->save($link, $request);

        return $this->redirectAfterSave($request, $link, __('links.messages.updated'));
    }

    /**
     * Remove the link and its options.
     */
    public function destroy(Link $link): RedirectResponse
    {
        DB::transaction(function () use ($link) {
            $link->options()->delete();
            $link->delete();
        });

        return $this->toIndex('links')
            ->with('success', __('links.messages.deleted'));
    }

    /**
     * Get the data the form needs: the link, its options and the select lists.
     *
     * @return array<string, mixed>
     */
    private function formData(Link $link): array
    {
        $cards = Card::with('board')
            ->where('type', Card::TYPE_LINKS)
            ->get()
            ->sortBy([
                fn (Card $a, Card $b) => [$a->board->sort_order, $a->board_id, $a->grid_y, $a->grid_x]
                    <=> [$b->board->sort_order, $b->board_id, $b->grid_y, $b->grid_x],
            ])
            ->mapWithKeys(fn (Card $card) => [$card->id => $card->board->name.' › '.$card->title])
            ->all();

        $applications = Application::orderBy('name')->pluck('name', 'id')->all();

        $options = $link->exists
            ? $link->options()->orderBy('sort_order')->get()
                ->map(fn ($option) => ['value' => $option->value, 'label' => $option->label])
                ->all()
            : [];

        return compact('link', 'cards', 'applications', 'options');
    }

    /**
     * Save the link and replace its options.
     */
    private function save(Link $link, LinkRequest $request): void
    {
        DB::transaction(function () use ($link, $request) {
            $data = $request->validated();
            $options = $data['options'] ?? [];
            unset($data['options']);

            // A new link (or one moved to another card) goes last, on its own row. Its place is changed from the designer.
            if (! $link->exists || $link->card_id !== (int) $data['card_id']) {
                $lastRow = Link::where('card_id', $data['card_id'])->max('grid_y');

                $data['sort_order'] = (Link::where('card_id', $data['card_id'])->max('sort_order') ?? 0) + 1;
                $data['grid_x'] = 0;
                $data['grid_y'] = $lastRow === null ? 0 : $lastRow + 1;
                $data['grid_w'] = 12;
            }

            $link->fill($data + ['updated_by' => $request->user()->id])->save();

            $before = $this->optionsText($link);

            $link->options()->delete();
            $link->options()->createMany(
                collect($options)
                    ->values()
                    ->map(fn (array $option, int $index) => $option + ['sort_order' => $index])
                    ->all()
            );

            $after = $this->optionsText($link->refresh());

            if ($before !== $after) {
                activity('links')->performedOn($link)->event('updated')
                    ->withProperties(['old' => ['options' => $before], 'attributes' => ['options' => $after]])
                    ->log($link->label);
            }
        });
    }

    /**
     * The options of the link as one line of text ("value = label"), to compare and to write in the log.
     */
    private function optionsText(Link $link): string
    {
        return $link->options()->orderBy('sort_order')->get()
            ->map(fn ($option) => $option->value.' = '.$option->label)
            ->implode(', ');
    }

    /**
     * Go back to the board designer when the form was opened from there.
     */
    private function redirectAfterSave(Request $request, Link $link, string $message): RedirectResponse
    {
        $to = $request->input('from') === 'design'
            ? to_route('admin.boards.design', $link->card->board_id)
            : $this->toIndex('links');

        return $to->with('success', $message);
    }
}
