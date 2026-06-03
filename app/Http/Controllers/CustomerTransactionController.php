<?php

namespace App\Http\Controllers;

use Inertia\Inertia;
use App\Models\Customer;
use Illuminate\Http\Request;

class CustomerTransactionController extends Controller
{
    public function index(Request $request, Customer $customer)
    {
        $filters = $request->all('search', 'trashed');

        return Inertia::render('Customer/Transactions', [
            'filters'      => $filters,
            'customer'     => $customer,
            'transactions' => $customer->transactions()->filter($filters)->orderByDesc('id')->paginate()->onEachSide(2)->withQueryString(),
        ]);
    }
}
