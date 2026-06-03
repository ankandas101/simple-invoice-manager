<?php

namespace App\Http\Controllers;

use App\Models\Role;
use Inertia\Inertia;
use App\Models\Permission;
use Illuminate\Http\Request;
use App\Http\Requests\RoleRequest;
use App\Http\Resources\RoleResource;
use App\Http\Resources\RoleCollection;

class RoleController extends Controller
{
    public function create()
    {
        return Inertia::render('Role/Form');
    }

    public function destroy(Request $request, Role $role)
    {
        if (demo()) {
            return back()->with('error', __('This feature is disabled on demo.'));
        }

        if ($role->{$request->force ? 'forceDelete' : 'delete'}()) {
            return redirect()->route('roles.index')->with('message', __('{record} has been {action}.', ['record' => 'Role', 'action' => 'deleted']));
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
        foreach (Role::whereIn('id', $request->selection)->get() as $role) {
            $role->{$request->force ? 'forceDelete' : 'delete'}() ? $count++ : $failed--;
        }

        return back()->with('message', __('The task has completed, {count} deleted and {failed} failed.', ['count' => $count, 'failed' => $failed]));
    }

    public function destroyPermanently(Role $role)
    {
        if ($role->forceDelete()) {
            return redirect()->route('roles.index')->with('message', __('{record} has been {action}.', ['record' => 'Role', 'action' => 'permanently deleted']));
        }

        return back()->with('error', __('The record can not be deleted.'));
    }

    public function edit(Role $role)
    {
        if ($role->name == 'admin') {
            return redirect()->route('roles.index')->with('error', __('Admin role can not be modified.'));
        }

        return Inertia::render('Role/Form', ['edit' => new RoleResource($role)]);
    }

    public function index(Request $request)
    {
        return Inertia::render('Role/List', [
            'filters' => $request->all('search', 'trashed'),
            'roles'   => new RoleCollection(
                Role::ofAccount()->orderBy('name')->filter($request->only('search', 'trashed'))->paginate()->onEachSide(2)
            ),
        ]);
    }

    public function restore(Role $role)
    {
        $role->restore();

        return back()->with('message', __('{record} has been {action}.', ['record' => 'Role', 'action' => 'restored']));
    }

    public function store(RoleRequest $request)
    {
        Role::create($request->validated());

        return redirect()->route('roles.index')->with('message', __('{record} has been {action}.', ['record' => 'Role', 'action' => 'created']));
    }

    public function update(RoleRequest $request, Role $role)
    {
        if ($role->name == 'admin') {
            return redirect()->route('roles.index')->with('error', __('Admin role can not be modified.'));
        }

        $role->update($request->validated());
        $permissions = collect($request->permissions)->flatten()->all();
        foreach ($permissions as $permission) {
            Permission::updateOrCreate(['name' => $permission, 'account_id' => $role->account_id, 'guard_name' => 'web'], ['name' => $permission, 'account_id' => $role->account_id, 'guard_name' => 'web']);
        }
        $role->syncPermissions($permissions);

        return back()->with('message', __('{record} has been {action}.', ['record' => 'Role', 'action' => 'updated']));
    }
}
