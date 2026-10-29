<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\LabTest;
use Illuminate\Http\Request;

/** Standard price list maintenance (PACLAB Pricing 2026) */
class LabTestController extends Controller
{
    protected function rules(): array
    {
        return [
            'category' => 'required|string|max:191',
            'name' => 'required|string|max:191',
            'matrix' => 'nullable|string|max:191',
            'method' => 'nullable|string|max:191',
            'price_sgd' => 'required|numeric|min:0',
            'price_usd' => 'required|numeric|min:0',
            'sort_order' => 'nullable|integer|min:0',
            'is_active' => 'nullable|boolean',
        ];
    }

    public function index(Request $request)
    {
        $query = LabTest::query()->orderBy('sort_order')->orderBy('id');
        if ($c = $request->query('category')) {
            $query->where('category', $c);
        }
        if ($s = trim((string) $request->query('q'))) {
            $query->where(fn ($q) => $q->where('name', 'like', "%$s%")->orWhere('matrix', 'like', "%$s%")->orWhere('method', 'like', "%$s%"));
        }

        return view('admin.lab-tests.index', [
            'tests' => $query->paginate(50)->withQueryString(),
            'categories' => LabTest::query()->select('category')->distinct()->orderBy('category')->pluck('category'),
        ]);
    }

    public function create()
    {
        return view('admin.lab-tests.form', [
            'test' => new LabTest(['is_active' => true, 'sort_order' => (int) LabTest::max('sort_order') + 1]),
            'categories' => LabTest::query()->select('category')->distinct()->orderBy('category')->pluck('category'),
        ]);
    }

    public function store(Request $request)
    {
        $data = $request->validate($this->rules());
        $data['is_active'] = $request->boolean('is_active');
        $data['sort_order'] = $data['sort_order'] ?? ((int) LabTest::max('sort_order') + 1);
        LabTest::create($data);

        return redirect()->route('admin.lab-tests.index')->with('success', 'Test added to the price list.');
    }

    public function edit(LabTest $labTest)
    {
        return view('admin.lab-tests.form', [
            'test' => $labTest,
            'categories' => LabTest::query()->select('category')->distinct()->orderBy('category')->pluck('category'),
        ]);
    }

    public function update(Request $request, LabTest $labTest)
    {
        $data = $request->validate($this->rules());
        $data['is_active'] = $request->boolean('is_active');
        $labTest->update($data);

        return redirect()->route('admin.lab-tests.index', array_filter([
            'q' => $request->input('q'),
            'category' => $request->input('category_filter'),
        ]))->with('success', 'Price list updated.');
    }

    public function destroy(LabTest $labTest)
    {
        // Keep history intact: tests already used on quotations are only deactivated
        if (\App\Models\EnquiryItem::where('lab_test_id', $labTest->id)->exists()) {
            $labTest->update(['is_active' => false]);

            return back()->with('success', 'Test is used on past quotations, so it was deactivated instead of deleted.');
        }
        $labTest->delete();

        return back()->with('success', 'Test deleted.');
    }

    /** Download the whole price list as CSV (opens in Excel) */
    public function export()
    {
        $rows = LabTest::orderBy('sort_order')->orderBy('id')->get();

        return response()->streamDownload(function () use ($rows) {
            $out = fopen('php://output', 'w');
            fwrite($out, "\xEF\xBB\xBF"); // UTF-8 BOM so Excel shows symbols like α, β correctly
            fputcsv($out, ['id', 'category', 'name', 'matrix', 'method', 'price_sgd', 'price_usd', 'is_active']);
            foreach ($rows as $t) {
                fputcsv($out, [$t->id, $t->category, $t->name, $t->matrix, $t->method, $t->price_sgd, $t->price_usd, $t->is_active ? 1 : 0]);
            }
            fclose($out);
        }, 'PacLab_Price_List_'.now()->format('Y-m-d').'.csv', ['Content-Type' => 'text/csv; charset=UTF-8']);
    }

    /**
     * Upload a CSV with the same columns as the export.
     * Rows with an id update that test; rows without an id are added.
     */
    public function import(Request $request)
    {
        $request->validate(['file' => 'required|file|mimes:csv,txt|max:5120']);

        $handle = fopen($request->file('file')->getRealPath(), 'r');
        $header = fgetcsv($handle);
        if (! $header) {
            return back()->with('error', 'The file is empty.');
        }
        $header = array_map(fn ($h) => strtolower(trim(preg_replace('/^\xEF\xBB\xBF/', '', (string) $h))), $header);
        foreach (['category', 'name', 'price_sgd', 'price_usd'] as $required) {
            if (! in_array($required, $header, true)) {
                return back()->with('error', "Column \"$required\" is missing. Use Export first to get the correct layout.");
            }
        }

        $added = $updated = 0;
        $order = (int) LabTest::max('sort_order');
        while (($row = fgetcsv($handle)) !== false) {
            if (count(array_filter($row, fn ($v) => trim((string) $v) !== '')) === 0) {
                continue;
            }
            $r = array_combine($header, array_pad(array_slice($row, 0, count($header)), count($header), null));
            if (blank($r['name'] ?? null) || blank($r['category'] ?? null)) {
                continue;
            }
            $values = [
                'category' => trim($r['category']),
                'name' => trim($r['name']),
                'matrix' => ($r['matrix'] ?? '') !== '' ? trim($r['matrix']) : null,
                'method' => ($r['method'] ?? '') !== '' ? trim($r['method']) : null,
                'price_sgd' => (float) str_replace(',', '', (string) $r['price_sgd']),
                'price_usd' => (float) str_replace(',', '', (string) $r['price_usd']),
                'is_active' => isset($r['is_active']) && $r['is_active'] !== '' ? (bool) (int) $r['is_active'] : true,
            ];
            $existing = ! empty($r['id']) ? LabTest::find((int) $r['id']) : null;
            if ($existing) {
                $existing->update($values);
                $updated++;
            } else {
                LabTest::create($values + ['sort_order' => ++$order]);
                $added++;
            }
        }
        fclose($handle);

        return back()->with('success', "Price list imported: $updated updated, $added added.");
    }
}
