<?php

namespace App\Http\Controllers;

use Inertia\Inertia;
use App\Models\Product;
use App\Models\TaxRate;
use Illuminate\Http\Request;
use Rap2hpoutre\FastExcel\FastExcel;
use Illuminate\Support\Facades\Storage;

class ProductPortController extends Controller
{
    public function export()
    {
        if (demo()) {
            return back()->with('error', __('This feature is disabled on demo.'));
        }

        return (new FastExcel($this->productGenerator()))->download('products.xlsx');
    }

    public function import()
    {
        return Inertia::render('Product/Import');
    }

    public function save(Request $request)
    {
        $request->validate(['excel' => 'required|file|mimes:xls,xlsx']);
        if (demo()) {
            return back()->with('error', __('This feature is disabled on demo.'));
        }

        $path = $request->file('excel')->store('imports');
        try {
            $columns = ['name', 'price', 'details', 'tax_method', 'taxes'];
            $products = (new FastExcel())->import(Storage::path($path), function ($line) use ($columns) {
                if (! $line['name'] || (! $line['price'])) {
                    throw new \Exception(__('name and price are required.'));
                }
                $extra_attributes = [];
                foreach ($line as $key => $value) {
                    if (! in_array($key, $columns)) {
                        $extra_attributes[$key] = $value;
                    }
                }

                $product = Product::updateOrCreate([
                    'name' => $line['name'],
                ], [
                    'name'             => $line['name'],
                    'price'            => $line['price'],
                    'details'          => $line['details'] ?? null,
                    'tax_method'       => $line['tax_method'] ?? null,
                    'extra_attributes' => $extra_attributes ?? null,
                ]);
                if ($line['taxes']) {
                    $taxes = TaxRate::whereIn('name', explode(',', $line['taxes']))->pluck('id')->toArray();
                }
                $product->taxes()->sync($taxes ?? []);

                return $product;
            });
        } catch (\Exception $e) {
            return back()->with('error', $e->getMessage());
        }

        return redirect()->route('products.index')->with('message', __('{count} {records} record(s) has imported or updated.', ['records' => 'Product', 'count' => $products->count()]));
    }

    private function productGenerator()
    {
        foreach (Product::with('taxes')->cursor() as $product) {
            $item = [
                'name'       => $product->name,
                'price'      => $product->price + 0,
                'details'    => $product->details ?? 0,
                'taxes'      => implode(',', $product->taxes?->pluck('name')->all() ?: []),
                'tax_method' => $product->tax_method ?? '',
            ];
            if ($product->extra_attributes) {
                foreach ($product->extra_attributes as $key => $value) {
                    $item[$key] = $value;
                }
            }
            yield $item;
        }
    }
}
