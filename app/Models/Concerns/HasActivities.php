<?php

namespace App\Models\Concerns;

use App\Models\Activity;
use Illuminate\Database\Eloquent\Relations\MorphMany;

trait HasActivities
{
    public function activities(): MorphMany
    {
        return $this->morphMany(Activity::class, 'subject')->latest();
    }

    public function log(string $action, ?string $note = null): Activity
    {
        return $this->activities()->create([
            'user_id' => auth()->id(),
            'action' => $action,
            'note' => $note,
        ]);
    }
}
