<?php

namespace App\Http\Controllers;

use App\Models\Role;
use App\Models\User;
use Inertia\Inertia;
use App\Models\Field;
use Illuminate\Http\Request;
use App\Http\Requests\UserRequest;
use App\Http\Resources\UserCollection;

class UserController extends Controller
{
    public function create()
    {
        return Inertia::render('User/Form', [
            'roles'  => Role::ofAccount()->get(),
            'fields' => Field::ofModel('user')->get(),
        ]);
    }

    public function destroy(Request $request, User $user)
    {
        if ($user->id == auth()->id()) {
            return back()->with('error', __('You should not delete your own account.'));
        }

        if (demo()) {
            return back()->with('error', __('This feature is disabled on demo.'));
        }

        if ($user->{$request->force ? 'forceDelete' : 'delete'}()) {
            return redirect()->route('users.index')->with('message', __('{record} has been {action}.', ['record' => 'User', 'action' => 'deleted']));
        }

        return back()->with('error', __('The record can not be deleted.'));
    }

    public function destroyMany(Request $request)
    {
        if (demo()) {
            return back()->with('error', __('This feature is disabled on demo.'));
        }

        $count = 0;
        $failed = count($request->selection);
        foreach (User::whereIn('id', $request->selection)->get() as $user) {
            $user->{$request->force ? 'forceDelete' : 'delete'}() ? $count++ : $failed--;
        }

        return back()->with('message', __('The task has completed, {count} deleted and {failed} failed.', ['count' => $count, 'failed' => $failed]));
    }

    public function destroyPermanently(User $user)
    {
        if ($user->id == auth()->id()) {
            return back()->with('error', __('You should not delete your own account.'));
        }

        if ($user->forceDelete()) {
            return redirect()->route('users.index')->with('message', __('{record} has been {action}.', ['record' => 'User', 'action' => 'deleted']));
        }

        return back()->with('error', __('The record can not be deleted.'));
    }

    public function disable2FA(User $user)
    {
        $user->forceFill(['two_factor_secret' => null, 'two_factor_recovery_codes' => null])->save();

        return back()->with('message', __('{record} has been {action}.', ['record' => 'Tow factor authentication', 'action' => 'disabled']));
    }

    public function edit(User $user)
    {
        return Inertia::render('User/Form', [
            'edit'   => ['data' => $user],
            'roles'  => Role::ofAccount()->get(),
            'fields' => Field::ofModel('user')->get(),
        ]);
    }

    public function index(Request $request)
    {
        $filters = $request->all('search', 'role', 'trashed');

        return Inertia::render('User/List', [
            'filters' => $filters,
            'roles'   => Role::ofAccount()->get(),
            'users'   => new UserCollection(User::ofAccount()->orderBy('name')->filter($filters)->paginate()->onEachSide(2)),
        ]);
    }

    public function restore(User $user)
    {
        $user->restore();

        return back()->with('message', __('{record} has been {action}.', ['record' => 'User', 'action' => 'restored']));
    }

    public function store(UserRequest $request)
    {
        $user = User::create($request->validated());
        $user->assignRole($request->input('roles'));

        return redirect()->route('users.index')->with('message', __('{record} has been {action}.', ['record' => 'User', 'action' => 'created']));
    }

    public function update(UserRequest $request, User $user)
    {
        if ($user->id == auth()->id()) {
            return back()->with('error', __('You should not update your own account.'));
        }
        if (demo() && $user->id == 1) {
            return back()->with('error', 'This feature is disabled on demo');
        }

        $user->update($request->validated());
        $user->syncRoles($request->input('roles'));

        return back()->with('message', __('{record} has been {action}.', ['record' => 'User', 'action' => 'updated']));
    }
}
