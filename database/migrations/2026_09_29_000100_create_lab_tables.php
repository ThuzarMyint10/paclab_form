<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Key/value settings editable from Admin > Settings
        Schema::create('settings', function (Blueprint $table) {
            $table->id();
            $table->string('key')->unique();
            $table->text('value')->nullable();
            $table->timestamps();
        });

        // Standard (pre-approved) price list — PACLAB Pricing 2026
        Schema::create('lab_tests', function (Blueprint $table) {
            $table->id();
            $table->string('category');
            $table->string('name');
            $table->string('matrix')->nullable();
            $table->string('method')->nullable();
            $table->decimal('price_sgd', 10, 2)->default(0);
            $table->decimal('price_usd', 10, 2)->default(0);
            $table->boolean('is_active')->default(true);
            $table->unsignedInteger('sort_order')->default(0);
            $table->timestamps();
            $table->index(['category', 'sort_order']);
        });

        // Pre-described tracking words staff choose from
        Schema::create('tracking_statuses', function (Blueprint $table) {
            $table->id();
            $table->string('code')->unique();
            $table->string('label');
            $table->string('customer_message')->nullable();
            $table->string('sets_status', 30)->nullable(); // enquiry status this update moves the job to
            $table->boolean('is_manual')->default(true);   // selectable by staff (system ones are automatic)
            $table->boolean('notify_customer')->default(true);
            $table->boolean('is_active')->default(true);
            $table->unsignedInteger('sort_order')->default(0);
            $table->timestamps();
        });

        Schema::create('enquiries', function (Blueprint $table) {
            $table->id();
            $table->string('reference')->unique();           // ENQ-2026-00001
            $table->string('access_token', 64)->unique();    // secure link for the customer (quotation / tracking)
            $table->foreignId('customer_id')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('company_id')->nullable()->constrained()->nullOnDelete();
            $table->string('status', 30)->default('submitted');

            // Customer details (as submitted)
            $table->string('company_name');
            $table->string('contact_person');
            $table->string('email');
            $table->string('phone')->nullable();
            $table->string('mobile')->nullable();
            $table->string('fax')->nullable();
            $table->text('address')->nullable();
            $table->string('country')->nullable();
            $table->string('currency', 3)->default('SGD');
            $table->string('turnaround', 20)->default('standard'); // standard | urgent
            $table->text('special_instructions')->nullable();

            // Section II – reporting / invoice address (if different)
            $table->string('report_name')->nullable();
            $table->string('report_company')->nullable();
            $table->text('report_address')->nullable();
            $table->string('invoice_name')->nullable();
            $table->string('invoice_company')->nullable();
            $table->text('invoice_address')->nullable();

            // Quotation
            $table->string('quotation_number')->nullable()->unique();
            $table->date('quotation_date')->nullable();
            $table->date('quotation_valid_until')->nullable();
            $table->decimal('subtotal', 12, 2)->default(0);
            $table->decimal('surcharge_percent', 5, 2)->default(0);
            $table->decimal('surcharge_amount', 12, 2)->default(0);
            $table->decimal('discount_percent', 5, 2)->default(0);
            $table->decimal('discount_amount', 12, 2)->default(0);
            $table->decimal('total', 12, 2)->default(0);
            $table->string('payment_terms')->nullable();
            $table->text('quotation_notes')->nullable();
            $table->text('internal_notes')->nullable();
            $table->foreignId('priced_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('quoted_at')->nullable();

            // Customer response
            $table->timestamp('responded_at')->nullable();
            $table->text('decline_reason')->nullable();
            $table->string('po_number')->nullable();
            $table->string('po_file')->nullable();
            $table->string('approved_by_name')->nullable();

            // Sample submission form
            $table->string('ssf_number')->nullable()->unique(); // 2026-09-15-001
            $table->timestamp('ssf_generated_at')->nullable();
            $table->string('courier_name')->nullable();
            $table->string('courier_tracking_no')->nullable();
            $table->timestamp('samples_received_at')->nullable();
            $table->timestamp('completed_at')->nullable();

            $table->timestamps();
            $table->index(['status', 'created_at']);
        });

        Schema::create('enquiry_samples', function (Blueprint $table) {
            $table->id();
            $table->foreignId('enquiry_id')->constrained()->cascadeOnDelete();
            $table->string('description');                 // client sample ID / description
            $table->string('sample_type')->nullable();     // e.g. Feed, Premix, Pure material
            $table->string('batch_no')->nullable();
            $table->date('production_date')->nullable();
            $table->unsignedInteger('quantity')->default(1); // total number of samples
            $table->string('storage')->nullable();
            $table->string('lab_code')->nullable();        // assigned by PacLab on receipt, e.g. AJ12176
            $table->unsignedInteger('sort_order')->default(0);
            $table->timestamps();
        });

        Schema::create('enquiry_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('enquiry_id')->constrained()->cascadeOnDelete();
            $table->foreignId('enquiry_sample_id')->nullable()->constrained()->cascadeOnDelete();
            $table->foreignId('lab_test_id')->nullable()->constrained()->nullOnDelete();
            $table->string('test_name');
            $table->string('matrix')->nullable();
            $table->string('method')->nullable();
            $table->decimal('list_price', 10, 2)->default(0);  // from standard price list
            $table->decimal('unit_price', 10, 2)->default(0);  // price offered (staff may amend)
            $table->unsignedInteger('quantity')->default(1);
            $table->decimal('amount', 12, 2)->default(0);
            // COA result fields
            $table->string('result_value')->nullable();
            $table->string('result_unit')->nullable();
            $table->string('result_method')->nullable();
            $table->unsignedInteger('sort_order')->default(0);
            $table->timestamps();
        });

        Schema::create('tracking_updates', function (Blueprint $table) {
            $table->id();
            $table->foreignId('enquiry_id')->constrained()->cascadeOnDelete();
            $table->foreignId('tracking_status_id')->nullable()->constrained()->nullOnDelete();
            $table->string('label');
            $table->text('remarks')->nullable();
            $table->boolean('visible_to_customer')->default(true);
            $table->boolean('customer_notified')->default(false);
            $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete();
            $table->timestamps();
        });

        Schema::create('invoices', function (Blueprint $table) {
            $table->id();
            $table->foreignId('enquiry_id')->constrained()->cascadeOnDelete();
            $table->string('invoice_number')->unique();
            $table->date('invoice_date');
            $table->string('status', 20)->default('draft'); // draft | issued | paid | void
            $table->string('currency', 3);
            $table->string('payment_terms')->nullable();
            $table->string('po_number')->nullable();
            $table->string('our_ref')->nullable();
            $table->text('bill_to')->nullable();
            $table->decimal('total', 12, 2)->default(0);
            $table->decimal('discount_percent', 5, 2)->default(0);
            $table->decimal('discount_amount', 12, 2)->default(0);
            $table->decimal('subtotal', 12, 2)->default(0);
            $table->decimal('gst_percent', 5, 2)->default(0);
            $table->decimal('gst_amount', 12, 2)->default(0);
            $table->decimal('grand_total', 12, 2)->default(0);
            $table->text('notes')->nullable();
            $table->timestamp('issued_at')->nullable();
            $table->timestamp('paid_at')->nullable();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
        });

        Schema::create('invoice_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('invoice_id')->constrained()->cascadeOnDelete();
            $table->string('description');
            $table->unsignedInteger('quantity')->default(1);
            $table->decimal('rate', 10, 2)->default(0);
            $table->decimal('amount', 12, 2)->default(0);
            $table->unsignedInteger('sort_order')->default(0);
            $table->timestamps();
        });

        Schema::create('coa_reports', function (Blueprint $table) {
            $table->id();
            $table->foreignId('enquiry_id')->unique()->constrained()->cascadeOnDelete();
            $table->string('report_number')->unique();
            $table->date('report_date');
            $table->date('samples_received_date')->nullable();
            $table->string('contact_name')->nullable();
            $table->string('signatory_name')->nullable();
            $table->string('signatory_title')->nullable();
            $table->text('remarks')->nullable();
            $table->string('status', 20)->default('draft'); // draft | released
            $table->timestamp('released_at')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('coa_reports');
        Schema::dropIfExists('invoice_items');
        Schema::dropIfExists('invoices');
        Schema::dropIfExists('tracking_updates');
        Schema::dropIfExists('enquiry_items');
        Schema::dropIfExists('enquiry_samples');
        Schema::dropIfExists('enquiries');
        Schema::dropIfExists('tracking_statuses');
        Schema::dropIfExists('lab_tests');
        Schema::dropIfExists('settings');
    }
};
