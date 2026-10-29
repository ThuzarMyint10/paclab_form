<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Enquiry;
use App\Models\Invoice;

class DashboardController extends Controller
{
    public function index()
    {
        $counts = Enquiry::query()->toBase()->selectRaw('status, count(*) as cnt')->groupBy('status')->pluck('cnt', 'status')->map(fn ($n) => (int) $n);

        return view('admin.dashboard', [
            'counts' => $counts,
            'newEnquiries' => Enquiry::where('status', 'submitted')->oldest()->limit(10)->get(),
            'awaitingSamples' => Enquiry::where('status', 'approved')->latest('responded_at')->limit(10)->get(),
            'inLab' => Enquiry::whereIn('status', ['sample_received', 'in_progress', 'on_hold'])->latest('updated_at')->limit(10)->get(),
            'toInvoice' => Enquiry::whereIn('status', ['completed', 'reported', 'dispatched'])
                ->whereDoesntHave('invoices', fn ($q) => $q->where('status', '!=', 'void'))->limit(10)->get(),
            'unpaid' => Invoice::where('status', 'issued')->with('enquiry')->oldest('invoice_date')->limit(10)->get(),
        ]);
    }
}
