<x-layouts.app title="Settings">
    <x-page title="Settings" sub="Company details, terms and bank details print on every quotation and invoice, so nobody retypes them." />
    <form method="post" action="{{ route('settings.update') }}" class="grid gap-4 lg:grid-cols-2">@csrf @method('put')
        <div class="card"><div class="card-h">Company</div><div class="card-b grid gap-3 sm:grid-cols-2">
            <x-field label="Company name" name="company_name" required class="sm:col-span-2"><input name="company_name" class="input" value="{{ old('company_name', $s['company_name'] ?? '') }}"></x-field>
            <x-field label="Address" name="company_address" class="sm:col-span-2"><input name="company_address" class="input" value="{{ old('company_address', $s['company_address'] ?? '') }}"></x-field>
            <x-field label="Phone" name="company_phone"><input name="company_phone" class="input" value="{{ old('company_phone', $s['company_phone'] ?? '') }}"></x-field>
            <x-field label="Email" name="company_email"><input name="company_email" class="input" value="{{ old('company_email', $s['company_email'] ?? '') }}"></x-field>
            <x-field label="Website" name="company_website"><input name="company_website" class="input" value="{{ old('company_website', $s['company_website'] ?? '') }}"></x-field>
            <x-field label="TRN" name="company_trn"><input name="company_trn" class="input" value="{{ old('company_trn', $s['company_trn'] ?? '') }}"></x-field>
        </div></div>
        <div class="card"><div class="card-h">Bank details (printed on quotations & invoices)</div><div class="card-b grid gap-3 sm:grid-cols-2">
            <x-field label="Bank" name="bank_name"><input name="bank_name" class="input" value="{{ old('bank_name', $s['bank_name'] ?? '') }}"></x-field>
            <x-field label="Branch" name="bank_branch"><input name="bank_branch" class="input" value="{{ old('bank_branch', $s['bank_branch'] ?? '') }}"></x-field>
            <x-field label="Account no" name="bank_account"><input name="bank_account" class="input" value="{{ old('bank_account', $s['bank_account'] ?? '') }}"></x-field>
            <x-field label="IBAN" name="bank_iban"><input name="bank_iban" class="input" value="{{ old('bank_iban', $s['bank_iban'] ?? '') }}"></x-field>
        </div></div>
        <div class="card"><div class="card-h">Defaults</div><div class="card-b grid gap-3 sm:grid-cols-4">
            <x-field label="VAT %" name="vat_pct" required><input type="number" step="0.01" name="vat_pct" class="input" value="{{ old('vat_pct', $s['vat_pct'] ?? 5) }}"></x-field>
            <x-field label="Quote valid (days)" name="quote_valid_days" required><input type="number" name="quote_valid_days" class="input" value="{{ old('quote_valid_days', $s['quote_valid_days'] ?? 15) }}"></x-field>
            <x-field label="Invoice due (days)" name="invoice_due_days" required><input type="number" name="invoice_due_days" class="input" value="{{ old('invoice_due_days', $s['invoice_due_days'] ?? 30) }}"></x-field>
            <x-field label="Complaint SLA (hours)" name="complaint_sla_hours" required hint="Complaints older than this show red"><input type="number" name="complaint_sla_hours" class="input" value="{{ old('complaint_sla_hours', $s['complaint_sla_hours'] ?? 48) }}"></x-field>
        </div></div>
        <div class="card"><div class="card-h">Standard terms</div><div class="card-b grid gap-3">
            <x-field label="Payment" name="payment_terms"><input name="payment_terms" class="input" value="{{ old('payment_terms', $s['payment_terms'] ?? '') }}"></x-field>
            <x-field label="Delivery" name="delivery_terms"><input name="delivery_terms" class="input" value="{{ old('delivery_terms', $s['delivery_terms'] ?? '') }}"></x-field>
            <x-field label="Warranty" name="warranty_terms"><input name="warranty_terms" class="input" value="{{ old('warranty_terms', $s['warranty_terms'] ?? '') }}"></x-field>
            <x-field label="Availability" name="availability"><input name="availability" class="input" value="{{ old('availability', $s['availability'] ?? '') }}"></x-field>
        </div></div>
        <div class="lg:col-span-2"><button class="btn btn-primary btn-lg">Save settings</button></div>
    </form>

    <div class="card mt-6"><div class="card-h">Users <span class="text-xs font-normal text-slate-500">Technicians only see “My jobs”; Accounts see invoices; Sales and Service see their modules; Admin sees everything.</span></div>
        <table class="tbl"><thead><tr><th>Name</th><th>Email</th><th>Phone</th><th>Role</th><th>Active</th><th>New password</th><th></th></tr></thead><tbody>
        @foreach($users as $u)
            <tr><form method="post" action="{{ route('settings.users.update', $u) }}">@csrf @method('put')
                <td class="font-medium">{{ $u->name }}<div class="text-xs text-slate-500">{{ $u->designation }}</div></td><td class="text-slate-600">{{ $u->email }}</td><td class="text-slate-600">{{ $u->phone }}</td>
                <td><select name="role" class="input w-32 py-1 text-xs" @disabled($u->id === auth()->id())>@foreach(\App\Models\User::ROLES as $k => $l)<option value="{{ $k }}" @selected($u->role === $k)>{{ $l }}</option>@endforeach</select></td>
                <td><input type="checkbox" name="active" value="1" @checked($u->active) @disabled($u->id === auth()->id())></td>
                <td><input type="password" name="password" class="input w-36 py-1 text-xs" placeholder="leave blank to keep" autocomplete="new-password"></td>
                <td class="text-right"><button class="btn btn-sm">Save</button></td>
            </form></tr>
        @endforeach
        <tr class="bg-slate-50"><form method="post" action="{{ route('settings.users.store') }}">@csrf
            <td><input name="name" class="input py-1 text-xs" placeholder="Full name" required></td><td><input type="email" name="email" class="input py-1 text-xs" placeholder="email" required></td><td><input name="phone" class="input py-1 text-xs" placeholder="05x"></td>
            <td><select name="role" class="input w-32 py-1 text-xs">@foreach(\App\Models\User::ROLES as $k => $l)<option value="{{ $k }}">{{ $l }}</option>@endforeach</select></td><td></td>
            <td><input type="password" name="password" class="input w-36 py-1 text-xs" placeholder="min 8 chars" required autocomplete="new-password"></td><td class="text-right"><button class="btn btn-sm btn-primary">Add user</button></td>
        </form></tr>
        </tbody></table>
    </div>
</x-layouts.app>
