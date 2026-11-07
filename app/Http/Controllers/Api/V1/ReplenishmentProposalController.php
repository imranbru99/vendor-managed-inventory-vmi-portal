<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\ReplenishmentProposal;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ReplenishmentProposalController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $perPage = min((int) $request->integer('per_page', 25), 100);

        $proposals = ReplenishmentProposal::query()
            ->with(['relationship.buyerOrganization:id,name', 'relationship.supplierOrganization:id,name'])
            ->latest('id')
            ->paginate($perPage);

        return response()->json($proposals);
    }

    public function show(ReplenishmentProposal $proposal): JsonResponse
    {
        $proposal->load(['lines.product:id,sku,name', 'relationship.supplierOrganization:id,name']);

        return response()->json(['data' => $proposal]);
    }
}
