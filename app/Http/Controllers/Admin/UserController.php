<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\UserRequest;
use App\Models\Application;
use App\Models\Board;
use App\Models\Card;
use App\Models\Link;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Routing\Controllers\HasMiddleware;
use Illuminate\Routing\Controllers\Middleware;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class UserController extends Controller implements HasMiddleware
{
    /**
     * Each action asks for its own permission.
     *
     * @return array<int, Middleware>
     */
    public static function middleware(): array
    {
        return [
            new Middleware('can:users.view', only: ['index']),
            new Middleware('can:users.create', only: ['create', 'store']),
            new Middleware('can:users.update', only: ['edit', 'update']),
            new Middleware('can:users.delete', only: ['destroy']),
        ];
    }

    /**
     * Display a list of the users.
     */
    public function index(): View
    {
        $users = User::with('roles')->orderBy('name')->get();

        return view('admin.users.index', compact('users'));
    }

    /**
     * Show the form for creating a new user.
     */
    public function create(): View
    {
        $user = new User(['is_active' => true]);

        return view('admin.users.create', ['user' => $user, 'roles' => UserRequest::assignableRoles(), 'role' => 'user']);
    }

    /**
     * Store a newly created user.
     */
    public function store(UserRequest $request): RedirectResponse
    {
        $user = DB::transaction(function () use ($request) {
            $user = User::create($request->safe()->only(['name', 'email', 'password', 'is_active']));
            $user->syncRoles($request->validated('role'));

            return $user;
        });

        $this->logChanges($user, [], ['role' => $this->roleLabel($request->validated('role'))]);

        return to_route('admin.users.index')->with('success', __('users.messages.created'));
    }

    /**
     * Show the form for editing the user.
     */
    public function edit(User $user): View
    {
        $this->guard($user);

        return view('admin.users.edit', ['user' => $user, 'roles' => UserRequest::assignableRoles(), 'role' => $user->roles->first()?->name]);
    }

    /**
     * Update the user. The password is only changed when a new one is typed.
     */
    public function update(UserRequest $request, User $user): RedirectResponse
    {
        $this->guard($user);

        $role = $request->validated('role');

        // Nobody can lock themselves out: the own role and the own access stay as they are.
        if ($user->is($request->user()) && ($role !== $user->roles->first()?->name || ! $request->boolean('is_active'))) {
            return back()->withInput()->with('error', __('users.messages.self'));
        }

        $data = $request->safe()->only(['name', 'email', 'is_active']);

        if ($request->filled('password')) {
            $data['password'] = $request->validated('password');
        }

        $old = [];
        $new = [];

        if ($role !== $user->roles->first()?->name) {
            $old['role'] = $this->roleLabel($user->roles->first()?->name);
            $new['role'] = $this->roleLabel($role);
        }

        if ($request->filled('password')) {
            $old['password'] = '••••••';
            $new['password'] = '••••••';
        }

        DB::transaction(function () use ($user, $data, $role) {
            $user->update($data);
            $user->syncRoles($role);
        });

        $this->logChanges($user, $old, $new);

        return to_route('admin.users.index')->with('success', __('users.messages.updated'));
    }

    /**
     * Remove the user.
     */
    public function destroy(User $user): RedirectResponse
    {
        $this->guard($user);

        if ($user->is(request()->user())) {
            return to_route('admin.users.index')->with('error', __('users.messages.self_delete'));
        }

        // The boards, cards, links and applications remember who changed them last: such a user can only be deactivated.
        $hasRecords = collect([Board::class, Card::class, Link::class, Application::class])
            ->contains(fn (string $model) => $model::where('updated_by', $user->id)->exists());

        if ($hasRecords) {
            return to_route('admin.users.index')->with('error', __('users.messages.has_records', ['name' => $user->name]));
        }

        $user->delete();

        return to_route('admin.users.index')->with('success', __('users.messages.deleted'));
    }

    /**
     * Only a super admin can see or change another super admin.
     */
    private function guard(User $user): void
    {
        abort_if($user->isSuperAdmin() && ! request()->user()->isSuperAdmin(), 403);
    }

    private function roleLabel(?string $role): string
    {
        return $role ? __('roles.names.'.$role) : '—';
    }

    /**
     * Write in the log what the model log cannot see: the role and the password.
     *
     * @param  array<string, string>  $old
     * @param  array<string, string>  $new
     */
    private function logChanges(User $user, array $old, array $new): void
    {
        if ($new === []) {
            return;
        }

        activity('users')->performedOn($user)->event('updated')
            ->withProperties(['old' => $old, 'attributes' => $new])
            ->log($user->name);
    }
}
