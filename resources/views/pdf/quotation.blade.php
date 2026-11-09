@php
    $words = ucfirst((new NumberFormatter('en', NumberFormatter::SPELLOUT))->format((int) $q->total));
    $fils = (int) round(($q->total - floor($q->total)) * 100);
@endphp
<!DOCTYPE html>
<html><head><meta charset="utf-8">
<style>
    @page { margin: 0 0 0 0; }
    body { font-family: DejaVu Sans, sans-serif; font-size: 10.5px; color: #222; margin: 0; }
    .header { width: 100%; } .footer { position: fixed; bottom: 0; left: 0; right: 0; width: 100%; }
    .page { padding: 10px 34px 150px 34px; }
    h1 { font-size: 15px; text-align: center; letter-spacing: 2px; margin: 10px 0 12px; text-decoration: underline; }
    table { width: 100%; border-collapse: collapse; }
    .meta td { padding: 2px 4px; vertical-align: top; }
    .items th { background: #e8eef7; border: 1px solid #999; padding: 5px 4px; font-size: 10px; text-align: left; }
    .items td { border: 1px solid #999; padding: 5px 4px; vertical-align: top; }
    .r { text-align: right; } .c { text-align: center; }
    .tot td { border: none; padding: 3px 4px; }
    .box { border: 1px solid #999; padding: 6px 8px; margin-top: 10px; }
    .muted { color: #555; } .b { font-weight: bold; }
    .sign { margin-top: 28px; }
</style></head>
<body>
<img class="header" src="{{ public_path('brand/header.jpg') }}">
<img class="footer" src="{{ public_path('brand/footer.jpg') }}">
<div class="page">
    <h1>{{ $q->kind === 'amc' ? 'ANNUAL MAINTENANCE QUOTATION' : 'QUOTATION' }}</h1>
    <table class="meta"><tr>
        <td style="width:55%">
            <span class="muted">To,</span><br>
            <span class="b">{{ $q->customer->name }}</span><br>
            {{ $q->customer->full_address }}<br>
            @if($q->customer->contact_name)Attn: {{ $q->customer->contact_name }}@if($q->customer->contact_phone || $q->customer->phone) · {{ $q->customer->contact_phone ?: $q->customer->phone }}@endif<br>@endif
            @if($q->customer->trn)TRN: {{ $q->customer->trn }}@endif
        </td>
        <td>
            <table class="meta">
                <tr><td class="muted">Quotation No</td><td class="b">{{ $q->ref }}</td></tr>
                <tr><td class="muted">Date</td><td>{{ dmy($q->date) }}</td></tr>
                <tr><td class="muted">Valid until</td><td>{{ dmy($q->valid_until) }}</td></tr>
                @if($q->enquiry?->client_ref)<tr><td class="muted">Your ref</td><td>{{ $q->enquiry->client_ref }}</td></tr>@endif
                <tr><td class="muted">Prepared by</td><td>{{ $q->user->name }}@if($q->user->phone), {{ $q->user->phone }}@endif</td></tr>
            </table>
        </td>
    </tr></table>

    <p style="margin:10px 0 8px"><span class="b">Subject: {{ $q->subject }}</span><br>Dear Sir / Madam,<br>Thank you for your enquiry. We are pleased to quote as follows:</p>

    <table class="items">
        <thead><tr><th style="width:5%" class="c">Sl</th><th style="width:14%">Brand</th><th>Description</th><th style="width:11%">Capacity</th><th style="width:7%" class="c">Qty</th><th style="width:13%" class="r">Unit price</th><th style="width:14%" class="r">Amount (AED)</th></tr></thead>
        <tbody>
        @foreach($q->items as $it)<tr><td class="c">{{ $loop->iteration }}</td><td>{{ $it->brand }}</td><td>{{ $it->description }}</td><td>{{ $it->capacity }}</td><td class="c">{{ $it->qty + 0 }}</td><td class="r">{{ money($it->unit_price) }}</td><td class="r">{{ money($it->total) }}</td></tr>@endforeach
        </tbody>
    </table>
    <table class="tot" style="margin-top:4px"><tr><td style="width:60%; vertical-align:top">
            <span class="muted">Amount in words:</span> <span class="b">UAE Dirhams {{ $words }}@if($fils) and {{ $fils }}/100 @endif only</span>
        </td><td>
            <table class="tot">
                <tr><td class="r muted">Subtotal</td><td class="r" style="width:110px">{{ money($q->subtotal) }}</td></tr>
                @if($q->discount_amt > 0)<tr><td class="r muted">Discount {{ $q->discount_pct + 0 }}%</td><td class="r">- {{ money($q->discount_amt) }}</td></tr>@endif
                <tr><td class="r muted">VAT {{ $q->vat_pct + 0 }}%</td><td class="r">{{ money($q->vat_amt) }}</td></tr>
                <tr class="b" style="font-size:12px"><td class="r">Grand total</td><td class="r" style="border-top:1px solid #999">AED {{ money($q->total) }}</td></tr>
            </table>
        </td></tr></table>

    <div class="box">
        <div class="b" style="margin-bottom:4px">Terms & conditions</div>
        <table class="meta">
            <tr><td class="muted" style="width:90px">Payment</td><td>{{ $q->payment_terms }}</td></tr>
            <tr><td class="muted">Delivery</td><td>{{ $q->delivery_terms }}</td></tr>
            <tr><td class="muted">Warranty</td><td>{{ $q->warranty_terms }}</td></tr>
            <tr><td class="muted">Availability</td><td>{{ $q->availability }}</td></tr>
            <tr><td class="muted">Validity</td><td>{{ $q->valid_days }} days from the date of this quotation</td></tr>
            @if($q->notes)<tr><td class="muted">Note</td><td>{{ $q->notes }}</td></tr>@endif
        </table>
    </div>
    <div class="box">
        <div class="b" style="margin-bottom:4px">Bank details</div>
        {{ $s['bank_name'] ?? '' }}@if(!empty($s['bank_branch'])), {{ $s['bank_branch'] }} branch @endif · Account name: {{ $s['company_name'] ?? '' }} · A/C: {{ $s['bank_account'] ?? '' }} · IBAN: {{ $s['bank_iban'] ?? '' }}
    </div>
    <table class="sign"><tr>
        <td style="width:50%">We trust the above is in line with your requirement and look forward to your confirmation.<br><br><span class="b">For {{ $s['company_name'] ?? 'Catalyst AC Units Fix Cont.' }}</span><br><br><br>______________________<br>{{ $q->user->name }}@if($q->user->designation), {{ $q->user->designation }}@endif</td>
        <td class="r" style="vertical-align:bottom"><span class="muted">Accepted by (customer)</span><br><br><br>______________________<br>Name, signature & stamp</td>
    </tr></table>
</div>
</body></html>
