<?php

namespace App\Http\Controllers;

use Inertia\Inertia;
use App\Models\Field;
use App\Models\Product;
use App\Models\TaxRate;
use Illuminate\Http\Request;
use App\Http\Requests\ProductRequest;
use App\Http\Resources\ProductCollection;

class ProductController extends Controller
{
    public function create()
    {
        return Inertia::render('Product/Form', [
            'fields'    => Field::ofModel('product')->get(),
            'tax_rates' => TaxRate::all(['id as value', 'name as label']),
        ]);
    }

    public function destroy(Request $request, Product $product)
    {
        if ($product->{$request->force ? 'forceDelete' : 'delete'}()) {
            return redirect()->route('products.index')->with('message', __('{record} has been {action}.', ['record' => __('Product'), 'action' => __('deleted')]));
        }

        return back()->with('error', __('The record can not be deleted.'));
    }

    public function destroyMany(Request $request)
    {
        $count = 0;
        $failed = count($request->selection);
        foreach (Product::withTrashed()->whereIn('id', $request->selection)->get() as $product) {
            $product->{$request->force ? 'forceDelete' : 'delete'}() ? $count++ : $failed--;
        }

        return back()->with('message', __('The task has completed, {count} deleted and {failed} failed.', ['count' => $count, 'failed' => $failed]));
    }

    public function destroyPermanently(Product $product)
    {
        if ($product->forceDelete()) {
            return redirect()->route('products.index')->with('message', __('{record} has been {action}.', ['record' => __('Product'), 'action' => __('permanently deleted')]));
        }

        return back()->with('error', __('The record can not be deleted.'));
    }

    public function edit(Product $product)
    {
        return Inertia::render('Product/Form', [
            'fields'    => Field::ofModel('product')->get(),
            'edit'      => ['data' => $product->load('taxes')],
            'tax_rates' => TaxRate::all(['id as value', 'name as label']),
        ]);
    }

    public function index(Request $request)
    {
        $filters = $request->all('search', 'trashed');

        return Inertia::render('Product/List', [
            'filters'  => $filters,
            'products' => new ProductCollection(
                Product::with('taxes')->filter($filters)->latest()->paginate()->onEachSide(2)->withQueryString()
            ),
        ]);
    }

    public function restore(Product $product)
    {
        $product->restore();

        return back()->with('message', __('{record} has been {action}.', ['record' => __('Product'), 'action' => __('restored')]));
    }

    public function store(ProductRequest $request)
    {
        $product = Product::create($request->validated());
        $product->taxes()->attach($request->input('taxes'));

        return redirect()->route('products.index')->with('message', __('{record} has been {action}.', ['record' => __('Product'), 'action' => __('created')]));
    }

    public function update(ProductRequest $request, Product $product)
    {
        $product->update($request->validated());
        $product->taxes()->sync($request->input('taxes'));

        return redirect()->route('products.index')->with('message', __('{record} has been {action}.', ['record' => __('Product'), 'action' => __('updated')]));
    }
}
