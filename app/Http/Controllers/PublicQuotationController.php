<?php

namespace App\Http\Controllers;

use App\Models\Enquiry;
use App\Services\Documents;
use App\Services\Workflow;
use Illuminate\Http\Request;

/**
 * The link emailed with the quotation. The long random token is the key,
 * so the customer can approve or decline without logging in.
 */
class PublicQuotationController extends Controller
{
    protected function find(string $token): Enquiry
    {
        abort_if(strlen($token) < 40, 404);

        return Enquiry::where('access_token', $token)->with('samples.items')->firstOrFail();
    }

    public function show(string $token)
    {
        $enquiry = $this->find($token);
        abort_if(in_array($enquiry->status, ['submitted', 'cancelled'], true), 404);

        return view('public.quotation', ['enquiry' => $enquiry]);
    }

    public function approve(Request $request, string $token)
    {
        $enquiry = $this->find($token);
        if (! $enquiry->canRespond()) {
            return back()->with('error', 'This quotation can no longer be approved. Please contact Pacific Lab.');
        }

        $data = $request->validate([
            'approved_by_name' => 'required|string|max:191',
            'po_number' => 'nullable|string|max:100',
            'po_document' => 'nullable|file|mimes:pdf,jpg,jpeg,png|max:10240',
            'accept_terms' => 'accepted',
        ]);
        if ($request->hasFile('po_document')) {
            $data['po_file'] = $request->file('po_document')->store('po-documents/'.$enquiry->id);
        }

        Workflow::approve($enquiry, $data);

        return redirect()->route('quotation.public', $token)
            ->with('success', 'Thank you! Your quotation is approved. Your Sample Submission Form has been emailed to you and can be downloaded below.');
    }

    public function decline(Request $request, string $token)
    {
        $enquiry = $this->find($token);
        if (! $enquiry->canRespond()) {
            return back()->with('error', 'This quotation can no longer be changed.');
        }
        $data = $request->validate(['decline_reason' => 'nullable|string|max:1000']);

        Workflow::decline($enquiry, $data['decline_reason'] ?? null);

        return redirect()->route('quotation.public', $token)->with('success', 'Your response has been recorded. Thank you for considering Pacific Lab Services.');
    }

    public function document(string $token, string $doc)
    {
        $enquiry = $this->find($token);

        if ($doc === 'ssf') {
            abort_unless($enquiry->ssf_number && $enquiry->isApprovedJob(), 404);

            return Documents::ssf($enquiry)->download(Documents::fileName('PacLab_Sample_Submission_Form', $enquiry->ssf_number));
        }

        abort_unless($enquiry->quotation_number, 404);

        return Documents::quotation($enquiry)->download(Documents::fileName('PacLab_Quotation', $enquiry->quotation_number));
    }
}
