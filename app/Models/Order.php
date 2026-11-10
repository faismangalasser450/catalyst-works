<?php

namespace App\Models;

use App\Models\Concerns\HasActivities;
use App\Models\Concerns\HasCode;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Order extends Model
{
    use HasActivities, HasCode;

    protected $guarded = [];
    protected $casts = ['po_date' => 'date', 'start_date' => 'date', 'due_date' => 'date', 'delivered_on' => 'date'];

    public const STATUSES = ['confirmed' => 'Confirmed', 'in_progress' => 'In progress', 'delivered' => 'Delivered', 'closed' => 'Closed'];

    public function quotation(): BelongsTo { return $this->belongsTo(Quotation::class); }
    public function customer(): BelongsTo { return $this->belongsTo(Customer::class); }
    public function user(): BelongsTo { return $this->belongsTo(User::class); }
    public function technician(): BelongsTo { return $this->belongsTo(User::class, 'technician_id'); }
    public function invoice(): HasOne { return $this->hasOne(Invoice::class); }
}
