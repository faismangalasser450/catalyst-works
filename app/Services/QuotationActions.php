<?php

namespace App\Services;

use App\Models\AmcContract;
use App\Models\Invoice;
use App\Models\Order;
use App\Models\Quotation;
use App\Models\Setting;

class QuotationActions
{
    public function markSent(Quotation $q): void
    {
        if ($q->status !== 'draft') {
            return;
        }
        $q->update(['status' => 'sent', 'sent_at' => now()]);
        $q->log('status', 'Sent to customer');
    }

    public function approve(Quotation $q): void
    {
        if (! $q->isOpen()) {
            return;
        }
        $q->update(['status' => 'approved', 'decided_at' => now(), 'sent_at' => $q->sent_at ?? now()]);
        $q->log('status', 'Approved by customer');
        $q->enquiry?->update(['status' => 'won']);
    }

    public function reject(Quotation $q, string $reason): void
    {
        if (! $q->isOpen()) {
            return;
        }
        $q->update(['status' => 'rejected', 'decided_at' => now(), 'lost_reason' => $reason]);
        $q->log('status', 'Rejected: '.$reason);
        if ($q->enquiry && ! $q->enquiry->quotations()->where('status', '!=', 'rejected')->exists()) {
            $q->enquiry->update(['status' => 'lost']);
        }
    }

    public function revise(Quotation $q): Quotation
    {
        $new = $q->replicate(['sent_at', 'decided_at', 'lost_reason']);
        $new->fill(['revision' => $q->revision + 1, 'status' => 'draft', 'date' => today()])->save();
        foreach ($q->items as $it) {
            $new->items()->create($it->only(['product_id', 'brand', 'description', 'capacity', 'qty', 'unit_price', 'total', 'sort']));
        }
        if ($q->isOpen()) {
            $q->update(['status' => 'rejected', 'lost_reason' => 'Superseded by R'.$new->revision, 'decided_at' => now()]);
        }
        $new->log('created', 'Revision of '.$q->ref);

        return $new;
    }

    public function createOrder(Quotation $q): Order
    {
        if ($q->order) {
            return $q->order;
        }
        $order = Order::create([
            'code' => Order::nextCode('SO-'), 'quotation_id' => $q->id, 'customer_id' => $q->customer_id, 'user_id' => auth()->id(),
            'po_date' => today(), 'start_date' => today(), 'due_date' => today()->addDays(14), 'status' => 'confirmed', 'total' => $q->total,
        ]);
        $q->update(['status' => 'ordered']);
        $q->log('status', 'Order '.$order->code.' created');
        $order->log('created', 'From '.$q->ref);

        return $order;
    }

    public function createContract(Quotation $q): AmcContract
    {
        if ($q->contract) {
            return $q->contract;
        }
        $c = AmcContract::create([
            'code' => AmcContract::nextCode('AMC-'), 'customer_id' => $q->customer_id, 'quotation_id' => $q->id,
            'site_name' => $q->customer->name.($q->customer->city ? ', '.$q->customer->city : ''), 'equipment' => $q->items->first()?->description ?? $q->subject,
            'start_date' => today(), 'end_date' => today()->addYear()->subDay(), 'visits_per_year' => 4, 'value' => $q->total, 'status' => 'active',
        ]);
        $c->schedulevisits();
        $q->update(['status' => 'ordered']);
        $q->log('status', 'AMC '.$c->code.' started');
        $c->log('created', 'From '.$q->ref);

        return $c;
    }

    public function createInvoice(Quotation $q, ?Order $order = null, ?AmcContract $contract = null): Invoice
    {
        if ($q->invoice) {
            return $q->invoice;
        }
        $inv = Invoice::create([
            'code' => Invoice::nextCode('INV-'), 'kind' => $q->kind, 'customer_id' => $q->customer_id, 'quotation_id' => $q->id,
            'order_id' => $order?->id, 'amc_contract_id' => $contract?->id, 'user_id' => auth()->id(), 'date' => today(),
            'due_date' => today()->addDays((int) Setting::get('invoice_due_days', 30)), 'subtotal' => $q->subtotal, 'discount_amt' => $q->discount_amt,
            'vat_pct' => $q->vat_pct, 'vat_amt' => $q->vat_amt, 'total' => $q->total, 'lpo_number' => $order?->po_number,
        ]);
        foreach ($q->items as $it) {
            $inv->items()->create(['description' => trim(($it->brand ? $it->brand.' ' : '').$it->description.($it->capacity ? ', '.$it->capacity : '')), 'qty' => $it->qty, 'unit_price' => $it->unit_price, 'total' => $it->total]);
        }
        $q->update(['status' => 'invoiced']);
        $q->log('status', 'Invoice '.$inv->code.' created');
        $inv->log('created', 'From '.$q->ref);

        return $inv;
    }
}
