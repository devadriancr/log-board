<?php

namespace App\Http\Controllers;

use App\Models\Board;
use Illuminate\View\View;

class PublicBoardController extends Controller
{
    /**
     * Show the home board, or the first active one when none is marked as home.
     */
    public function index(): View
    {
        $board = Board::where('is_active', true)
            ->ordered()
            ->first();

        return $this->render($board);
    }

    /**
     * Show the board with the given slug.
     */
    public function show(Board $board): View
    {
        abort_unless($board->is_active, 404);

        return $this->render($board);
    }

    /**
     * Build the page with the active tabs and the cards that have something to show.
     */
    private function render(?Board $board): View
    {
        $boards = Board::where('is_active', true)
            ->ordered()
            ->get(['id', 'name', 'slug', 'is_home']);

        $cards = $board?->visibleCards() ?? collect();

        return view('public.board', compact('board', 'boards', 'cards'));
    }
}
