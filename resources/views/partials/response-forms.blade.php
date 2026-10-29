{{-- Approve / Decline — used on the emailed link page and in the customer portal --}}
<div class="row g-3">
    <div class="col-lg-7">
        <div class="card h-100 border border-success">
            <div class="card-header text-success">✔ Approve this quotation</div>
            <div class="card-body">
                <form method="POST" action="{{ $approveUrl }}" enctype="multipart/form-data">
                    @csrf
                    <div class="mb-3">
                        <label class="form-label required">Approved by (your name)</label>
                        <input name="approved_by_name" class="form-control" value="{{ old('approved_by_name', $enquiry->contact_person) }}" required>
                    </div>
                    <div class="row g-2 mb-3">
                        <div class="col-sm-6">
                            <label class="form-label">Your PO number (optional)</label>
                            <input name="po_number" class="form-control" value="{{ old('po_number') }}">
                        </div>
                        <div class="col-sm-6">
                            <label class="form-label">Upload PO (PDF/JPG, optional)</label>
                            <input type="file" name="po_document" class="form-control" accept=".pdf,.jpg,.jpeg,.png">
                        </div>
                    </div>
                    <div class="form-check mb-3">
                        <input class="form-check-input" type="checkbox" name="accept_terms" value="1" id="accept_terms" required>
                        <label class="form-check-label small" for="accept_terms">
                            I confirm that I am authorised to approve this quotation of <strong>{{ $enquiry->money($enquiry->total) }}</strong>
                            and accept the payment terms ({{ $enquiry->payment_terms ?: 'as agreed' }}).
                        </label>
                    </div>
                    <button class="btn btn-green">Approve &amp; get Sample Submission Form</button>
                </form>
            </div>
        </div>
    </div>
    <div class="col-lg-5">
        <div class="card h-100 border border-danger-subtle">
            <div class="card-header text-danger">✖ Decline</div>
            <div class="card-body">
                <form method="POST" action="{{ $declineUrl }}" onsubmit="return confirm('Decline this quotation? This enquiry will be closed.')">
                    @csrf
                    <label class="form-label">Reason (optional)</label>
                    <textarea name="decline_reason" class="form-control mb-3" rows="4" placeholder="e.g. price, timing, no longer required">{{ old('decline_reason') }}</textarea>
                    <button class="btn btn-outline-danger">Decline quotation</button>
                </form>
            </div>
        </div>
    </div>
</div>
