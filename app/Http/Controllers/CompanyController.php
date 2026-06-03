<?php

namespace App\Http\Controllers;

use Inertia\Inertia;
use App\Models\Field;
use App\Models\Company;
use Illuminate\Http\Request;
use App\Http\Requests\CompanyRequest;
use App\Http\Resources\CompanyCollection;

class CompanyController extends Controller
{
    public function create()
    {
        return Inertia::render('Company/Form', ['fields' => Field::ofModel('company')->get()]);
    }

    public function destroy(Request $request, Company $company)
    {
        if ($company->{$request->force ? 'forceDelete' : 'delete'}()) {
            return redirect()->route('companies.index')->with('message', __('{record} has been {action}.', ['record' => __('Company'), 'action' => __('deleted')]));
        }

        return back()->with('error', __('The record can not be deleted.'));
    }

    public function destroyMany(Request $request)
    {
        $count = 0;
        $failed = count($request->selection);
        foreach (Company::whereIn('id', $request->selection)->get() as $company) {
            $company->{$request->force ? 'forceDelete' : 'delete'}() ? $count++ : $failed--;
        }

        return back()->with('message', __('The task has completed, {count} deleted and {failed} failed.', ['count' => $count, 'failed' => $failed]));
    }

    public function destroyPermanently(Company $company)
    {
        if ($company->forceDelete()) {
            return redirect()->route('companies.index')->with('message', __('{record} has been {action}.', ['record' => __('Company'), 'action' => __('permanently deleted')]));
        }

        return back()->with('error', __('The record can not be deleted.'));
    }

    public function edit(Company $company)
    {
        return Inertia::render('Company/Form', [
            'edit'   => ['data' => $company],
            'fields' => Field::ofModel('company')->get(),
        ]);
    }

    public function index(Request $request)
    {
        $filters = $request->all('search', 'trashed');

        return Inertia::render('Company/List', [
            'filters'   => $filters,
            'companies' => new CompanyCollection(
                Company::filter($filters)->latest()->paginate()->onEachSide(2)->withQueryString()
            ),
        ]);
    }

    public function restore(Company $company)
    {
        $company->restore();

        return back()->with('message', __('{record} has been {action}.', ['record' => __('Company'), 'action' => __('restored')]));
    }

    public function store(CompanyRequest $request)
    {
        Company::create($request->validated());

        return redirect()->route('companies.index')->with('message', __('{record} has been {action}.', ['record' => __('Company'), 'action' => __('created')]));
    }

    public function update(CompanyRequest $request, Company $company)
    {
        $company->update($request->validated());

        return redirect()->route('companies.index')->with('message', __('{record} has been {action}.', ['record' => __('Company'), 'action' => __('updated')]));
    }
}
