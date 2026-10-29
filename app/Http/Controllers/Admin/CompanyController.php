<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Company;
use Illuminate\Http\Request;

/** Customer accounts master: agreed currency, discount % and payment terms */
class CompanyController extends Controller
{
    protected function rules(): array
    {
        return [
            'name' => 'required|string|max:191',
            'country' => 'nullable|string|max:100',
            'currency' => 'required|in:SGD,USD',
            'discount_percent' => 'required|numeric|min:0|max:100',
            'payment_terms' => 'nullable|string|max:100',
            'address' => 'nullable|string|max:1000',
            'email' => 'nullable|email|max:191',
            'phone' => 'nullable|string|max:50',
            'notes' => 'nullable|string|max:191',
        ];
    }

    public function index(Request $request)
    {
        $query = Company::query()->withCount('enquiries')->orderBy('name');
        if ($s = trim((string) $request->query('q'))) {
            $query->where(fn ($q) => $q->where('name', 'like', "%$s%")->orWhere('country', 'like', "%$s%"));
        }

        return view('admin.companies.index', ['companies' => $query->paginate(30)->withQueryString()]);
    }

    public function create()
    {
        return view('admin.companies.form', ['company' => new Company(['currency' => 'SGD', 'discount_percent' => 0, 'is_active' => true])]);
    }

    public function store(Request $request)
    {
        $data = $request->validate($this->rules());
        Company::create($data + ['is_active' => $request->boolean('is_active', true)]);

        return redirect()->route('admin.companies.index')->with('success', 'Customer company added.');
    }

    public function edit(Company $company)
    {
        return view('admin.companies.form', ['company' => $company]);
    }

    public function update(Request $request, Company $company)
    {
        $data = $request->validate($this->rules());
        $company->update($data + ['is_active' => $request->boolean('is_active')]);

        return redirect()->route('admin.companies.index')->with('success', 'Customer company updated.');
    }

    public function destroy(Company $company)
    {
        $company->delete();

        return back()->with('success', 'Company removed.');
    }
}
