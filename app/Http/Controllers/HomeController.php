<?php

namespace App\Http\Controllers;

use Inertia\Inertia;
use App\Models\Invoice;
use App\Models\Payment;
use App\Models\Product;
use App\Models\Activity;
use App\Models\Customer;
use App\Models\Quotation;
use Illuminate\Http\Request;
use App\Http\Resources\ActivityCollection;

class HomeController extends Controller
{
    public function activity(Request $request)
    {
        return Inertia::render('Activity/List', [
            'filters'    => $request->all('search'),
            'activities' => new ActivityCollection(Activity::filter($request->only('search'))->orderByDesc('id')->paginate()->onEachSide(2)),
        ]);
    }

    public function index()
    {
        $last = now()->subDays(60);
        $before = now()->subDays(30);
        $data = [];
        $data = Product::selectRaw('COUNT(*) as items')
            ->addSelect(['invoices' => Invoice::selectRaw('SUM(grand_total) as invoices')
                ->whereBetween('date', [$before, now()])])
            ->addSelect(['payments' => Payment::selectRaw('SUM(amount) as payments')
                ->whereBetween('date', [$before, now()])])
            ->addSelect(['quotations' => Quotation::selectRaw('COUNT(id) as quotations')
                ->whereBetween('date', [$before, now()])])
            ->addSelect(['previous_invoices' => Invoice::selectRaw('SUM(grand_total) as invoices')
                ->whereBetween('date', [$last, $before])])
            ->addSelect(['previous_payments' => Payment::selectRaw('SUM(amount) as payments')
                ->whereBetween('date', [$last, $before])])
            ->addSelect(['previous_quotations' => Quotation::selectRaw('COUNT(id) as quotations')
                ->whereBetween('date', [$last, $before])])
            ->addSelect(['customers' => Customer::selectRaw('COUNT(id) as customers')])
            ->addSelect(['total' => Invoice::selectRaw('COUNT(id) as invoices')])
            ->addSelect(['paid' => Invoice::selectRaw('COUNT(id) as invoices')->where('status', 'paid')])
            ->addSelect(['pending' => Invoice::selectRaw('COUNT(id) as invoices')->where('status', 'pending')])
            ->addSelect(['overdue' => Invoice::selectRaw('COUNT(id) as invoices')->where('status', 'overdue')])
            ->addSelect(['canceled' => Invoice::selectRaw('COUNT(id) as invoices')->where('status', 'canceled')])
            ->first();

        return Inertia::render('Dashboard', ['data' => $data]);
    }
}
