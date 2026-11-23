<?php

namespace App\Http\Controllers\Admin;

use App\Enums\LinkStyle;
use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\BoardRequest;
use App\Models\Board;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Routing\Controllers\HasMiddleware;
use Illuminate\Routing\Controllers\Middleware;
use Illuminate\View\View;

class BoardController extends Controller implements HasMiddleware
{
    /**
     * Each action asks for its own permission.
     *
     * @return array<int, Middleware>
     */
    public static function middleware(): array
    {
        return [
            new Middleware('can:boards.view', only: ['index']),
            new Middleware('can:boards.create', only: ['create', 'store']),
            new Middleware('can:boards.update', only: ['edit', 'update']),
            new Middleware('can:boards.delete', only: ['destroy']),
            new Middleware('can:design.update', only: ['reorder']),
        ];
    }

    /**
     * Display a list of the boards.
     */
    public function index(): View
    {
        $boards = Board::withCount('cards')
            ->ordered()
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
            'sort_order' => (Board::max('sort_order') ?? 0) + 1,
            'is_active' => true,
        ]);

        return view('admin.boards.create', compact('board'));
    }

    /**
     * Store a newly created board.
     */
    public function store(BoardRequest $request): RedirectResponse
    {
        $board = new Board();
        $this->save($board, $request);

        $to = $request->input('from') === 'design'
            ? to_route('admin.boards.design', $board)
            : $this->toIndex('boards');

        return $to->with('success', __('boards.messages.created'));
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

        $to = $request->input('from') === 'design'
            ? to_route('admin.boards.design', $board)
            : $this->toIndex('boards');

        return $to->with('success', __('boards.messages.updated'));
    }

    /**
     * Remove the board.
     */
    public function destroy(Request $request, Board $board): RedirectResponse
    {
        if ($board->cards()->exists()) {
            return ($request->input('from') === 'design' ? back() : $this->toIndex('boards'))
                ->with('error', __('boards.messages.has_cards', ['name' => $board->name]));
        }

        $board->delete();

        // From the designer go on to the first board left, if any.
        $next = $request->input('from') === 'design' ? Board::ordered()->first() : null;

        return ($next ? to_route('admin.boards.design', $next) : $this->toIndex('boards'))
            ->with('success', __('boards.messages.deleted'));
    }

    /**
     * Save the order of the tabs. The home board is not part of it: it always goes first.
     */
    public function reorder(Request $request): JsonResponse
    {
        $ids = $request->validate([
            'ids' => ['required', 'array'],
            'ids.*' => ['integer', 'distinct', 'exists:boards,id'],
        ])['ids'];

        DB::transaction(function () use ($ids, $request) {
            foreach ($ids as $index => $id) {
                Board::where('id', $id)->where('is_home', false)
                    ->update(['sort_order' => $index + 1, 'updated_by' => $request->user()->id]);
            }
        });

        $names = Board::whereIn('id', $ids)->pluck('name', 'id');
        activity('design')->event('reordered')->log(collect($ids)->map(fn ($id) => $names[$id])->implode(' › '));

        return response()->json(['message' => __('boards.messages.updated')]);
    }

    /**
     * Save the board, making sure there is only one home board.
     */
    private function save(Board $board, BoardRequest $request): void
    {
        DB::transaction(function () use ($board, $request) {
            $data = $request->validated() + ['updated_by' => $request->user()->id];

            // A new board always goes last; positions are changed by dragging the tabs in the designer.
            if (! $board->exists) {
                $data['sort_order'] = (Board::max('sort_order') ?? 0) + 1;
            }

            if ($data['is_home']) {
                // Not this board: its in-memory is_home is already true, so fill() would not write it back.
                Board::where('is_home', true)
                    ->when($board->exists, fn ($query) => $query->whereKeyNot($board->id))
                    ->update(['is_home' => false]);
            }

            $board->fill($data)->save();
        });
    }
}
