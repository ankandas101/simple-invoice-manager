<?php

namespace App\Http\Controllers;

use Carbon\Carbon;
use App\Models\User;
use Inertia\Inertia;
use App\Models\Company;
use App\Models\Invoice;
use App\Models\Payment;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Http\Resources\InvoiceCollection;
use App\Http\Resources\PaymentCollection;

class ReportController extends Controller
{
    public function dailyInvoices(Request $request, $month = null, $year = null)
    {
        $now = now();
        $month = $month ?: $now->format('n');
        $year = $year ?: $now->format('Y');
        $only_status = $request->get('status');
        $only_paid_invoices = $request->get('only_paid');
        $end_date = Carbon::parse($year . '-' . $month . '-01')->endOfMonth();
        $start_date = Carbon::parse($year . '-' . $month . '-01')->startOfMonth();

        $prev_date = $start_date->copy()->subDays(7);
        $prev_month_link = route('reports.daily_invoices', ['month' => $prev_date->format('m'), 'year' => $prev_date->format('Y')]);
        $next_date = $end_date->copy()->addDays(7);
        $next_month_link = route('reports.daily_invoices', ['month' => $next_date->format('m'), 'year' => $next_date->format('Y')]);

        $data = Invoice::selectRaw(DB::Raw('date, SUM(total) as total, SUM(total_tax_amount) as total_tax_amount, SUM(grand_total) as grand_total, SUM(paid) as paid'))
            ->when($only_status, fn ($q) => $q->where('status', $only_status))
            ->when($only_paid_invoices, fn ($q) => $q->whereColumn('paid', '>=', 'grand_total'))
            ->whereBetween('date', [$start_date, $end_date])->groupBy('date')->orderBy('date')->get()->groupBy(fn ($item) => Carbon::parse($item->date)->format('Y-m-d'))->transform(fn ($item) => $item[0])->all();
        // dd($data->toArray());

        $startOfCalendar = $start_date->copy()->firstOfMonth()->startOfWeek(Carbon::MONDAY);
        $endOfCalendar = $start_date->copy()->lastOfMonth()->endOfWeek(Carbon::SUNDAY);

        $current_month = $start_date->format('Y-m');

        $html = '<div class="calendar">';

        // $html = '<div class="header">';
        // $html .= '<div class="prev-month">' . $prev_month_link . '</div>';
        // $html .= '<div class="month-year">';
        // $html .= '<span class="month">' . $start_date->format('M') . '</span>';
        // $html .= '<span class="year">' . $start_date->format('Y') . '</span>';
        // $html .= '</div>';
        // $html .= '<div class="prev-month">' . $next_month_link . '</div>';
        // $html .= '</div>';

        $html .= '<div class="days">';

        // $dayLabels = ['Sun', 'Mon', 'Tue', 'Wed', 'Thu', 'Fri', 'Sat'];
        $dayLabels = [];
        $wd = Carbon::now()->startOfWeek();
        // dd(Carbon::getLocale(), $wd->getLocale());
        $dayLabels[] = $wd->getTranslatedShortDayName('D');
        // dd($wd->getTranslatedShortDayName('D'));
        for ($i = 0; $i < 6; $i++) {
            $dayLabels[] = $wd->addDay()->getTranslatedShortDayName('D');
        }
        foreach ($dayLabels as $key => $dayLabel) {
            $html .= '<div class="day-label label-' . $key . '">' . $dayLabel . '</div>';
        }

        $r = 1;
        while ($startOfCalendar <= $endOfCalendar) {
            $extraClass = $startOfCalendar->format('m') != $start_date->format('m') ? 'dull' : '';
            $extraClass .= $startOfCalendar->isToday() ? ' today' : '';
            $date = $startOfCalendar->format('Y-m-d');
            $html .= '<div class="day ' . $extraClass . ' day-' . $r . '"><div class="content">' . $startOfCalendar->format('j') . '</div>' . (isset($data[$date]) ? '<div class="amount ' . $date . ' data" data-date="' . $date . '">' . formatNumber($data[$date]['grand_total']) . '</div>' : '') . '</div>';
            $startOfCalendar->addDay();
            $r++;
        }
        $html .= '</div></div>';

        return Inertia::render('Report/DailyInvoices', [
            'html'               => $html,
            'data'               => $data,
            'current_month'      => $current_month,
            'prev_month_link'    => $prev_month_link,
            'next_month_link'    => $next_month_link,
            'only_paid_invoices' => $only_paid_invoices,
            'only_status'        => $only_status,
        ]);
    }

    public function invoices(Request $request)
    {
        $filters = $request->all('search', 'recurring', 'start_date', 'end_date', 'status', 'trashed', 'company', 'customer', 'user', 'product', 'fields');
        $data = Invoice::selectRaw(DB::Raw('COUNT(id) as count, SUM(total_tax_amount) as total_tax_amount, SUM(grand_total) as total, SUM(paid) as paid'))->filter($filters)->first();

        return Inertia::render('Report/Invoices', [
            'data'      => $data,
            'filters'   => $filters,
            'users'     => User::get(['id as value', 'name as label']),
            'companies' => Company::get(['id as value', 'name as label']),
            'invoices'  => new InvoiceCollection(
                Invoice::with(['company', 'customer', 'items', 'user'])->filter($filters)->orderByDesc('date')->latest()->paginate()->onEachSide(2)->withQueryString()
            ),
        ]);
    }

    public function monthlyInvoices(Request $request, $year = null)
    {
        $now = now();
        $year = $year ?: $now->format('Y');
        $only_status = $request->get('status');
        $only_paid_invoices = $request->get('only_paid');
        $end_date = Carbon::parse($year . '-01-01')->endOfYear();
        $start_date = Carbon::parse($year . '-12-01')->startOfYear();

        $prev_date = $start_date->copy()->subDays(7);
        $prev_year_link = route('reports.monthly_invoices', ['year' => $prev_date->format('Y')]);
        $next_date = $end_date->copy()->addDays(7);
        $next_year_link = route('reports.monthly_invoices', ['year' => $next_date->format('Y')]);

        $data = Invoice::selectRaw(DB::Raw('MONTH(date) as month, YEAR(date) as year, SUM(total) as total, SUM(total_tax_amount) as total_tax_amount, SUM(grand_total) as grand_total, SUM(paid) as paid'))
            ->when($only_status, fn ($q) => $q->where('status', $only_status))
            ->when($only_paid_invoices, fn ($q) => $q->whereColumn('paid', '>=', 'grand_total'))
            ->whereBetween('date', [$start_date, $end_date])
            ->groupBy(DB::raw('YEAR(date), MONTH(date)'))
            ->orderBy('year')->orderBy('month')->get()
            ->groupBy(fn ($item) => $item->month)
            ->transform(fn ($item) => $item[0])->all();
        // dd($data->toArray());

        $startOfCalendar = $start_date->copy();
        $endOfCalendar = $end_date->copy();
        $current_year = $start_date->format('Y');

        $html = '<div class="calendar">';

        // $html = '<div class="header">';
        // $html .= '<div class="prev-month">' . $prev_month_link . '</div>';
        // $html .= '<div class="month-year">';
        // $html .= '<span class="month">' . $start_date->format('M') . '</span>';
        // $html .= '<span class="year">' . $start_date->format('Y') . '</span>';
        // $html .= '</div>';
        // $html .= '<div class="prev-month">' . $next_month_link . '</div>';
        // $html .= '</div>';

        $html .= '<div class="months">';

        // $monthLabels = ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun', 'Jul', 'Aug', 'Sep', 'Oct', 'Nov', 'Dec'];
        // foreach ($monthLabels as $key => $monthLabel) {
        //     $html .= '<div class="month-label label-' . $key . '">' . $monthLabel . '</div>';
        // }

        $r = 1;
        while ($startOfCalendar <= $endOfCalendar) {
            $extraClass = $startOfCalendar->isCurrentMonth() ? ' current-month' : '';
            $month = $startOfCalendar->format('n');
            $html .= '<div class="month ' . $extraClass . ' month-' . $r . '"><div class="content">' . $startOfCalendar->getTranslatedShortMonthName('M') . '</div>' . (isset($data[$month]) ? '<div class="amount ' . $startOfCalendar->format('m') . ' data" data-month="' . $month . '">' . formatNumber($data[$month]['grand_total']) . '</div>' : '') . '</div>';
            $startOfCalendar->addMonth();
            $r++;
        }
        $html .= '</div></div>';

        return Inertia::render('Report/MonthlyInvoices', [
            'html'               => $html,
            'data'               => $data,
            'current_year'       => $current_year,
            'prev_year_link'     => $prev_year_link,
            'next_year_link'     => $next_year_link,
            'only_paid_invoices' => $only_paid_invoices,
            'only_status'        => $only_status,
        ]);
    }

    public function payments(Request $request)
    {
        $filters = $request->all('search', 'start_date', 'end_date', 'method', 'trashed', 'company', 'customer', 'user', 'invoice');
        $data = Payment::selectRaw(DB::Raw('COUNT(id) as count, SUM(amount) as amount'))->filter($filters)->first();

        return Inertia::render('Report/Payments', [
            'data'      => $data,
            'filters'   => $filters,
            'users'     => User::get(['id as value', 'name as label']),
            'companies' => Company::get(['id as value', 'name as label']),
            'payments'  => new PaymentCollection(
                Payment::with(['company', 'customer', 'user:id,name,email', 'invoice:id,reference'])->filter($filters)->latest()->paginate()->onEachSide(2)->withQueryString()
            ),
        ]);
    }
}
