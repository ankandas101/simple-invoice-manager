<?php

namespace App\Http\Controllers;

use App\Models\User;
use Inertia\Inertia;
use App\Models\Field;
use App\Models\Customer;
use App\Http\Requests\UserRequest;
use App\Http\Requests\CustomerRequest;

class CustomerUserController extends Controller
{
    public function editCustomer()
    {
        $asUser = null;
        if (session()->get('impersonate')) {
            $asUser = User::find(session()->get('impersonate'));
        }

        return Inertia::render('Customer/BillingForm', [
            'fields' => Field::ofModel('customer')->get(),
            'edit'   => ['data' => $asUser ? $asUser->customer : auth()->user()?->customer],
        ]);
    }

    public function store(UserRequest $request, Customer $customer)
    {
        $form = $request->validated();
        if ($form['customer_id'] == $customer->id) {
            $user = User::create($form);
            $user->syncRoles(['customer']);

            return back()->with('message', __('{record} has been {action}.', ['record' => 'User', 'action' => 'created']));
        }

        return back()->with('error', __('The record can not be saved.'));
    }

    public function update(UserRequest $request, Customer $customer, User $user)
    {
        $form = $request->validated();
        if ($form['customer_id'] == $customer->id) {
            $user->update($form);
            $user->syncRoles(['customer']);

            return back()->with('message', __('{record} has been {action}.', ['record' => 'User', 'action' => 'updated']));
        }

        return back()->with('error', __('The record can not be saved.'));
    }

    public function updateCustomer(CustomerRequest $request)
    {
        if (session()->get('impersonate')) {
            $asUser = User::find(session()->get('impersonate'));
            $asUser->customer->update($request->validated());

            return back()->with('message', __('{record} has been {action}.', ['record' => __('Details'), 'action' => __('updated')]));
        }
        auth()->user()?->customer->update($request->validated());

        return back()->with('message', __('{record} has been {action}.', ['record' => __('Details'), 'action' => __('updated')]));
    }
}
