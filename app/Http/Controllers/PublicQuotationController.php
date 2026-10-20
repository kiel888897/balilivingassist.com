<?php

namespace App\Http\Controllers;

use App\Models\Quotation;

class PublicQuotationController extends Controller
{
    public function preview(string $token)
    {
        $quotation = Quotation::where('public_token', $token)->whereNotNull('public_token')->with('items')->firstOrFail();

        return view('quotations.preview', [
            'quotation' => $quotation,
            'documentType' => 'quotation',
        ]);
    }

    public function invoice(string $token)
    {
        $quotation = Quotation::where('public_token', $token)->whereNotNull('public_token')->with('items')->firstOrFail();
        abort_unless($quotation->hasPaidInvoice(), 404);

        return view('quotations.preview', [
            'quotation' => $quotation,
            'documentType' => 'invoice',
        ]);
    }
}
