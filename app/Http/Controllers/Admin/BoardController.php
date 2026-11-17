<?php

namespace App\Http\Controllers\Admin;

use App\Enums\LinkStyle;
use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\BoardRequest;
use App\Models\Board;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class BoardController extends Controller
{
    /**
     * Display a list of the boards.
     */
    public function index(): View
    {
        $boards = Board::withCount('cards')
            ->orderBy('sort_order')
            ->orderBy('name')
            ->get();

        return view('admin.boards.index', compact('boards'));
    }

    /**
     * Show the form for creating a new board.
     */
    public function create(): View
    {
        $board = new Board([
            'link_style' => LinkStyle::Button,
            'sort_order' => 0,
            'is_active' => true,
        ]);

        return view('admin.boards.create', compact('board'));
    }

    /**
     * Store a newly created board.
     */
    public function store(BoardRequest $request): RedirectResponse
    {
        $this->save(new Board(), $request);

        return to_route('admin.boards.index')
            ->with('success', __('boards.messages.created'));
    }

    /**
     * Show the form for editing the board.
     */
    public function edit(Board $board): View
    {
        return view('admin.boards.edit', compact('board'));
    }

    /**
     * Update the board.
     */
    public function update(BoardRequest $request, Board $board): RedirectResponse
    {
        $this->save($board, $request);

        return to_route('admin.boards.index')
            ->with('success', __('boards.messages.updated'));
    }

    /**
     * Remove the board.
     */
    public function destroy(Board $board): RedirectResponse
    {
        if ($board->cards()->exists()) {
            return to_route('admin.boards.index')
                ->with('error', __('boards.messages.has_cards', ['name' => $board->name]));
        }

        $board->delete();

        return to_route('admin.boards.index')
            ->with('success', __('boards.messages.deleted'));
    }

    /**
     * Save the board, making sure there is only one home board.
     */
    private function save(Board $board, BoardRequest $request): void
    {
        DB::transaction(function () use ($board, $request) {
            $data = $request->validated() + ['updated_by' => $request->user()->id];

            if ($data['is_home']) {
                Board::where('is_home', true)->update(['is_home' => false]);
            }

            $board->fill($data)->save();
        });
    }
}
