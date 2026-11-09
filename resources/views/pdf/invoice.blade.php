@php
    $words = ucfirst((new NumberFormatter('en', NumberFormatter::SPELLOUT))->format((int) $i->total));
    $fils = (int) round(($i->total - floor($i->total)) * 100);
@endphp
<!DOCTYPE html>
<html><head><meta charset="utf-8">
<style>
    @page { margin: 0; } body { font-family: DejaVu Sans, sans-serif; font-size: 10.5px; color: #222; margin: 0; }
    .header { width: 100%; } .footer { position: fixed; bottom: 0; left: 0; right: 0; width: 100%; } .page { padding: 10px 34px 150px 34px; }
    h1 { font-size: 15px; text-align: center; letter-spacing: 2px; margin: 10px 0 12px; text-decoration: underline; }
    table { width: 100%; border-collapse: collapse; } .meta td { padding: 2px 4px; vertical-align: top; }
    .items th { background: #e8eef7; border: 1px solid #999; padding: 5px 4px; font-size: 10px; text-align: left; } .items td { border: 1px solid #999; padding: 5px 4px; vertical-align: top; }
    .r { text-align: right; } .c { text-align: center; } .tot td { border: none; padding: 3px 4px; } .box { border: 1px solid #999; padding: 6px 8px; margin-top: 10px; } .muted { color: #555; } .b { font-weight: bold; }
</style></head>
<body>
<img class="header" src="{{ public_path('brand/header.jpg') }}"><img class="footer" src="{{ public_path('brand/footer.jpg') }}">
<div class="page">
    <h1>TAX INVOICE</h1>
    <table class="meta"><tr>
        <td style="width:55%"><span class="muted">Bill to,</span><br><span class="b">{{ $i->customer->name }}</span><br>{{ $i->customer->full_address }}<br>@if($i->customer->trn)TRN: {{ $i->customer->trn }}<br>@endif @if($i->customer->contact_name)Attn: {{ $i->customer->contact_name }}@endif</td>
        <td><table class="meta">
            <tr><td class="muted">Invoice No</td><td class="b">{{ $i->code }}</td></tr>
            <tr><td class="muted">Date</td><td>{{ dmy($i->date) }}</td></tr>
            <tr><td class="muted">Due date</td><td>{{ dmy($i->due_date) }}</td></tr>
            @if($i->lpo_number)<tr><td class="muted">Your LPO</td><td>{{ $i->lpo_number }}</td></tr>@endif
            @if($i->quotation)<tr><td class="muted">Our quotation</td><td>{{ $i->quotation->ref }}</td></tr>@endif
            <tr><td class="muted">Our TRN</td><td>{{ $s['company_trn'] ?? '' }}</td></tr>
        </table></td>
    </tr></table>
    <table class="items" style="margin-top:10px">
        <thead><tr><th style="width:5%" class="c">Sl</th><th>Description</th><th style="width:7%" class="c">Qty</th><th style="width:13%" class="r">Unit price</th><th style="width:12%" class="r">VAT {{ $i->vat_pct + 0 }}%</th><th style="width:15%" class="r">Amount (AED)</th></tr></thead>
        <tbody>@foreach($i->items as $it)<tr><td class="c">{{ $loop->iteration }}</td><td>{{ $it->description }}</td><td class="c">{{ $it->qty + 0 }}</td><td class="r">{{ money($it->unit_price) }}</td><td class="r">{{ money($it->total * $i->vat_pct / 100) }}</td><td class="r">{{ money($it->total) }}</td></tr>@endforeach</tbody>
    </table>
    <table class="tot" style="margin-top:4px"><tr><td style="width:58%; vertical-align:top"><span class="muted">Amount in words:</span> <span class="b">UAE Dirhams {{ $words }}@if($fils) and {{ $fils }}/100 @endif only</span></td>
        <td><table class="tot">
            <tr><td class="r muted">Subtotal</td><td class="r" style="width:110px">{{ money($i->subtotal) }}</td></tr>
            @if($i->discount_amt > 0)<tr><td class="r muted">Discount</td><td class="r">- {{ money($i->discount_amt) }}</td></tr>@endif
            <tr><td class="r muted">VAT {{ $i->vat_pct + 0 }}%</td><td class="r">{{ money($i->vat_amt) }}</td></tr>
            <tr class="b" style="font-size:12px"><td class="r">Total</td><td class="r" style="border-top:1px solid #999">AED {{ money($i->total) }}</td></tr>
            @if($i->paid_amount > 0)<tr><td class="r muted">Paid</td><td class="r">{{ money($i->paid_amount) }}</td></tr><tr class="b"><td class="r">Balance due</td><td class="r">AED {{ money($i->balance) }}</td></tr>@endif
        </table></td></tr></table>
    <div class="box"><div class="b" style="margin-bottom:4px">Bank details</div>{{ $s['bank_name'] ?? '' }}@if(!empty($s['bank_branch'])), {{ $s['bank_branch'] }} branch @endif · Account name: {{ $s['company_name'] ?? '' }} · A/C: {{ $s['bank_account'] ?? '' }} · IBAN: {{ $s['bank_iban'] ?? '' }}</div>
    @if($i->notes)<div class="box">{{ $i->notes }}</div>@endif
    <table style="margin-top:28px"><tr><td style="width:50%"><span class="b">For {{ $s['company_name'] ?? '' }}</span><br><br><br>______________________<br>Authorised signatory</td><td class="r" style="vertical-align:bottom"><span class="muted">Received by</span><br><br><br>______________________<br>Name, signature & stamp</td></tr></table>
</div>
</body></html>
