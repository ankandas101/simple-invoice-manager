<?php

namespace App\Http\Controllers;

use Inertia\Inertia;
use App\Models\Field;
use Illuminate\Http\Request;
use App\Http\Requests\FieldRequest;
use App\Http\Resources\FieldResource;
use App\Http\Resources\FieldCollection;

class FieldController extends Controller
{
    public function create()
    {
        return Inertia::render('Field/Form');
    }

    public function destroy(Request $request, Field $field)
    {
        if ($field->{$request->force ? 'forceDelete' : 'delete'}()) {
            return redirect()->route('fields.index')->with('message', __('{record} has been {action}.', ['record' => __('Field'), 'action' => __('deleted')]));
        }

        return back()->with('error', __('The record can not be deleted.'));
    }

    public function destroyMany(Request $request)
    {
        $count = 0;
        $failed = count($request->selection);
        foreach (Field::whereIn('id', $request->selection)->get() as $field) {
            $field->{$request->force ? 'forceDelete' : 'delete'}() ? $count++ : $failed--;
        }

        return back()->with('message', __('The task has completed, {count} deleted and {failed} failed.', ['count' => $count, 'failed' => $failed]));
    }

    public function destroyPermanently(Field $field)
    {
        if ($field->forceDelete()) {
            return redirect()->route('fields.index')->with('message', __('{record} has been {action}.', ['record' => __('Field'), 'action' => __('permanently deleted')]));
        }

        return back()->with('error', __('The record can not be deleted.'));
    }

    public function edit(Field $field)
    {
        return Inertia::render('Field/Form', [
            'edit' => new FieldResource($field),
        ]);
    }

    public function index(Request $request)
    {
        $filters = $request->all('search', 'trashed');

        return Inertia::render('Field/List', [
            'filters' => $filters,
            'fields'  => new FieldCollection(
                Field::filter($filters)->orderByDesc('id')->paginate()->onEachSide(2)->withQueryString()
            ),
        ]);
    }

    public function restore(Field $field)
    {
        $field->restore();

        return back()->with('message', __('{record} has been {action}.', ['record' => __('Field'), 'action' => __('restored')]));
    }

    public function store(FieldRequest $request)
    {
        Field::create($request->validated());

        return redirect()->route('fields.index')->with('message', __('{record} has been {action}.', ['record' => __('Field'), 'action' => __('created')]));
    }

    public function update(FieldRequest $request, Field $field)
    {
        $field->update($request->validated());

        return redirect()->route('fields.index')->with('message', __('{record} has been {action}.', ['record' => __('Field'), 'action' => __('updated')]));
    }
}
