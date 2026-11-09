<?php

namespace App\Http\Controllers;

use App\Models\Invoice;
use App\Models\Setting;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;

class InvoiceController extends Controller
{
    public function show(Invoice $invoice)
    {
        $invoice->load(['customer', 'quotation', 'order', 'contract', 'items', 'payments', 'activities.user']);

        return view('invoices.show', compact('invoice'));
    }

    public function pay(Request $request, Invoice $invoice)
    {
        $data = $request->validate(['date' => 'required|date', 'amount' => 'required|numeric|min:0.01|max:'.$invoice->balance, 'method' => 'required|string|max:40', 'reference' => 'nullable|string|max:60']);
        $invoice->payments()->create($data + ['user_id' => $request->user()->id]);
        $invoice->refreshPaidStatus();
        $invoice->log('payment', money($data['amount'], true).' '.$data['method'].(! empty($data['reference']) ? ' '.$data['reference'] : ''));

        return back()->with('ok', 'Payment recorded. '.($invoice->balance > 0 ? money($invoice->balance, true).' still due.' : 'Invoice fully paid.'));
    }

    public function cancel(Invoice $invoice)
    {
        abort_if($invoice->paid_amount > 0, 422, 'Cannot cancel an invoice with payments.');
        $invoice->update(['status' => 'cancelled']);
        $invoice->log('status', 'Cancelled');

        return back()->with('ok', $invoice->code.' cancelled.');
    }

    public function pdf(Invoice $invoice)
    {
        $invoice->load(['customer', 'items', 'order', 'quotation.user']);

        return Pdf::loadView('pdf.invoice', ['i' => $invoice, 's' => Setting::map()])->setPaper('a4')->stream($invoice->code.'.pdf');
    }
}
