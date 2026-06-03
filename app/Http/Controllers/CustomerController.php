<?php

namespace App\Http\Controllers;

use Inertia\Inertia;
use App\Models\Field;
use App\Models\Customer;
use Illuminate\Http\Request;
use App\Http\Requests\CustomerRequest;
use App\Http\Resources\CustomerCollection;

class CustomerController extends Controller
{
    public function create()
    {
        return Inertia::render('Customer/Form', ['fields' => Field::ofModel('customer')->get()]);
    }

    public function destroy(Request $request, Customer $customer)
    {
        if ($customer->{$request->force ? 'forceDelete' : 'delete'}()) {
            return redirect()->route('customers.index')->with('message', __('{record} has been {action}.', ['record' => __('Customer'), 'action' => __('deleted')]));
        }

        return back()->with('error', __('The record can not be deleted.'));
    }

    public function destroyMany(Request $request)
    {
        $count = 0;
        $failed = count($request->selection);
        foreach (Customer::whereIn('id', $request->selection)->get() as $customer) {
            $customer->{$request->force ? 'forceDelete' : 'delete'}() ? $count++ : $failed--;
        }

        return back()->with('message', __('The task has completed, {count} deleted and {failed} failed.', ['count' => $count, 'failed' => $failed]));
    }

    public function destroyPermanently(Customer $customer)
    {
        if ($customer->forceDelete()) {
            return redirect()->route('customers.index')->with('message', __('{record} has been {action}.', ['record' => __('Customer'), 'action' => __('permanently deleted')]));
        }

        return back()->with('error', __('The record can not be deleted.'));
    }

    public function edit(Customer $customer)
    {
        return Inertia::render('Customer/Form', [
            'edit'   => ['data' => $customer],
            'fields' => Field::ofModel('customer')->get(),
        ]);
    }

    public function index(Request $request)
    {
        $filters = $request->all('search', 'trashed');

        return Inertia::render('Customer/List', [
            'filters'   => $filters,
            'customers' => new CustomerCollection(
                Customer::with('users')->filter($filters)->latest()->paginate()->onEachSide(2)->withQueryString()
            ),
        ]);
    }

    public function restore(Customer $customer)
    {
        $customer->restore();

        return back()->with('message', __('{record} has been {action}.', ['record' => __('Customer'), 'action' => __('restored')]));
    }

    public function store(CustomerRequest $request)
    {
        $customer = Customer::create($request->validated());
        if ($request->is_ajax == 'yes') {
            return response()->json($customer);
        }

        return redirect()->route('customers.index')->with('message', __('{record} has been {action}.', ['record' => __('Customer'), 'action' => __('created')]));
    }

    public function update(CustomerRequest $request, Customer $customer)
    {
        $customer->update($request->validated());

        return redirect()->route('customers.index')->with('message', __('{record} has been {action}.', ['record' => __('Customer'), 'action' => __('updated')]));
    }
}
