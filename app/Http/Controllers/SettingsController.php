<?php

namespace App\Http\Controllers;

use App\Models\Setting;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class SettingsController extends Controller
{
    public const KEYS = ['company_name', 'company_address', 'company_phone', 'company_email', 'company_website', 'company_trn', 'bank_name', 'bank_branch', 'bank_account', 'bank_iban',
        'vat_pct', 'quote_valid_days', 'invoice_due_days', 'complaint_sla_hours', 'payment_terms', 'delivery_terms', 'warranty_terms', 'availability'];

    public function index()
    {
        abort_unless(auth()->user()->isAdmin(), 403);

        return view('settings.index', ['s' => Setting::map(), 'users' => User::orderBy('name')->get()]);
    }

    public function update(Request $request)
    {
        abort_unless(auth()->user()->isAdmin(), 403);
        $data = $request->validate(['company_name' => 'required|string|max:120', 'vat_pct' => 'required|numeric|min:0|max:100', 'quote_valid_days' => 'required|integer|min:1|max:120', 'invoice_due_days' => 'required|integer|min:0|max:180', 'complaint_sla_hours' => 'required|integer|min:1|max:720'] + array_fill_keys(array_diff(self::KEYS, ['company_name', 'vat_pct', 'quote_valid_days', 'invoice_due_days', 'complaint_sla_hours']), 'nullable|string|max:300'));
        foreach ($data as $k => $v) {
            Setting::put($k, $v ?? '');
        }

        return back()->with('ok', 'Settings saved. New quotations and invoices use them from now on.');
    }

    public function storeUser(Request $request)
    {
        abort_unless(auth()->user()->isAdmin(), 403);
        $data = $request->validate(['name' => 'required|string|max:80', 'email' => 'required|email|unique:users,email', 'phone' => 'nullable|string|max:30', 'designation' => 'nullable|string|max:60', 'role' => ['required', Rule::in(array_keys(User::ROLES))], 'password' => 'required|string|min:8']);
        User::create($data + ['active' => true]);

        return back()->with('ok', $data['name'].' can now log in.');
    }

    public function updateUser(Request $request, User $user)
    {
        abort_unless(auth()->user()->isAdmin(), 403);
        $data = $request->validate(['role' => ['required', Rule::in(array_keys(User::ROLES))], 'active' => 'boolean', 'password' => 'nullable|string|min:8']);
        if ($user->id === auth()->id()) {
            $data['active'] = true;
            $data['role'] = 'admin';
        }
        $user->update(array_filter(['role' => $data['role'], 'active' => $request->boolean('active'), 'password' => $data['password'] ?? null], fn ($v) => $v !== null));

        return back()->with('ok', $user->name.' updated.');
    }
}
