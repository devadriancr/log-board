<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\CardRequest;
use App\Models\Board;
use App\Models\Card;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class CardController extends Controller
{
    /**
     * Display a list of the cards.
     */
    public function index(): View
    {
        $cards = Card::with('board')
            ->withCount('links')
            ->get()
            ->sortBy([
                fn (Card $a, Card $b) => [$a->board->sort_order, $a->board_id, $a->grid_y, $a->grid_x]
                    <=> [$b->board->sort_order, $b->board_id, $b->grid_y, $b->grid_x],
            ])
            ->values();

        return view('admin.cards.index', compact('cards'));
    }

    /**
     * Show the form for creating a new card.
     */
    public function create(Request $request): View
    {
        $card = new Card([
            'board_id' => $request->integer('board') ?: null,
            'grid_w' => 6,
            'grid_h' => 2,
            'is_active' => true,
        ]);

        return view('admin.cards.create', [
            'card' => $card,
            'boards' => $this->boardOptions(),
            'icons' => Card::icons(),
        ]);
    }

    /**
     * Store a newly created card. It is placed at the bottom of its board.
     */
    public function store(CardRequest $request): RedirectResponse
    {
        $data = $request->validated();

        $card = new Card($data + [
            'grid_x' => 0,
            'grid_y' => $this->bottomOf((int) $data['board_id']),
            'updated_by' => $request->user()->id,
        ]);
        $card->save();

        return $this->redirectAfterSave($request, $card, __('cards.messages.created'));
    }

    /**
     * Show the form for editing the card.
     */
    public function edit(Card $card): View
    {
        return view('admin.cards.edit', [
            'card' => $card,
            'boards' => $this->boardOptions(),
            'icons' => Card::icons(),
        ]);
    }

    /**
     * Update the card, keeping it inside the 12 columns of the grid.
     */
    public function update(CardRequest $request, Card $card): RedirectResponse
    {
        $data = $request->validated();

        if ((int) $data['board_id'] !== $card->board_id) {
            $data['grid_x'] = 0;
            $data['grid_y'] = $this->bottomOf((int) $data['board_id']);
        } else {
            $data['grid_x'] = min($card->grid_x, 12 - (int) $data['grid_w']);
        }

        $card->fill($data + ['updated_by' => $request->user()->id])->save();

        return $this->redirectAfterSave($request, $card, __('cards.messages.updated'));
    }

    /**
     * Remove the card.
     */
    public function destroy(Card $card): RedirectResponse
    {
        if ($card->links()->exists()) {
            return to_route('admin.cards.index')
                ->with('error', __('cards.messages.has_links', ['name' => $card->title]));
        }

        $card->delete();

        return to_route('admin.cards.index')
            ->with('success', __('cards.messages.deleted'));
    }

    /**
     * Get the boards as value => text pairs for the select.
     *
     * @return array<int, string>
     */
    private function boardOptions(): array
    {
        return Board::ordered()->pluck('name', 'id')->all();
    }

    /**
     * Get the first free row below the cards of the board.
     */
    private function bottomOf(int $boardId): int
    {
        return (int) Card::where('board_id', $boardId)
            ->selectRaw('MAX(grid_y + grid_h) as bottom')
            ->value('bottom');
    }

    /**
     * Go back to the board designer when the form was opened from there.
     */
    private function redirectAfterSave(Request $request, Card $card, string $message): RedirectResponse
    {
        $to = $request->input('from') === 'design'
            ? to_route('admin.boards.design', $card->board_id)
            : to_route('admin.cards.index');

        return $to->with('success', $message);
    }
}
