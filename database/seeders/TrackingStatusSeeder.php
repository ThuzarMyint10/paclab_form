<?php

namespace Database\Seeders;

use App\Models\TrackingStatus;
use Illuminate\Database\Seeder;

class TrackingStatusSeeder extends Seeder
{
    public function run(): void
    {
        // [code, label, customer message, sets enquiry status, selectable by staff, email customer]
        $rows = [
            // --- automatic (system) steps ---
            ['enquiry_received', 'Enquiry Received', 'We have received your enquiry and will send you a quotation shortly.', 'submitted', false, false],
            ['quotation_issued', 'Quotation Issued', 'Your quotation is ready for your review.', 'quoted', false, false],
            ['quotation_approved', 'Quotation Approved', 'Thank you for approving the quotation.', 'approved', false, false],
            ['quotation_declined', 'Quotation Declined', 'The quotation was declined. This enquiry is now closed.', 'declined', false, true],
            ['awaiting_samples', 'Awaiting Samples', 'Please send your samples together with the signed Sample Submission Form.', 'approved', false, false],
            ['report_issued', 'Report (COA) Issued', 'Your Certificate of Analysis has been issued.', 'reported', false, false],
            ['invoice_issued', 'Invoice Issued', 'Your tax invoice has been issued.', null, false, false],
            ['payment_received', 'Payment Received', 'Thank you — your payment has been received.', null, false, false],
            // --- pre-described words staff choose from ---
            ['received_courier', 'Sample Received – via Courier', 'Your samples have arrived at our laboratory by courier.', 'sample_received', true, true],
            ['received_post', 'Sample Received – via Post', 'Your samples have arrived at our laboratory by post.', 'sample_received', true, true],
            ['received_hand', 'Sample Received – Hand Delivered / Walk-in', 'Your samples have been received at our laboratory.', 'sample_received', true, true],
            ['sample_registered', 'Sample Checked & Registered', 'Your samples have been checked and registered in our system.', null, true, false],
            ['testing_started', 'Testing Started – In Progress', 'Analysis of your samples has started.', 'in_progress', true, true],
            ['subcontracted', 'Sent to Sub-contract Laboratory', 'Part of the analysis is being performed by our partner laboratory.', 'in_progress', true, false],
            ['testing_completed', 'Testing Completed', 'Analysis of your samples is complete. Your report is being prepared.', 'completed', true, true],
            ['sample_sent', 'Sample Sent / Dispatched', 'Your samples / documents have been dispatched.', 'dispatched', true, true],
            ['on_hold', 'On Hold – Awaiting Customer Information', 'Your job is on hold — we need more information from you. Our team will contact you.', 'on_hold', true, true],
            ['job_closed', 'Job Closed', 'This job has been completed and closed. Thank you for choosing Pacific Lab Services.', 'closed', true, false],
        ];

        foreach ($rows as $i => [$code, $label, $message, $sets, $manual, $notify]) {
            TrackingStatus::updateOrCreate(['code' => $code], [
                'label' => $label,
                'customer_message' => $message,
                'sets_status' => $sets,
                'is_manual' => $manual,
                'notify_customer' => $notify,
                'is_active' => true,
                'sort_order' => ($i + 1) * 10,
            ]);
        }
    }
}
