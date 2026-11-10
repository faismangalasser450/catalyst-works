<?php

namespace App\Http\Controllers;

use App\Models\Quotation;
use App\Models\Setting;
use App\Services\QuotationActions;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;

class QuotationController extends Controller
{
    public function __construct(private QuotationActions $actions) {}

    public function show(Quotation $quotation)
    {
        $quotation->load(['customer', 'user', 'enquiry', 'items', 'order', 'invoice', 'contract', 'activities.user']);

        return view('quotations.show', compact('quotation'));
    }

    public function sent(Quotation $quotation)
    {
        $this->actions->markSent($quotation);

        return back()->with('ok', $quotation->ref.' marked as sent.');
    }

    public function approve(Quotation $quotation)
    {
        if (! $this->actions->approve($quotation)) {
            return back()->with('err', $quotation->ref.' is already '.$quotation->status.' and cannot be approved.');
        }

        return back()->with('ok', $quotation->ref.' approved. Next: create the order.');
    }

    public function reject(Request $request, Quotation $quotation)
    {
        $this->actions->reject($quotation, $request->validate(['reason' => 'required|string|max:200'])['reason']);

        return back()->with('ok', $quotation->ref.' marked as rejected.');
    }

    public function revise(Quotation $quotation)
    {
        $new = $this->actions->revise($quotation);

        return redirect()->route('quotations.edit', $new)->with('ok', 'Revision '.$new->ref.' created. Edit and send it.');
    }

    public function order(Quotation $quotation)
    {
        abort_unless($quotation->status === 'approved', 422, 'Only approved quotations can be ordered.');
        if ($quotation->kind === 'amc') {
            $c = $this->actions->createContract($quotation);

            return redirect()->route('contracts.show', $c)->with('ok', 'AMC '.$c->code.' started, PPM visits scheduled.');
        }
        $o = $this->actions->createOrder($quotation);

        return redirect()->route('orders.show', $o)->with('ok', 'Order '.$o->code.' created.');
    }

    public function invoice(Quotation $quotation)
    {
        abort_unless(in_array($quotation->status, ['approved', 'ordered']), 422, 'Approve the quotation first.');
        $inv = $this->actions->createInvoice($quotation, $quotation->order, $quotation->contract);

        return redirect()->route('invoices.show', $inv)->with('ok', 'Invoice '.$inv->code.' created.');
    }

    public function pdf(Quotation $quotation)
    {
        $quotation->load(['customer', 'user', 'items']);
        $pdf = Pdf::loadView('pdf.quotation', ['q' => $quotation, 's' => Setting::map()])->setPaper('a4');

        return $pdf->stream(str_replace('/', '-', $quotation->ref).'.pdf');
    }
}
