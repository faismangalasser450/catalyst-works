<?php

namespace App\Models;

use App\Models\Concerns\HasActivities;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Quotation extends Model
{
    use HasActivities;

    protected $guarded = [];
    protected $casts = ['date' => 'date', 'sent_at' => 'datetime', 'decided_at' => 'datetime'];

    public const STATUSES = [
        'draft' => 'Draft', 'sent' => 'Sent', 'approved' => 'Approved',
        'rejected' => 'Rejected', 'ordered' => 'Ordered', 'invoiced' => 'Invoiced',
    ];

    public static function nextCode(string $kind = 'sales'): string
    {
        $prefix = $kind === 'amc' ? 'CAC/AMC/' : 'CAC/QTN/';
        $last = static::query()->where('code', 'like', $prefix.'%')->orderByDesc('id')->value('code');
        $n = $last ? ((int) substr($last, strlen($prefix))) + 1 : 1;

        return $prefix.str_pad((string) $n, 4, '0', STR_PAD_LEFT);
    }

    public function customer(): BelongsTo { return $this->belongsTo(Customer::class); }
    public function user(): BelongsTo { return $this->belongsTo(User::class); }
    public function enquiry(): BelongsTo { return $this->belongsTo(Enquiry::class); }
    public function items(): HasMany { return $this->hasMany(QuotationItem::class)->orderBy('sort'); }
    public function order(): HasOne { return $this->hasOne(Order::class); }
    public function invoice(): HasOne { return $this->hasOne(Invoice::class); }
    public function contract(): HasOne { return $this->hasOne(AmcContract::class); }

    public function recalculate(): void
    {
        $subtotal = $this->items()->sum('total');
        $discount = round($subtotal * $this->discount_pct / 100, 2);
        $taxable = $subtotal - $discount;
        $vat = round($taxable * $this->vat_pct / 100, 2);
        $this->forceFill([
            'subtotal' => $subtotal,
            'discount_amt' => $discount,
            'vat_amt' => $vat,
            'total' => $taxable + $vat,
        ])->save();
    }

    public function getRefAttribute(): string
    {
        return $this->code.($this->revision ? ' R'.$this->revision : '');
    }

    public function getValidUntilAttribute()
    {
        return $this->date->copy()->addDays($this->valid_days);
    }

    public function isOpen(): bool
    {
        return in_array($this->status, ['draft', 'sent']);
    }
}
