<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Enquiry;
use App\Models\EnquiryItem;
use App\Services\Workflow;
use Illuminate\Http\Request;

/** Certificate of Analysis — staff enter results per test, then release to the customer */
class CoaController extends Controller
{
    public function edit(Enquiry $enquiry)
    {
        abort_unless($enquiry->isApprovedJob(), 404);
        $coa = Workflow::coaFor($enquiry);
        $enquiry->load('samples.items');

        return view('admin.coa.edit', compact('enquiry', 'coa'));
    }

    public function update(Request $request, Enquiry $enquiry)
    {
        abort_unless($enquiry->isApprovedJob(), 404);
        $coa = Workflow::coaFor($enquiry);

        $data = $request->validate([
            'report_number' => 'required|string|max:50|unique:coa_reports,report_number,'.$coa->id,
            'report_date' => 'required|date',
            'samples_received_date' => 'nullable|date',
            'contact_name' => 'nullable|string|max:191',
            'signatory_name' => 'nullable|string|max:191',
            'signatory_title' => 'nullable|string|max:191',
            'remarks' => 'nullable|string|max:3000',
            'results' => 'array',
            'results.*.result_value' => 'nullable|string|max:100',
            'results.*.result_unit' => 'nullable|string|max:50',
            'results.*.result_method' => 'nullable|string|max:191',
        ]);

        $coa->update(collect($data)->except('results')->all());

        foreach ($data['results'] ?? [] as $itemId => $row) {
            EnquiryItem::where('enquiry_id', $enquiry->id)->whereKey($itemId)->update($row);
        }

        if ($request->input('action') === 'release') {
            return $this->release($request, $enquiry);
        }

        return back()->with('success', 'Results saved.');
    }

    public function release(Request $request, Enquiry $enquiry)
    {
        abort_unless($enquiry->isApprovedJob(), 404);
        Workflow::releaseCoa($enquiry, $request->user());

        return redirect()->route('admin.enquiries.show', $enquiry)->with('success', 'Certificate of Analysis released and emailed to the customer.');
    }
}
