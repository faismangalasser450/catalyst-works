<?php

namespace App\Models;

use App\Models\Concerns\HasActivities;
use App\Models\Concerns\HasCode;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Complaint extends Model
{
    use HasActivities, HasCode;

    protected $guarded = [];

    protected $casts = ['reported_at' => 'datetime', 'closed_at' => 'datetime', 'chargeable' => 'boolean'];

    public const TYPES = ['Not cooling', 'Water leak', 'Noise', 'Not starting', 'Bad smell', 'Thermostat / remote', 'Gas leak', 'Other'];

    public const EQUIPMENT = ['Split', 'Ducted split', 'Cassette', 'Package unit', 'FCU', 'Chiller', 'VRF'];

    public const STATUSES = ['open' => 'Open', 'assigned' => 'Assigned', 'visited' => 'Visited', 'in_progress' => 'In progress', 'closed' => 'Closed'];

    public const PRIORITIES = ['low' => 'Low', 'normal' => 'Normal', 'high' => 'High'];

    public function customer(): BelongsTo
    {
        return $this->belongsTo(Customer::class);
    }

    public function contract(): BelongsTo
    {
        return $this->belongsTo(AmcContract::class, 'amc_contract_id');
    }

    public function technician(): BelongsTo
    {
        return $this->belongsTo(User::class, 'technician_id');
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function visits(): HasMany
    {
        return $this->hasMany(ComplaintVisit::class)->latest('visited_at');
    }

    public function materials(): HasMany
    {
        return $this->hasMany(ComplaintMaterial::class);
    }

    public function ageHours(): int
    {
        return (int) $this->reported_at->diffInHours($this->closed_at ?? now());
    }

    public function isOverdue(): bool
    {
        return $this->status !== 'closed' && $this->ageHours() > 48;
    }

    public function setStatus(string $status, ?string $note = null): void
    {
        $this->status = $status;
        if ($status === 'closed') {
            $this->closed_at = now();
            $this->closing_note = $note;
        } else {
            $this->closed_at = null;
        }
        $this->save();
        $this->log('status', self::STATUSES[$status].($note ? ' — '.$note : ''));
    }
}
