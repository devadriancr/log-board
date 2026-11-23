<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Routing\Controllers\HasMiddleware;
use Illuminate\Routing\Controllers\Middleware;
use Illuminate\View\View;
use Spatie\Activitylog\Models\Activity;

class ActivityController extends Controller implements HasMiddleware
{
    /**
     * @return array<int, Middleware>
     */
    public static function middleware(): array
    {
        return [new Middleware('can:activity.view')];
    }

    /**
     * Display the log, newest first, with an optional filter by section and by user.
     */
    public function index(Request $request): View
    {
        $sections = __('activity.sections');

        $activities = Activity::with('causer')
            ->when(array_key_exists((string) $request->query('section'), $sections),
                fn ($query) => $query->where('log_name', $request->query('section')))
            ->when($request->filled('user'),
                fn ($query) => $query->where('causer_type', (new User)->getMorphClass())->where('causer_id', $request->integer('user')))
            ->latest('id')
            ->paginate(25)
            ->withQueryString();

        $users = User::orderBy('name')->pluck('name', 'id');

        return view('admin.activity.index', compact('activities', 'sections', 'users'));
    }
}
