@extends('layouts.admin')
@section('title', 'Settings')
@section('content')
<h3 class="text-navy mb-3">Settings</h3>
@php
    $groups = [
        'Company details (printed on documents)' => [
            'company_name' => 'Company name', 'trading_name' => 'Trading name', 'tagline' => 'Tagline', 'address' => 'Address|textarea',
            'phone' => 'Telephone', 'fax' => 'Fax', 'email' => 'Email', 'website' => 'Website',
            'company_reg_no' => 'Company Reg No', 'gst_reg_no' => 'GST Reg No',
        ],
        'Email notifications' => [
            'admin_emails' => 'Staff emails that receive new-enquiry and approval/decline alerts (comma separated)',
        ],
        'Quotation & pricing' => [
            'quotation_valid_days' => 'Quotation valid for (days)', 'urgent_surcharge_percent' => 'Urgent surcharge %',
            'default_payment_terms' => 'Default payment terms', 'quotation_notes' => 'Standard quotation notes|textarea',
            'sample_dispatch_instructions' => 'Instructions sent with the Sample Submission Form|textarea',
        ],
        'Invoice' => [
            'gst_percent' => 'GST % (applied to Singapore customers)', 'bank_details' => 'Payment / bank details|textarea',
        ],
        'Certificate of Analysis' => [
            'coa_signatory_name' => 'Signatory name', 'coa_signatory_title' => 'Signatory title', 'coa_disclaimer' => 'Disclaimer|textarea',
        ],
        'Running numbers' => [
            'next_enquiry_number' => 'Next enquiry number', 'quotation_number_format' => 'Quotation format ({n} = number, {Y} = year)',
            'next_quotation_number' => 'Next quotation number', 'invoice_number_prefix' => 'Invoice prefix', 'next_invoice_number' => 'Next invoice number',
            'lab_code_prefix' => 'Lab sample code prefix', 'next_lab_code' => 'Next lab sample code',
        ],
    ];
@endphp
<form method="POST" action="{{ route('admin.settings.update') }}">
    @csrf @method('PUT')
    <div class="row g-4">
        @foreach ($groups as $title => $fields)
            <div class="col-xl-6">
                <div class="card h-100">
                    <div class="card-header">{{ $title }}</div>
                    <div class="card-body">
                        @foreach ($fields as $key => $label)
                            @php [$text, $type] = array_pad(explode('|', $label), 2, 'text'); @endphp
                            <div class="mb-2">
                                <label class="form-label small">{{ $text }}</label>
                                @if ($type === 'textarea')
                                    <textarea name="settings[{{ $key }}]" rows="{{ in_array($key, ['coa_disclaimer', 'bank_details']) ? 5 : 3 }}" class="form-control form-control-sm">{{ old('settings.'.$key, $s[$key]) }}</textarea>
                                @else
                                    <input name="settings[{{ $key }}]" value="{{ old('settings.'.$key, $s[$key]) }}" class="form-control form-control-sm">
                                @endif
                            </div>
                        @endforeach
                    </div>
                </div>
            </div>
        @endforeach
    </div>
    <button class="btn btn-green mt-4">Save settings</button>
</form>
@endsection
