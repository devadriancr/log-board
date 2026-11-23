<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\ApplicationRequest;
use App\Models\Application;
use App\Models\Board;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Controllers\HasMiddleware;
use Illuminate\Routing\Controllers\Middleware;
use Illuminate\View\View;

class ApplicationController extends Controller implements HasMiddleware
{
    /**
     * Each action asks for its own permission.
     *
     * @return array<int, Middleware>
     */
    public static function middleware(): array
    {
        return [
            new Middleware('can:applications.view', only: ['index']),
            new Middleware('can:applications.create', only: ['create', 'store']),
            new Middleware('can:applications.update', only: ['edit', 'update']),
            new Middleware('can:applications.delete', only: ['destroy']),
        ];
    }

    /**
     * Display a list of the applications.
     */
    public function index(): View
    {
        $applications = Application::withCount('links')
            ->orderBy('name')
            ->get();

        return view('admin.applications.index', compact('applications'));
    }

    /**
     * Show the form for creating a new application.
     */
    public function create(Request $request): View
    {
        $application = new Application([
            'requires_auth' => false,
            'is_active' => true,
        ]);

        // Opened from the board designer: Cancel and Save go back to that board.
        $designBoard = $request->query('from') === 'design' ? Board::find($request->integer('board')) : null;

        return view('admin.applications.create', compact('application', 'designBoard'));
    }

    /**
     * Store a newly created application.
     */
    public function store(ApplicationRequest $request): RedirectResponse
    {
        (new Application())
            ->fill($request->validated() + ['updated_by' => $request->user()->id])
            ->save();

        $designBoard = $request->input('from') === 'design' ? Board::find($request->integer('board')) : null;

        return ($designBoard ? to_route('admin.boards.design', $designBoard) : $this->toIndex('applications'))
            ->with('success', __('applications.messages.created'));
    }

    /**
     * Show the form for editing the application.
     */
    public function edit(Application $application): View
    {
        return view('admin.applications.edit', compact('application'));
    }

    /**
     * Update the application.
     */
    public function update(ApplicationRequest $request, Application $application): RedirectResponse
    {
        $application
            ->fill($request->validated() + ['updated_by' => $request->user()->id])
            ->save();

        return $this->toIndex('applications')
            ->with('success', __('applications.messages.updated'));
    }

    /**
     * Remove the application.
     */
    public function destroy(Application $application): RedirectResponse
    {
        if ($application->links()->exists()) {
            return $this->toIndex('applications')
                ->with('error', __('applications.messages.has_links', ['name' => $application->name]));
        }

        $application->delete();

        return $this->toIndex('applications')
            ->with('success', __('applications.messages.deleted'));
    }
}
