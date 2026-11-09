<?php

namespace App\Http\Controllers;

use App\Models\PpmVisit;
use Illuminate\Http\Request;

class PpmController extends Controller
{
    public function done(Request $request, PpmVisit $visit)
    {
        $data = $request->validate(['technician_id' => 'nullable|exists:users,id', 'notes' => 'nullable|string|max:500']);
        $visit->update(['status' => 'done', 'done_on' => today(), 'technician_id' => $data['technician_id'] ?? $visit->technician_id, 'notes' => $data['notes'] ?? $visit->notes]);
        $visit->contract->log('ppm', 'Visit planned '.dmy($visit->scheduled_on).' done');

        return back()->with('ok', 'PPM visit marked done.');
    }
}
