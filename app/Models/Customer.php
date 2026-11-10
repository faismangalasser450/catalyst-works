<?php

namespace App\Models;

use App\Models\Concerns\HasCode;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Customer extends Model
{
    use HasCode, HasFactory;

    protected $guarded = [];

    public function enquiries(): HasMany { return $this->hasMany(Enquiry::class)->latest('date'); }
    public function quotations(): HasMany { return $this->hasMany(Quotation::class)->latest('date'); }
    public function orders(): HasMany { return $this->hasMany(Order::class)->latest(); }
    public function invoices(): HasMany { return $this->hasMany(Invoice::class)->latest('date'); }
    public function complaints(): HasMany { return $this->hasMany(Complaint::class)->latest('reported_at'); }
    public function contracts(): HasMany { return $this->hasMany(AmcContract::class)->latest('end_date'); }

    public function getFullAddressAttribute(): string
    {
        return collect([$this->address, $this->city, $this->emirate])->filter()->implode(', ');
    }
}
