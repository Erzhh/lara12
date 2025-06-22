<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Product;
use Illuminate\Http\Request;

class ProductController extends Controller
{
    public function index(Request $request)
    {
        $search   = $request->input('search');
        $products = Product::search($search)->paginate(10);

        return response()->json($products);
    }
}
