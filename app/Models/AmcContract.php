<?php

namespace App\Models;

use App\Models\Concerns\HasActivities;
use App\Models\Concerns\HasCode;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class AmcContract extends Model
{
    use HasActivities, HasCode;

    protected $guarded = [];
    protected $casts = ['start_date' => 'date', 'end_date' => 'date'];

    public const STATUSES = ['active' => 'Active', 'expired' => 'Expired', 'renewed' => 'Renewed', 'cancelled' => 'Cancelled'];

    public function customer(): BelongsTo { return $this->belongsTo(Customer::class); }
    public function quotation(): BelongsTo { return $this->belongsTo(Quotation::class); }
    public function visits(): HasMany { return $this->hasMany(PpmVisit::class)->orderBy('scheduled_on'); }
    public function complaints(): HasMany { return $this->hasMany(Complaint::class)->latest('reported_at'); }
    public function invoices(): HasMany { return $this->hasMany(Invoice::class, 'amc_contract_id'); }

    public function daysLeft(): int
    {
        return (int) now()->startOfDay()->diffInDays($this->end_date, false);
    }

    public function schedulevisits(): void
    {
        $this->visits()->delete();
        $gap = 12 / max(1, $this->visits_per_year);
        for ($i = 0; $i < $this->visits_per_year; $i++) {
            $this->visits()->create(['scheduled_on' => $this->start_date->copy()->addMonths((int) round($gap * $i + $gap / 2))]);
        }
    }
}
