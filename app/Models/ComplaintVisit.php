<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ComplaintVisit extends Model
{
    protected $guarded = [];
    protected $casts = ['visited_at' => 'datetime'];

    public function technician(): BelongsTo { return $this->belongsTo(User::class, 'technician_id'); }
}
