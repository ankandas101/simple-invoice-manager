<?php

namespace App\Http\Controllers;

use App\Models\Note;
use Inertia\Inertia;
use Illuminate\Http\Request;
use App\Http\Requests\NoteRequest;
use App\Http\Resources\NoteResource;
use App\Http\Resources\NoteCollection;

class NoteController extends Controller
{
    public function create()
    {
        return Inertia::render('Note/Form');
    }

    public function destroy(Request $request, Note $note)
    {
        if ($note->{$request->force ? 'forceDelete' : 'delete'}()) {
            return redirect()->route('notes.index')->with('message', __('{record} has been {action}.', ['record' => __('Note'), 'action' => __('deleted')]));
        }

        return back()->with('error', __('The record can not be deleted.'));
    }

    public function destroyMany(Request $request)
    {
        $count = 0;
        $failed = count($request->selection);
        foreach (Note::whereIn('id', $request->selection)->get() as $note) {
            $note->{$request->force ? 'forceDelete' : 'delete'}() ? $count++ : $failed--;
        }

        return back()->with('message', __('The task has completed, {count} deleted and {failed} failed.', ['count' => $count, 'failed' => $failed]));
    }

    public function destroyPermanently(Note $note)
    {
        if ($note->forceDelete()) {
            return redirect()->route('notes.index')->with('message', __('{record} has been {action}.', ['record' => __('Note'), 'action' => __('permanently deleted')]));
        }

        return back()->with('error', __('The record can not be deleted.'));
    }

    public function edit(Note $note)
    {
        return Inertia::render('Note/Form', [
            'edit' => new NoteResource($note),
        ]);
    }

    public function index(Request $request)
    {
        $filters = $request->all('search', 'trashed');

        return Inertia::render('Note/List', [
            'filters' => $filters,
            'notes'   => new NoteCollection(
                Note::filter($filters)->orderByDesc('id')->paginate()->onEachSide(2)->withQueryString()
            ),
        ]);
    }

    public function restore(Note $note)
    {
        $note->restore();

        return back()->with('message', __('{record} has been {action}.', ['record' => __('Note'), 'action' => __('restored')]));
    }

    public function store(NoteRequest $request)
    {
        Note::create($request->validated());

        return redirect()->route('notes.index')->with('message', __('{record} has been {action}.', ['record' => __('Note'), 'action' => __('created')]));
    }

    public function update(NoteRequest $request, Note $note)
    {
        $note->update($request->validated());

        return redirect()->route('notes.index')->with('message', __('{record} has been {action}.', ['record' => __('Note'), 'action' => __('updated')]));
    }
}
