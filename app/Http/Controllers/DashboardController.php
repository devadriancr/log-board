<?php

namespace App\Http\Controllers;

use App\Models\Board;
use Illuminate\View\View;

class DashboardController extends Controller
{
    /**
     * Show a preview of what the public sees: every active board with its cards.
     */
    public function __invoke(): View
    {
        $boards = Board::where('is_active', true)
            ->ordered()
            ->get()
            ->each(fn (Board $board) => $board->setRelation('cards', $board->visibleCards()));

        return view('dashboard', compact('boards'));
    }
}
