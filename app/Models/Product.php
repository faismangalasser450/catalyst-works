<?php

namespace App\Models;

use App\Models\Concerns\HasCode;
use Illuminate\Database\Eloquent\Model;

class Product extends Model
{
    use HasCode;

    protected $guarded = [];

    public function getLabelAttribute(): string
    {
        return collect([$this->brand, $this->name, $this->model, $this->capacity])->filter()->implode(' · ');
    }
}
