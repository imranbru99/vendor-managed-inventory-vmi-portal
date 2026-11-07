<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\InventoryBalance;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class InventoryController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $perPage = min((int) $request->integer('per_page', 25), 100);

        $balances = InventoryBalance::query()
            ->with(['product:id,sku,name', 'warehouse:id,code,name'])
            ->orderByDesc('updated_at')
            ->paginate($perPage);

        return response()->json($balances);
    }
}
