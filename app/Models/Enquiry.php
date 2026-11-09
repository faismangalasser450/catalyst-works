<?php

namespace App\Models;

use App\Models\Concerns\HasActivities;
use App\Models\Concerns\HasCode;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Enquiry extends Model
{
    use HasActivities, HasCode;

    protected $guarded = [];
    protected $casts = ['date' => 'date', 'follow_up_on' => 'date'];

    public const TYPES = ['Supply & install', 'Supply only', 'Service / repair', 'AMC', 'Ducting works'];
    public const SOURCES = ['Phone', 'WhatsApp', 'Email', 'Walk-in', 'Referral', 'Website'];
    public const STATUSES = ['open' => 'Open', 'quoted' => 'Quoted', 'won' => 'Won', 'lost' => 'Lost'];

    public function customer(): BelongsTo { return $this->belongsTo(Customer::class); }
    public function user(): BelongsTo { return $this->belongsTo(User::class); }
    public function quotations(): HasMany { return $this->hasMany(Quotation::class)->latest(); }
}
