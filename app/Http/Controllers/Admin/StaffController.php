<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Validation\Rules\Password as PasswordRule;

/** Pacific Lab staff logins (Administrator only), plus "My account" for every staff member */
class StaffController extends Controller
{
    public function index()
    {
        return view('admin.staff.index', ['staff' => User::whereIn('role', ['admin', 'staff'])->orderBy('name')->get()]);
    }

    public function create()
    {
        return view('admin.staff.form', ['member' => new User(['role' => 'staff', 'is_active' => true])]);
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'name' => 'required|string|max:191',
            'email' => 'required|email|max:191|unique:users,email',
            'role' => 'required|in:admin,staff',
            'password' => ['required', 'confirmed', PasswordRule::min(8)],
        ]);
        User::create($data + ['is_active' => $request->boolean('is_active', true)]);

        return redirect()->route('admin.staff.index')->with('success', 'Staff login created.');
    }

    public function edit(User $staff)
    {
        abort_unless($staff->isStaff(), 404);

        return view('admin.staff.form', ['member' => $staff]);
    }

    public function update(Request $request, User $staff)
    {
        abort_unless($staff->isStaff(), 404);
        $data = $request->validate([
            'name' => 'required|string|max:191',
            'email' => 'required|email|max:191|unique:users,email,'.$staff->id,
            'role' => 'required|in:admin,staff',
            'password' => ['nullable', 'confirmed', PasswordRule::min(8)],
        ]);
        if (empty($data['password'])) {
            unset($data['password']);
        }
        $isSelf = $staff->id === $request->user()->id;
        $staff->update($data + ['is_active' => $isSelf ? true : $request->boolean('is_active')]);

        return redirect()->route('admin.staff.index')->with('success', 'Staff login updated.');
    }

    public function destroy(Request $request, User $staff)
    {
        abort_unless($staff->isStaff(), 404);
        if ($staff->id === $request->user()->id) {
            return back()->with('error', 'You cannot delete your own login.');
        }
        $staff->update(['is_active' => false]);

        return back()->with('success', 'Staff login deactivated.');
    }

    public function account(Request $request)
    {
        return view('admin.staff.account', ['member' => $request->user()]);
    }

    public function updateAccount(Request $request)
    {
        $user = $request->user();
        $data = $request->validate([
            'name' => 'required|string|max:191',
            'current_password' => 'required_with:password|nullable|current_password',
            'password' => ['nullable', 'confirmed', PasswordRule::min(8)],
        ]);
        $user->name = $data['name'];
        if (! empty($data['password'])) {
            $user->password = $data['password'];
        }
        $user->save();

        return back()->with('success', 'Your account has been updated.');
    }
}
