<?php

namespace App\Livewire\Quotations;

use App\Models\Customer;
use App\Models\Enquiry;
use App\Models\Product;
use App\Models\Quotation;
use App\Models\Setting;
use Livewire\Component;

class Editor extends Component
{
    public ?Quotation $quotation = null;
    public ?int $enquiry_id = null;
    public string $kind = 'sales';
    public ?int $customer_id = null;
    public string $date = '';
    public int $valid_days = 15;
    public string $subject = '';
    public string $discount_pct = '0';
    public string $vat_pct = '5';
    public array $items = [];
    public bool $showTerms = false;
    public string $payment_terms = '';
    public string $delivery_terms = '';
    public string $warranty_terms = '';
    public string $availability = '';
    public string $notes = '';
    public string $productSearch = '';

    public function mount(?Quotation $quotation = null, ?int $enquiry = null, ?int $customer = null, ?string $kind = null): void
    {
        $this->date = today()->format('Y-m-d');
        $this->valid_days = (int) Setting::get('quote_valid_days', 15);
        $this->vat_pct = (string) Setting::get('vat_pct', 5);
        foreach (['payment_terms', 'delivery_terms', 'warranty_terms', 'availability'] as $k) {
            $this->$k = (string) Setting::get($k, '');
        }
        if ($quotation?->exists) {
            $this->quotation = $quotation;
            $this->fill($quotation->only(['enquiry_id', 'kind', 'customer_id', 'subject', 'payment_terms', 'delivery_terms', 'warranty_terms', 'availability']));
            $this->date = $quotation->date->format('Y-m-d');
            $this->valid_days = $quotation->valid_days;
            $this->discount_pct = (string) ($quotation->discount_pct + 0);
            $this->vat_pct = (string) ($quotation->vat_pct + 0);
            $this->notes = (string) $quotation->notes;
            $this->items = $quotation->items->map(fn ($i) => ['brand' => $i->brand, 'description' => $i->description, 'capacity' => $i->capacity, 'qty' => $i->qty + 0, 'unit_price' => $i->unit_price + 0, 'product_id' => $i->product_id])->all();
        } elseif ($enquiry && $e = Enquiry::find($enquiry)) {
            $this->enquiry_id = $e->id;
            $this->customer_id = $e->customer_id;
            $this->kind = $e->kind;
            $this->subject = $e->description;
        } else {
            $this->customer_id = $customer;
            $this->kind = $kind === 'amc' ? 'amc' : 'sales';
        }
        if (! $this->items) {
            $this->addItem();
        }
    }

    public function addItem(): void
    {
        $this->items[] = ['brand' => '', 'description' => '', 'capacity' => '', 'qty' => 1, 'unit_price' => 0, 'product_id' => null];
    }

    public function addProduct(int $id): void
    {
        $p = Product::findOrFail($id);
        $blank = collect($this->items)->search(fn ($i) => $i['description'] === '' && (float) $i['unit_price'] == 0);
        $row = ['brand' => $p->brand, 'description' => trim($p->name.' '.$p->model), 'capacity' => $p->capacity, 'qty' => 1, 'unit_price' => $p->unit_price + 0, 'product_id' => $p->id];
        if ($blank !== false) {
            $this->items[$blank] = $row;
        } else {
            $this->items[] = $row;
        }
        $this->productSearch = '';
    }

    public function removeItem(int $i): void
    {
        unset($this->items[$i]);
        $this->items = array_values($this->items);
        if (! $this->items) {
            $this->addItem();
        }
    }

    public function totals(): array
    {
        $sub = collect($this->items)->sum(fn ($i) => (float) $i['qty'] * (float) $i['unit_price']);
        $disc = round($sub * (float) $this->discount_pct / 100, 2);
        $vat = round(($sub - $disc) * (float) $this->vat_pct / 100, 2);

        return ['subtotal' => $sub, 'discount' => $disc, 'vat' => $vat, 'total' => $sub - $disc + $vat];
    }

    public function save(string $then = 'view')
    {
        $this->validate([
            'customer_id' => 'required|exists:customers,id', 'date' => 'required|date', 'valid_days' => 'required|integer|min:1|max:120',
            'subject' => 'required|string|max:200', 'discount_pct' => 'numeric|min:0|max:100', 'vat_pct' => 'numeric|min:0|max:100',
            'items' => 'required|array|min:1', 'items.*.description' => 'required|string|max:300', 'items.*.qty' => 'required|numeric|min:0.01', 'items.*.unit_price' => 'required|numeric|min:0',
        ], ['items.*.description.required' => 'Every line needs a description.', 'items.*.qty.required' => 'Every line needs a quantity.', 'items.*.unit_price.required' => 'Every line needs a price.']);

        $data = [
            'kind' => $this->kind, 'enquiry_id' => $this->enquiry_id, 'customer_id' => $this->customer_id, 'date' => $this->date, 'valid_days' => $this->valid_days,
            'subject' => $this->subject, 'discount_pct' => (float) $this->discount_pct, 'vat_pct' => (float) $this->vat_pct, 'payment_terms' => $this->payment_terms,
            'delivery_terms' => $this->delivery_terms, 'warranty_terms' => $this->warranty_terms, 'availability' => $this->availability, 'notes' => $this->notes ?: null,
        ];
        if ($this->quotation) {
            $this->quotation->update($data);
            $this->quotation->items()->delete();
            $q = $this->quotation;
            $q->log('edited');
        } else {
            $q = Quotation::create($data + ['code' => Quotation::nextCode($this->kind), 'user_id' => auth()->id(), 'status' => 'draft']);
            $q->log('created');
            $q->enquiry?->update(['status' => 'quoted']);
        }
        foreach ($this->items as $i => $it) {
            $q->items()->create(['product_id' => $it['product_id'] ?: null, 'brand' => $it['brand'] ?: null, 'description' => $it['description'], 'capacity' => $it['capacity'] ?: null, 'qty' => $it['qty'], 'unit_price' => $it['unit_price'], 'total' => round($it['qty'] * $it['unit_price'], 2), 'sort' => $i]);
        }
        $q->recalculate();
        if ($then === 'sent') {
            app(\App\Services\QuotationActions::class)->markSent($q);
        }

        return redirect()->route('quotations.show', $q)->with('ok', $q->ref.' saved'.($then === 'sent' ? ' and marked as sent.' : '.'));
    }

    public function render()
    {
        return view('livewire.quotations.editor', [
            'customers' => Customer::orderBy('name')->get(['id', 'name', 'city']),
            'products' => strlen($this->productSearch) >= 2 ? Product::where(fn ($q) => $q->where('name', 'like', "%{$this->productSearch}%")->orWhere('brand', 'like', "%{$this->productSearch}%")->orWhere('model', 'like', "%{$this->productSearch}%"))->limit(8)->get() : collect(),
            't' => $this->totals(),
        ])->layout('components.layouts.app', ['title' => $this->quotation ? 'Edit '.$this->quotation->ref : 'New quotation']);
    }
}
