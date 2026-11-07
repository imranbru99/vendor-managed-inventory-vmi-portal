<?php

namespace App\Services\Documents;

use App\Models\PurchaseOrder;
use Illuminate\Support\Facades\View;
use ImranDev\UnicodePdf\Facades\UnicodePdf;
use Symfony\Component\HttpFoundation\StreamedResponse;

class DocumentPdfService
{
    public function purchaseOrderStream(PurchaseOrder $purchaseOrder): StreamedResponse|string
    {
        $purchaseOrder->load(['lines.product:id,sku,name', 'relationship.supplierOrganization:id,name']);

        if (! class_exists(UnicodePdf::class)) {
            return View::make('documents.purchase-order', ['purchaseOrder' => $purchaseOrder])->render();
        }

        $pdf = UnicodePdf::loadView('documents.purchase-order', [
            'purchaseOrder' => $purchaseOrder,
        ])->a4();

        return $pdf->stream("purchase-order-{$purchaseOrder->po_number}.pdf");
    }
}
