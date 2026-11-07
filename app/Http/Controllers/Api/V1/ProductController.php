<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\Product;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ProductController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $perPage = min((int) $request->integer('per_page', 25), 100);

        $products = Product::query()
            ->with(['category:id,name', 'baseUom:id,code,name'])
            ->orderBy('sku')
            ->paginate($perPage);

        return response()->json($products);
    }

    public function show(Product $product): JsonResponse
    {
        $product->load(['category:id,name', 'baseUom:id,code,name']);

        return response()->json(['data' => $product]);
    }
}
