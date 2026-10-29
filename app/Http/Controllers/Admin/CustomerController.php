<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Mail\PasswordLinkMail;
use App\Models\Company;
use App\Models\User;
use App\Services\Notifier;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Password;

/** Customer portal logins */
class CustomerController extends Controller
{
    public function index(Request $request)
    {
        $query = User::where('role', 'customer')->with('company')->withCount('enquiries')->latest();
        if ($s = trim((string) $request->query('q'))) {
            $query->where(fn ($q) => $q->where('name', 'like', "%$s%")->orWhere('email', 'like', "%$s%")->orWhere('company_name', 'like', "%$s%"));
        }

        return view('admin.customers.index', ['customers' => $query->paginate(30)->withQueryString()]);
    }

    public function edit(User $customer)
    {
        abort_unless($customer->isCustomer(), 404);

        return view('admin.customers.edit', [
            'customer' => $customer,
            'companies' => Company::orderBy('name')->get(['id', 'name']),
            'enquiries' => $customer->enquiries()->latest()->get(),
        ]);
    }

    public function update(Request $request, User $customer)
    {
        abort_unless($customer->isCustomer(), 404);
        $data = $request->validate([
            'name' => 'required|string|max:191',
            'email' => 'required|email|max:191|unique:users,email,'.$customer->id,
            'company_name' => 'nullable|string|max:191',
            'company_id' => 'nullable|exists:companies,id',
            'phone' => 'nullable|string|max:50',
        ]);
        $customer->update($data + ['is_active' => $request->boolean('is_active')]);

        return back()->with('success', 'Customer updated.');
    }

    public function sendPasswordLink(User $customer)
    {
        abort_unless($customer->isCustomer(), 404);
        $token = Password::broker()->createToken($customer);
        Notifier::send($customer->email, new PasswordLinkMail($customer, $token, true));

        return back()->with('success', 'Password set-up link emailed to '.$customer->email.'.');
    }
}
