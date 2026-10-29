<?php

namespace App\Http\Controllers\Portal;

use App\Http\Controllers\Controller;
use App\Models\Enquiry;
use App\Models\Invoice;
use App\Services\Documents;
use App\Services\Workflow;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules\Password as PasswordRule;

/**
 * Customer area. Every query is scoped to the logged-in customer,
 * so a customer can only ever open their own enquiries and documents.
 */
class PortalController extends Controller
{
    protected function own(Request $request, Enquiry $enquiry): Enquiry
    {
        abort_unless((int) $enquiry->customer_id === (int) $request->user()->id, 404);

        return $enquiry;
    }

    public function dashboard(Request $request)
    {
        $user = $request->user();
        $query = $user->enquiries()->withCount('items')->latest();

        if ($s = trim((string) $request->query('q'))) {
            $query->where(fn ($q) => $q->where('reference', 'like', "%$s%")
                ->orWhere('quotation_number', 'like', "%$s%")
                ->orWhere('ssf_number', 'like', "%$s%"));
        }

        $all = $user->enquiries()->get(['status']);

        return view('portal.dashboard', [
            'enquiries' => $query->paginate(15)->withQueryString(),
            'counts' => [
                'awaiting' => $all->where('status', 'quoted')->count(),
                'active' => $all->whereIn('status', ['approved', 'sample_received', 'in_progress', 'on_hold'])->count(),
                'done' => $all->whereIn('status', ['completed', 'reported', 'dispatched', 'closed'])->count(),
            ],
            'invoices' => Invoice::whereHas('enquiry', fn ($q) => $q->where('customer_id', $user->id))
                ->whereIn('status', ['issued', 'paid'])->latest('invoice_date')->limit(10)->get(),
        ]);
    }

    public function show(Request $request, Enquiry $enquiry)
    {
        $this->own($request, $enquiry)->load([
            'samples.items',
            'trackingUpdates' => fn ($q) => $q->where('visible_to_customer', true),
            'invoices' => fn ($q) => $q->whereIn('status', ['issued', 'paid']),
            'coa',
        ]);

        return view('portal.enquiry', ['enquiry' => $enquiry]);
    }

    public function approve(Request $request, Enquiry $enquiry)
    {
        $this->own($request, $enquiry);
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

        return back()->with('success', 'Quotation approved. Your Sample Submission Form is ready — please print it and send it with your samples.');
    }

    public function decline(Request $request, Enquiry $enquiry)
    {
        $this->own($request, $enquiry);
        if (! $enquiry->canRespond()) {
            return back()->with('error', 'This quotation can no longer be changed.');
        }
        $data = $request->validate(['decline_reason' => 'nullable|string|max:1000']);
        Workflow::decline($enquiry, $data['decline_reason'] ?? null);

        return back()->with('success', 'Your response has been recorded.');
    }

    public function document(Request $request, Enquiry $enquiry, string $doc)
    {
        $this->own($request, $enquiry);

        return match ($doc) {
            'quotation' => $enquiry->quotation_number
                ? Documents::quotation($enquiry)->download(Documents::fileName('PacLab_Quotation', $enquiry->quotation_number))
                : abort(404),
            'ssf' => $enquiry->ssf_number && $enquiry->isApprovedJob()
                ? Documents::ssf($enquiry)->download(Documents::fileName('PacLab_Sample_Submission_Form', $enquiry->ssf_number))
                : abort(404),
            'coa' => $enquiry->coa && $enquiry->coa->isReleased()
                ? Documents::coa($enquiry)->download(Documents::fileName('PacLab_COA', $enquiry->coa->report_number))
                : abort(404),
        };
    }

    public function invoicePdf(Request $request, Invoice $invoice)
    {
        abort_unless($invoice->enquiry && (int) $invoice->enquiry->customer_id === (int) $request->user()->id && $invoice->isVisibleToCustomer(), 404);

        return Documents::invoice($invoice)->download(Documents::fileName('PacLab_Tax_Invoice', $invoice->invoice_number));
    }

    public function profile(Request $request)
    {
        return view('portal.profile', ['user' => $request->user()]);
    }

    public function updateProfile(Request $request)
    {
        $user = $request->user();
        $data = $request->validate([
            'name' => 'required|string|max:191',
            'company_name' => 'nullable|string|max:191',
            'phone' => 'nullable|string|max:50',
            'current_password' => 'nullable|required_with:password|current_password',
            'password' => ['nullable', 'confirmed', PasswordRule::min(8)],
        ]);

        $user->fill(collect($data)->only('name', 'company_name', 'phone')->all());
        if (! empty($data['password'])) {
            $user->password = Hash::make($data['password']);
        }
        $user->save();

        return back()->with('success', 'Your profile has been updated.');
    }
}
