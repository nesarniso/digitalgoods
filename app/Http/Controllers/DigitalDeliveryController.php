<?php

namespace App\Http\Controllers;

use App\Domain\Digital\Models\DigitalLicenseKey;
use App\Domain\Digital\Services\DigitalLicenseService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\URL;
use Barryvdh\DomPDF\Facade\Pdf;

class DigitalDeliveryController extends Controller
{
    public function access(Request $request, DigitalLicenseKey $license, DigitalLicenseService $service)
    {
        abort_unless($request->hasValidSignature(), 403, 'This digital access link is invalid or expired.');
        $this->assertCustomerBinding($request, $license);

        $secret = $service->access($license);

        return view('digital.access', [
            'license' => $license->load('product'),
            'secret' => $secret,
            'instructions' => $license->instructions,
            'downloadUrl' => URL::temporarySignedRoute('digital.download', now()->addDays(7), array_filter([
                'license' => $license->id,
                'customer' => $license->order?->user_id,
            ])),
        ]);
    }

    public function download(Request $request, DigitalLicenseKey $license, DigitalLicenseService $service)
    {
        abort_unless($request->hasValidSignature(), 403, 'This digital download link is invalid or expired.');
        $this->assertCustomerBinding($request, $license);

        $secret = $service->access($license);
        $license->load('product');

        return Pdf::loadView('digital.pdf', [
            'license' => $license,
            'secret' => $secret,
            'instructions' => $license->instructions,
        ])->download('digital-license-'.$license->reference.'.pdf');
    }

    private function assertCustomerBinding(Request $request, DigitalLicenseKey $license): void
    {
        if (! $request->query->has('customer')) {
            return;
        }

        abort_unless(
            $request->user() && hash_equals((string) $request->query('customer'), (string) $request->user()->id)
                && $license->order?->user_id === $request->user()->id,
            403,
            'This digital access link is not assigned to this customer.'
        );
    }
}
