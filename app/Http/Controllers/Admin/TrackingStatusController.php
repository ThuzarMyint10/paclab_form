<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Enquiry;
use App\Models\TrackingStatus;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

/** The pre-described words staff pick from when updating a job's status */
class TrackingStatusController extends Controller
{
    protected function rules(): array
    {
        return [
            'label' => 'required|string|max:191',
            'customer_message' => 'nullable|string|max:191',
            'sets_status' => 'nullable|in:'.implode(',', array_keys(Enquiry::STATUSES)),
            'sort_order' => 'nullable|integer|min:0',
        ];
    }

    public function index()
    {
        return view('admin.tracking-statuses.index', ['statuses' => TrackingStatus::orderBy('sort_order')->get()]);
    }

    public function create()
    {
        return view('admin.tracking-statuses.form', ['status' => new TrackingStatus(['is_manual' => true, 'notify_customer' => true, 'is_active' => true, 'sort_order' => (int) TrackingStatus::max('sort_order') + 1])]);
    }

    public function store(Request $request)
    {
        $data = $request->validate($this->rules());
        TrackingStatus::create($data + [
            'code' => Str::slug($data['label'], '_').'_'.Str::lower(Str::random(4)),
            'is_manual' => true,
            'notify_customer' => $request->boolean('notify_customer'),
            'is_active' => $request->boolean('is_active'),
        ]);

        return redirect()->route('admin.tracking-statuses.index')->with('success', 'Tracking status added.');
    }

    public function edit(TrackingStatus $trackingStatus)
    {
        return view('admin.tracking-statuses.form', ['status' => $trackingStatus]);
    }

    public function update(Request $request, TrackingStatus $trackingStatus)
    {
        $data = $request->validate($this->rules());
        if (! $trackingStatus->is_manual) {
            unset($data['sets_status']); // system steps keep their behaviour
        }
        $trackingStatus->update($data + [
            'notify_customer' => $request->boolean('notify_customer'),
            'is_active' => $trackingStatus->is_manual ? $request->boolean('is_active') : true,
        ]);

        return redirect()->route('admin.tracking-statuses.index')->with('success', 'Tracking status updated.');
    }

    public function destroy(TrackingStatus $trackingStatus)
    {
        if (! $trackingStatus->is_manual) {
            return back()->with('error', 'System statuses cannot be removed.');
        }
        $trackingStatus->update(['is_active' => false]);

        return back()->with('success', 'Tracking status hidden from the list.');
    }
}
