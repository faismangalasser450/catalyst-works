<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PpmVisit extends Model
{
    protected $guarded = [];
    protected $casts = ['scheduled_on' => 'date', 'done_on' => 'date'];

    public function contract(): BelongsTo { return $this->belongsTo(AmcContract::class, 'amc_contract_id'); }
    public function technician(): BelongsTo { return $this->belongsTo(User::class, 'technician_id'); }
}
