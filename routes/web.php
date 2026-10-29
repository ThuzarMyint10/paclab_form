<?php

use App\Http\Controllers\Admin;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\EnquiryController;
use App\Http\Controllers\Portal;
use App\Http\Controllers\PublicQuotationController;
use App\Http\Controllers\TrackController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Public website
|--------------------------------------------------------------------------
*/
Route::get('/', [EnquiryController::class, 'home'])->name('home');

Route::get('/enquiry', [EnquiryController::class, 'create'])->name('enquiry.create');
Route::post('/enquiry', [EnquiryController::class, 'store'])->middleware('throttle:10,1')->name('enquiry.store');
Route::get('/enquiry/submitted', [EnquiryController::class, 'submitted'])->name('enquiry.submitted');

Route::get('/track', [TrackController::class, 'form'])->name('track');
Route::post('/track', [TrackController::class, 'lookup'])->middleware('throttle:20,1')->name('track.lookup');

// Secure link emailed to the customer — view the quotation and approve / decline without logging in
Route::get('/q/{token}', [PublicQuotationController::class, 'show'])->name('quotation.public');
Route::post('/q/{token}/approve', [PublicQuotationController::class, 'approve'])->name('quotation.public.approve');
Route::post('/q/{token}/decline', [PublicQuotationController::class, 'decline'])->name('quotation.public.decline');
Route::get('/q/{token}/document/{doc}', [PublicQuotationController::class, 'document'])
    ->whereIn('doc', ['quotation', 'ssf'])->name('quotation.public.document');

/*
|--------------------------------------------------------------------------
| Customer login / password
|--------------------------------------------------------------------------
*/
Route::middleware('guest')->group(function () {
    Route::get('/login', [AuthController::class, 'showCustomerLogin'])->name('login');
    Route::post('/login', [AuthController::class, 'customerLogin'])->middleware('throttle:10,1');
    Route::get('/register', [AuthController::class, 'showRegister'])->name('register');
    Route::post('/register', [AuthController::class, 'register'])->middleware('throttle:5,1');
    Route::get('/forgot-password', [AuthController::class, 'showForgot'])->name('password.request');
    Route::post('/forgot-password', [AuthController::class, 'sendResetLink'])->middleware('throttle:5,1')->name('password.email');
    Route::get('/reset-password/{token}', [AuthController::class, 'showReset'])->name('password.reset');
    Route::post('/reset-password', [AuthController::class, 'resetPassword'])->name('password.update');

    Route::get('/admin/login', [AuthController::class, 'showAdminLogin'])->name('admin.login');
    Route::post('/admin/login', [AuthController::class, 'adminLogin'])->middleware('throttle:10,1');
});
Route::post('/logout', [AuthController::class, 'logout'])->middleware('auth')->name('logout');

/*
|--------------------------------------------------------------------------
| Customer portal — customers only ever see their own jobs
|--------------------------------------------------------------------------
*/
Route::prefix('portal')->name('portal.')->middleware(['auth', 'role:customer'])->group(function () {
    Route::get('/', [Portal\PortalController::class, 'dashboard'])->name('dashboard');
    Route::get('/enquiries/{enquiry}', [Portal\PortalController::class, 'show'])->name('enquiries.show');
    Route::post('/enquiries/{enquiry}/approve', [Portal\PortalController::class, 'approve'])->name('enquiries.approve');
    Route::post('/enquiries/{enquiry}/decline', [Portal\PortalController::class, 'decline'])->name('enquiries.decline');
    Route::get('/enquiries/{enquiry}/document/{doc}', [Portal\PortalController::class, 'document'])
        ->whereIn('doc', ['quotation', 'ssf', 'coa'])->name('enquiries.document');
    Route::get('/invoices/{invoice}/pdf', [Portal\PortalController::class, 'invoicePdf'])->name('invoices.pdf');
    Route::get('/profile', [Portal\PortalController::class, 'profile'])->name('profile');
    Route::put('/profile', [Portal\PortalController::class, 'updateProfile'])->name('profile.update');
});

/*
|--------------------------------------------------------------------------
| Administration — Pacific Lab staff (login required)
|--------------------------------------------------------------------------
*/
Route::prefix('admin')->name('admin.')->middleware(['auth', 'role:admin,staff'])->group(function () {
    Route::get('/', [Admin\DashboardController::class, 'index'])->name('dashboard');

    // Enquiries / jobs
    Route::get('/enquiries', [Admin\EnquiryController::class, 'index'])->name('enquiries.index');
    Route::get('/enquiries/{enquiry}', [Admin\EnquiryController::class, 'show'])->name('enquiries.show');
    Route::put('/enquiries/{enquiry}/details', [Admin\EnquiryController::class, 'updateDetails'])->name('enquiries.details');
    Route::put('/enquiries/{enquiry}/pricing', [Admin\EnquiryController::class, 'updatePricing'])->name('enquiries.pricing');
    Route::post('/enquiries/{enquiry}/price-list', [Admin\EnquiryController::class, 'applyPriceList'])->name('enquiries.price-list');
    Route::post('/enquiries/{enquiry}/items', [Admin\EnquiryController::class, 'addItem'])->name('enquiries.items.add');
    Route::delete('/enquiries/{enquiry}/items/{item}', [Admin\EnquiryController::class, 'removeItem'])->name('enquiries.items.remove');
    Route::post('/enquiries/{enquiry}/send-quotation', [Admin\EnquiryController::class, 'sendQuotation'])->name('enquiries.send-quotation');
    Route::post('/enquiries/{enquiry}/response', [Admin\EnquiryController::class, 'recordResponse'])->name('enquiries.response');
    Route::post('/enquiries/{enquiry}/tracking', [Admin\EnquiryController::class, 'addTracking'])->name('enquiries.tracking');
    Route::put('/enquiries/{enquiry}/samples', [Admin\EnquiryController::class, 'updateSamples'])->name('enquiries.samples');
    Route::get('/enquiries/{enquiry}/document/{doc}', [Admin\EnquiryController::class, 'document'])
        ->whereIn('doc', ['quotation', 'ssf', 'coa'])->name('enquiries.document');
    Route::get('/enquiries/{enquiry}/po-file', [Admin\EnquiryController::class, 'poFile'])->name('enquiries.po-file');

    // Certificate of Analysis
    Route::get('/enquiries/{enquiry}/coa', [Admin\CoaController::class, 'edit'])->name('coa.edit');
    Route::put('/enquiries/{enquiry}/coa', [Admin\CoaController::class, 'update'])->name('coa.update');
    Route::post('/enquiries/{enquiry}/coa/release', [Admin\CoaController::class, 'release'])->name('coa.release');

    // Invoices
    Route::get('/invoices', [Admin\InvoiceController::class, 'index'])->name('invoices.index');
    Route::post('/enquiries/{enquiry}/invoices', [Admin\InvoiceController::class, 'store'])->name('invoices.store');
    Route::get('/invoices/{invoice}', [Admin\InvoiceController::class, 'edit'])->name('invoices.edit');
    Route::put('/invoices/{invoice}', [Admin\InvoiceController::class, 'update'])->name('invoices.update');
    Route::post('/invoices/{invoice}/issue', [Admin\InvoiceController::class, 'issue'])->name('invoices.issue');
    Route::post('/invoices/{invoice}/status', [Admin\InvoiceController::class, 'setStatus'])->name('invoices.status');
    Route::get('/invoices/{invoice}/pdf', [Admin\InvoiceController::class, 'pdf'])->name('invoices.pdf');

    // Master data
    Route::get('/lab-tests/export', [Admin\LabTestController::class, 'export'])->name('lab-tests.export');
    Route::post('/lab-tests/import', [Admin\LabTestController::class, 'import'])->name('lab-tests.import');
    Route::resource('lab-tests', Admin\LabTestController::class)->except(['show']);
    Route::resource('companies', Admin\CompanyController::class)->except(['show']);
    Route::get('/customers', [Admin\CustomerController::class, 'index'])->name('customers.index');
    Route::get('/customers/{customer}/edit', [Admin\CustomerController::class, 'edit'])->name('customers.edit');
    Route::put('/customers/{customer}', [Admin\CustomerController::class, 'update'])->name('customers.update');
    Route::post('/customers/{customer}/password-link', [Admin\CustomerController::class, 'sendPasswordLink'])->name('customers.password-link');

    Route::get('/account', [Admin\StaffController::class, 'account'])->name('account');
    Route::put('/account', [Admin\StaffController::class, 'updateAccount'])->name('account.update');

    // Administrator only
    Route::middleware('role:admin')->group(function () {
        Route::resource('staff', Admin\StaffController::class)->except(['show'])->parameters(['staff' => 'staff']);
        Route::resource('tracking-statuses', Admin\TrackingStatusController::class)->except(['show']);
        Route::get('/settings', [Admin\SettingController::class, 'edit'])->name('settings.edit');
        Route::put('/settings', [Admin\SettingController::class, 'update'])->name('settings.update');
    });
});
