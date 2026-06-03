<?php

namespace App\Http\Controllers;

use Inertia\Inertia;
use App\Models\Customer;
use Illuminate\Http\Request;
use Rap2hpoutre\FastExcel\FastExcel;
use Illuminate\Support\Facades\Storage;

class CustomerPortController extends Controller
{
    public function export()
    {
        if (demo()) {
            return back()->with('error', __('This feature is disabled on demo.'));
        }

        return (new FastExcel($this->customerGenerator()))->download('customers.xlsx');
    }

    public function import()
    {
        return Inertia::render('Customer/Import');
    }

    public function save(Request $request)
    {
        $request->validate(['excel' => 'required|file|mimes:xls,xlsx']);
        if (demo()) {
            return back()->with('error', __('This feature is disabled on demo.'));
        }

        $path = $request->file('excel')->store('imports');
        try {
            $columns = ['name', 'email', 'phone', 'company', 'address', 'city', 'postal_code', 'state', 'country'];
            $customers = (new FastExcel())->import(Storage::path($path), function ($line) use ($columns) {
                if (! $line['name'] || (! $line['email'] && ! $line['phone'])) {
                    throw new \Exception(__('name along email or phone are required.'));
                }

                $extra_attributes = [];
                foreach ($line as $key => $value) {
                    if (! in_array($key, $columns)) {
                        $extra_attributes[$key] = $value;
                    }
                }

                return Customer::updateOrCreate([
                    'name'  => $line['name'],
                    'email' => $line['email'] ?? null,
                    'phone' => $line['phone'] ?? null,
                ], [
                    'name'             => $line['name'],
                    'email'            => $line['email'] ?? null,
                    'phone'            => $line['phone'] ?? null,
                    'company'          => $line['company'] ?? null,
                    'address'          => $line['address'] ?? null,
                    'city'             => $line['city'] ?? null,
                    'postal_code'      => $line['postal_code'] ?? null,
                    'state'            => $line['state'] ?? null,
                    'country'          => $line['country'] ?? null,
                    'extra_attributes' => $extra_attributes ?? null,
                ]);
            });
        } catch (\Exception $e) {
            return back()->with('error', $e->getMessage());
        }

        return redirect()->route('customers.index')->with('message', __('{count} {records} record(s) has imported or updated.', ['records' => 'Customer', 'count' => $customers->count()]));
    }

    private function customerGenerator()
    {
        foreach (Customer::cursor() as $customer) {
            $item = [
                'name'        => $customer->name,
                'email'       => $customer->email,
                'phone'       => $customer->phone,
                'company'     => $customer->company,
                'address'     => $customer->address,
                'city'        => $customer->city,
                'postal_code' => $customer->postal_code,
                'state'       => $customer->state,
                'country'     => $customer->country,
            ];

            if ($customer->extra_attributes) {
                foreach ($customer->extra_attributes as $key => $value) {
                    $item[$key] = $value;
                }
            }

            yield $item;
        }
    }
}
