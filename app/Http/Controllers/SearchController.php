<?php

namespace App\Http\Controllers;

use App\Models\Note;
use App\Models\Product;
use App\Models\Customer;
use Illuminate\Http\Request;

class SearchController extends Controller
{
    public function customers(Request $request)
    {
        if ($request->id) {
            return Customer::selectRaw('id as value, CASE WHEN company IS NOT NULL THEN company ELSE name END as label')->where('id', $request->id)->get();
        }

        return Customer::selectRaw('id as value, CASE WHEN company IS NOT NULL THEN company ELSE name END as label')->search($request->search)->take(10)->get();
    }

    public function notes(Request $request)
    {
        if ($request->id) {
            return Note::where('id', $request->id)->get(['id as value', 'name as label', 'details']);
        }

        return Note::search($request->search)->take(10)->get(['id as value', 'name as label', 'details']);
    }

    public function products(Request $request)
    {
        if ($request->id) {
            return Product::where('id', $request->id)->with('taxes')->get(['id as value', 'name as label', 'details']);
        }

        return Product::search($request->search)->with('taxes')->take(10)->get(['id as value', 'name as label', 'id', 'name', 'tax_method', 'details', 'price']);
    }
}
