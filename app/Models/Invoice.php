<?php

namespace App\Models;

use App\Models\Concerns\HasActivities;
use App\Models\Concerns\HasCode;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Invoice extends Model
{
    use HasActivities, HasCode;

    protected $guarded = [];
    protected $casts = ['date' => 'date', 'due_date' => 'date'];

    public const STATUSES = ['unpaid' => 'Unpaid', 'part_paid' => 'Partly paid', 'paid' => 'Paid', 'cancelled' => 'Cancelled'];

    public function customer(): BelongsTo { return $this->belongsTo(Customer::class); }
    public function quotation(): BelongsTo { return $this->belongsTo(Quotation::class); }
    public function order(): BelongsTo { return $this->belongsTo(Order::class); }
    public function contract(): BelongsTo { return $this->belongsTo(AmcContract::class, 'amc_contract_id'); }
    public function user(): BelongsTo { return $this->belongsTo(User::class); }
    public function items(): HasMany { return $this->hasMany(InvoiceItem::class); }
    public function payments(): HasMany { return $this->hasMany(Payment::class)->latest('date'); }

    public function getBalanceAttribute(): float
    {
        return round($this->total - $this->paid_amount, 2);
    }

    public function isOverdue(): bool
    {
        return $this->balance > 0 && $this->due_date && $this->due_date->isPast();
    }

    public function refreshPaidStatus(): void
    {
        $paid = $this->payments()->sum('amount');
        $status = $paid <= 0 ? 'unpaid' : ($paid + 0.005 >= $this->total ? 'paid' : 'part_paid');
        $this->forceFill(['paid_amount' => $paid, 'status' => $status])->save();
    }
}
