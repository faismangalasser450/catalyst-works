@if($errors->any())<div class="mb-3 rounded-md bg-red-50 px-3 py-2 text-sm text-red-700">{{ $errors->first() }}</div>@endif
<div>
    <form wire:submit="save">
    <x-page :title="$quotation ? 'Edit '.$quotation->ref : 'New quotation'" sub="Customer, subject, lines. Terms and bank details are filled from Settings." :back="$quotation ? route('quotations.show', $quotation) : route('quotations.index')">
        <x-slot:actions>
            <button class="btn btn-primary">Save draft</button>
            <button type="button" wire:click="save('sent')" class="btn">Save & mark sent</button>
        </x-slot:actions>
    </x-page>

    <div class="grid gap-4 lg:grid-cols-3">
        <div class="space-y-4 lg:col-span-2">
            <div class="card card-b grid gap-4 sm:grid-cols-6">
                <x-field label="Customer" name="customer_id" required class="sm:col-span-4">
                    <select wire:model="customer_id" class="input" autofocus><option value="">Choose customer…</option>@foreach($customers as $c)<option value="{{ $c->id }}">{{ $c->name }}{{ $c->city ? ' — '.$c->city : '' }}</option>@endforeach</select>
                </x-field>
                <x-field label="Type" name="kind" class="sm:col-span-2"><select wire:model="kind" class="input"><option value="sales">Sales</option><option value="amc">AMC</option></select></x-field>
                <x-field label="Subject" name="subject" required class="sm:col-span-6"><input wire:model="subject" class="input" placeholder="e.g. Supply & installation of ducted split units, Villa 12"></x-field>
            </div>

            <div class="card">
                <div class="card-h">Lines
                    <div class="relative w-72" x-data="{open:false}">
                        <input wire:model.live.debounce.250ms="productSearch" @focus="open=true" @click.outside="open=false" class="input py-1.5 text-[13px] font-normal" placeholder="Add from price list: type brand or model…">
                        @if($products->count())
                        <div x-show="open" class="absolute right-0 z-10 mt-1 w-96 card py-1 text-[13px] font-normal">
                            @foreach($products as $p)<button type="button" wire:click="addProduct({{ $p->id }})" class="flex w-full items-center justify-between px-3 py-1.5 text-left hover:bg-slate-50"><span>{{ $p->label }}</span><span class="text-slate-500">{{ money($p->unit_price) }}</span></button>@endforeach
                        </div>
                        @endif
                    </div>
                </div>
                <div class="overflow-x-auto"><table class="tbl">
                    <thead><tr><th class="w-8">#</th><th class="w-32">Brand</th><th>Description</th><th class="w-24">Capacity</th><th class="w-20 text-right">Qty</th><th class="w-32 text-right">Unit price</th><th class="w-28 text-right">Total</th><th class="w-8"></th></tr></thead>
                    <tbody>
                    @foreach($items as $i => $it)
                        <tr wire:key="row{{ $i }}">
                            <td class="text-slate-400">{{ $i + 1 }}</td>
                            <td><input wire:model="items.{{ $i }}.brand" class="input py-1.5" placeholder="O General"></td>
                            <td><input wire:model="items.{{ $i }}.description" class="input py-1.5" placeholder="Ducted split unit, supply & install">@error("items.$i.description")<div class="text-xs text-red-600">Required</div>@enderror</td>
                            <td><input wire:model="items.{{ $i }}.capacity" class="input py-1.5" placeholder="3.0 TR"></td>
                            <td><input type="number" step="any" min="0.01" wire:model.live="items.{{ $i }}.qty" class="input py-1.5 text-right @error("items.$i.qty") border-red-500 @enderror">@error("items.$i.qty")<div class="text-xs text-red-600">Qty &gt; 0</div>@enderror</td>
                            <td><input type="number" step="0.01" min="0" wire:model.live="items.{{ $i }}.unit_price" class="input py-1.5 text-right @error("items.$i.unit_price") border-red-500 @enderror"></td>
                            <td class="text-right font-medium">{{ money((float) $it['qty'] * (float) $it['unit_price']) }}</td>
                            <td><button type="button" wire:click="removeItem({{ $i }})" class="text-slate-400 hover:text-red-600" title="Remove line">✕</button></td>
                        </tr>
                    @endforeach
                    </tbody>
                </table></div>
                <div class="flex items-center justify-between px-4 py-3"><button type="button" wire:click="addItem" class="btn btn-sm">+ Line</button>
                    <div class="text-[13px] text-slate-500">Press Tab to move between cells</div>
                </div>
            </div>

            <div class="card" x-data="{open: @js($showTerms)}">
                <button type="button" @click="open=!open" class="card-h w-full text-left">Terms on the printout <span class="text-xs font-normal text-slate-500" x-text="open ? 'Hide' : 'Pre-filled from Settings, click to change for this quote'"></span></button>
                <div x-show="open" x-cloak class="card-b grid gap-4 sm:grid-cols-2">
                    <x-field label="Payment" name="payment_terms"><input wire:model="payment_terms" class="input"></x-field>
                    <x-field label="Delivery" name="delivery_terms"><input wire:model="delivery_terms" class="input"></x-field>
                    <x-field label="Warranty" name="warranty_terms"><input wire:model="warranty_terms" class="input"></x-field>
                    <x-field label="Availability" name="availability"><input wire:model="availability" class="input"></x-field>
                    <x-field label="Note to customer" name="notes" class="sm:col-span-2"><textarea wire:model="notes" rows="2" class="input" placeholder="Anything extra to print under the table"></textarea></x-field>
                </div>
            </div>
        </div>

        <div class="space-y-4">
            <div class="card card-b grid grid-cols-2 gap-3">
                <x-field label="Date" name="date" required><input type="date" wire:model="date" class="input"></x-field>
                <x-field label="Valid for (days)" name="valid_days" required><input type="number" wire:model="valid_days" class="input"></x-field>
                <x-field label="Discount %" name="discount_pct"><input type="number" step="0.01" wire:model.live="discount_pct" class="input"></x-field>
                <x-field label="VAT %" name="vat_pct"><input type="number" step="0.01" wire:model.live="vat_pct" class="input"></x-field>
            </div>
            <div class="card card-b text-[13px]">
                <div class="flex justify-between py-1"><span class="text-slate-600">Subtotal</span><span>{{ money($t['subtotal']) }}</span></div>
                @if($t['discount'] > 0)<div class="flex justify-between py-1"><span class="text-slate-600">Discount {{ $discount_pct + 0 }}%</span><span>− {{ money($t['discount']) }}</span></div>@endif
                <div class="flex justify-between py-1"><span class="text-slate-600">VAT {{ $vat_pct + 0 }}%</span><span>{{ money($t['vat']) }}</span></div>
                <div class="mt-1 flex justify-between border-t border-slate-200 pt-2 text-base font-semibold"><span>Total</span><span>AED {{ money($t['total']) }}</span></div>
            </div>
            <div class="flex gap-2 lg:flex-col"><button class="btn btn-primary flex-1 justify-center">Save draft</button><button type="button" wire:click="save('sent')" class="btn flex-1 justify-center">Save & mark sent</button></div>
        </div>
    </div>
    </form>
</div>
