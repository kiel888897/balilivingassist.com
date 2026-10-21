<?php

namespace App\Http\Controllers;

use App\Models\Quotation;
use App\Models\QuotationPaymentProof;
use App\Models\QuotationRevision;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\DB;

class QuotationPaymentProofController extends Controller
{
    public function storeCustomer(Request $request, Quotation $quotation)
    {
        abort_unless(Auth::check() && Auth::user()->hasRole('customer') && !Auth::user()->hasPermissionTo('admin.access'), 403);
        abort_unless((int) $quotation->user_id === (int) $request->user()->id, 404);
        abort_unless($quotation->status === 'payment_requested', 403);

        return $this->store($request, $quotation, 'customer');
    }

    public function storeAdmin(Request $request, Quotation $quotation)
    {
        return $this->store($request, $quotation, 'admin');
    }

    private function store(Request $request, Quotation $quotation, string $source)
    {
        $data = $request->validate([
            'proof' => ['required', 'file', 'mimes:jpg,jpeg,png,webp,pdf', 'max:10240'],
        ]);
        $file = $data['proof'];
        $path = $file->store('quotation-payment-proofs/' . $quotation->id, 'local');
        try {
            DB::transaction(function () use ($quotation, $request, $source, $file, $path) {
                $proof = QuotationPaymentProof::create([
                    'quotation_id' => $quotation->id,
                    'uploaded_by' => $request->user()->id,
                    'source' => $source,
                    'path' => $path,
                    'original_name' => basename($file->getClientOriginalName()),
                    'mime_type' => $file->getMimeType() ?: 'application/octet-stream',
                    'size' => $file->getSize(),
                ]);

                if ($quotation->is_order && $quotation->status === 'payment_requested') {
                    $quotation->status = 'payment_review';
                    $quotation->payment_rejection_reason = null;
                    $quotation->save();
                }

                QuotationRevision::create([
                    'quotation_id' => $quotation->id,
                    'user_id' => $request->user()->id,
                    'event' => 'payment_proof_uploaded',
                    'snapshot' => [
                        'quote_number' => $quotation->quote_number,
                        'status' => $quotation->fresh()->status,
                        'payment_proof_id' => $proof->id,
                        'original_name' => $proof->original_name,
                        'source' => $source,
                    ],
                ]);
            });
        } catch (\Throwable $exception) {
            Storage::disk('local')->delete($path);
            throw $exception;
        }

        $route = $source === 'admin' ? 'admin.quotations.edit' : 'customer.quotations.show';
        return redirect()->route($route, $quotation)->with('status', 'Bukti pembayaran berhasil diunggah.');
    }

    public function download(Request $request, QuotationPaymentProof $proof)
    {
        $this->authorizeProofAccess($request, $proof);

        abort_unless(Storage::disk('local')->exists($proof->path), 404);
        return Storage::disk('local')->download($proof->path, basename($proof->original_name), [
            'Content-Type' => $proof->mime_type,
            'X-Content-Type-Options' => 'nosniff',
        ]);
    }

    public function preview(Request $request, QuotationPaymentProof $proof)
    {
        $this->authorizeProofAccess($request, $proof);
        abort_unless(str_starts_with($proof->mime_type, 'image/'), 404);

        $path = Storage::disk('local')->path($proof->path);
        abort_unless(is_file($path), 404);

        return response()->file($path, [
            'Content-Type' => $proof->mime_type,
            'Cache-Control' => 'private, no-store, max-age=0',
            'X-Content-Type-Options' => 'nosniff',
        ]);
    }

    private function authorizeProofAccess(Request $request, QuotationPaymentProof $proof): void
    {
        $user = $request->user();
        $isAdmin = $user->hasPermissionTo('quotes.manage');
        $isOwner = $user->hasRole('customer')
            && !$user->hasPermissionTo('admin.access')
            && (int) $proof->quotation->user_id === (int) $user->id;
        abort_unless($isAdmin || $isOwner, 403);
    }
}
