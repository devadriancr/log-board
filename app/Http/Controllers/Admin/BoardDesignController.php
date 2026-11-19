<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\BoardDesignRequest;
use App\Models\Board;
use App\Models\Card;
use App\Models\Link;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class BoardDesignController extends Controller
{
    /**
     * Show the designer of the board.
     */
    public function edit(Board $board): View
    {
        $boards = Board::ordered()->get(['id', 'name', 'is_home']);

        $cards = $board->cards()
            ->with(['links' => fn ($query) => $query->orderBy('sort_order')])
            ->orderBy('grid_y')
            ->orderBy('grid_x')
            ->get();

        return view('admin.boards.design', compact('board', 'boards', 'cards'));
    }

    /**
     * Save the position and size of the cards of the board.
     */
    public function update(BoardDesignRequest $request, Board $board): JsonResponse
    {
        $layout = collect($request->validated('cards'))->keyBy('id');

        $belong = $board->cards()->whereIn('id', $layout->keys())->count();

        if ($belong !== $layout->count()) {
            return response()->json(['message' => __('design.messages.foreign')], 422);
        }

        $links = collect($request->validated('links', []))->keyBy('id');

        // Only links of this board, mapped to their card so the order number can be derived from the position.
        $cardOf = Link::whereIn('id', $links->keys())
            ->whereHas('card', fn ($query) => $query->where('board_id', $board->id))
            ->pluck('card_id', 'id');

        if ($cardOf->count() !== $links->count()) {
            return response()->json(['message' => __('design.messages.foreign')], 422);
        }

        DB::transaction(function () use ($layout, $links, $cardOf, $request) {
            // The order number of a link is its place in the card: top to bottom, left to right.
            foreach ($links->groupBy(fn ($link) => $cardOf[$link['id']]) as $inCard) {
                foreach ($inCard->sortBy([['y', 'asc'], ['x', 'asc']])->values() as $index => $link) {
                    Link::whereKey($link['id'])->update([
                        'grid_x' => $link['x'],
                        'grid_y' => $link['y'],
                        'grid_w' => $link['w'],
                        'sort_order' => $index + 1,
                        'updated_by' => $request->user()->id,
                    ]);
                }
            }

            foreach ($layout as $id => $item) {
                Card::whereKey($id)->update([
                    'grid_x' => $item['x'],
                    'grid_y' => $item['y'],
                    'grid_w' => $item['w'],
                    'grid_h' => $item['h'],
                    'updated_by' => $request->user()->id,
                ]);
            }
        });

        return response()->json(['message' => __('design.messages.saved')]);
    }
}
