<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Enquiry;
use App\Models\Invoice;
use App\Services\Documents;
use App\Services\Workflow;
use Illuminate\Http\Request;

class InvoiceController extends Controller
{
    public function index(Request $request)
    {
        $query = Invoice::with('enquiry')->latest('invoice_date')->latest('id');
        if ($status = $request->query('status')) {
            $query->where('status', $status);
        }
        if ($s = trim((string) $request->query('q'))) {
            $query->where(fn ($q) => $q->where('invoice_number', 'like', "%$s%")
                ->orWhere('po_number', 'like', "%$s%")
                ->orWhereHas('enquiry', fn ($e) => $e->where('company_name', 'like', "%$s%")->orWhere('reference', 'like', "%$s%")));
        }

        return view('admin.invoices.index', ['invoices' => $query->paginate(25)->withQueryString()]);
    }

    /** "Generate Invoice" button on a job */
    public function store(Request $request, Enquiry $enquiry)
    {
        if (! $enquiry->isApprovedJob()) {
            return back()->with('error', 'An invoice can only be generated for an approved job.');
        }
        $invoice = Workflow::createInvoice($enquiry, $request->user());

        return redirect()->route('admin.invoices.edit', $invoice)->with('success', 'Draft invoice '.$invoice->invoice_number.' created from the approved quotation. Review it and click "Issue & Email".');
    }

    public function edit(Invoice $invoice)
    {
        $invoice->load('items', 'enquiry');

        return view('admin.invoices.edit', ['invoice' => $invoice]);
    }

    public function update(Request $request, Invoice $invoice)
    {
        if ($invoice->status !== 'draft') {
            return back()->with('error', 'Only draft invoices can be edited. Void it and generate a new one if changes are needed.');
        }

        $data = $request->validate([
            'invoice_date' => 'required|date',
            'currency' => 'required|in:SGD,USD',
            'payment_terms' => 'nullable|string|max:100',
            'po_number' => 'nullable|string|max:100',
            'our_ref' => 'nullable|string|max:191',
            'bill_to' => 'nullable|string|max:2000',
            'discount_percent' => 'required|numeric|min:0|max:100',
            'gst_percent' => 'required|numeric|min:0|max:100',
            'notes' => 'nullable|string|max:2000',
            'items' => 'array',
            'items.*.description' => 'nullable|string|max:191',
            'items.*.quantity' => 'nullable|integer|min:1|max:9999',
            'items.*.rate' => 'nullable|numeric|min:-9999999|max:9999999', // negative allowed for discount lines
        ]);

        $invoice->update(collect($data)->except('items')->all());

        \App\Models\InvoiceItem::where('invoice_id', $invoice->id)->delete();
        $order = 0;
        foreach ($data['items'] ?? [] as $row) {
            if (blank($row['description'] ?? null)) {
                continue;
            }
            $qty = max(1, (int) ($row['quantity'] ?? 1));
            $rate = (float) ($row['rate'] ?? 0);
            $invoice->items()->create([
                'description' => $row['description'],
                'quantity' => $qty,
                'rate' => $rate,
                'amount' => round($qty * $rate, 2),
                'sort_order' => $order++,
            ]);
        }
        $invoice->unsetRelation('items');
        $invoice->recalculate();

        if ($request->input('action') === 'issue') {
            return $this->issue($request, $invoice);
        }

        return back()->with('success', 'Invoice saved.');
    }

    public function issue(Request $request, Invoice $invoice)
    {
        if ($invoice->status !== 'draft') {
            return back()->with('error', 'This invoice has already been issued.');
        }
        Workflow::issueInvoice($invoice, $request->user());

        return redirect()->route('admin.enquiries.show', $invoice->enquiry_id)->with('success', 'Invoice '.$invoice->invoice_number.' issued and emailed to the customer.');
    }

    public function setStatus(Request $request, Invoice $invoice)
    {
        $data = $request->validate(['status' => 'required|in:paid,issued,void']);

        $invoice->forceFill([
            'status' => $data['status'],
            'paid_at' => $data['status'] === 'paid' ? now() : null,
        ])->save();

        if ($data['status'] === 'paid') {
            Workflow::track($invoice->enquiry, 'payment_received', 'Invoice '.$invoice->invoice_number, $request->user(), false);
        }

        return back()->with('success', 'Invoice marked as '.$invoice->statusLabel().'.');
    }

    public function pdf(Invoice $invoice)
    {
        return Documents::invoice($invoice)->stream(Documents::fileName('PacLab_Tax_Invoice', $invoice->invoice_number));
    }
}
