<?php

namespace App\Http\Controllers;

use App\Models\Order;
use App\Models\User;
use Illuminate\Http\Request;

class OrderController extends Controller
{
    public function show(Order $order)
    {
        $order->load(['customer', 'quotation.items', 'technician', 'invoice', 'activities.user']);

        return view('orders.show', ['order' => $order, 'technicians' => User::where('role', 'technician')->where('active', true)->orderBy('name')->get()]);
    }

    public function update(Request $request, Order $order)
    {
        $data = $request->validate(['po_number' => 'nullable|string|max:60', 'po_date' => 'nullable|date', 'start_date' => 'nullable|date', 'due_date' => 'nullable|date', 'technician_id' => 'nullable|exists:users,id', 'notes' => 'nullable|string|max:1000']);
        $order->update($data);
        $order->log('edited');

        return back()->with('ok', 'Saved.');
    }

    public function status(Request $request, Order $order)
    {
        $status = $request->validate(['status' => 'required|in:'.implode(',', array_keys(Order::STATUSES))])['status'];
        $order->update(['status' => $status, 'delivered_on' => $status === 'delivered' ? ($order->delivered_on ?? today()) : $order->delivered_on]);
        $order->log('status', Order::STATUSES[$status]);

        return back()->with('ok', $order->code.' is now '.Order::STATUSES[$status].'.');
    }
}
