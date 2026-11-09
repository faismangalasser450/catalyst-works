<?php

namespace App\Http\Controllers;

use App\Models\AmcContract;
use App\Models\Complaint;
use App\Models\Customer;
use App\Models\Invoice;
use App\Models\Quotation;
use Illuminate\Http\Request;

class SearchController extends Controller
{
    public function __invoke(Request $request)
    {
        $q = trim((string) $request->input('q'));
        if ($q === '') {
            return redirect()->route('dashboard');
        }
        $like = "%$q%";
        $results = [
            'Customers' => Customer::where('name', 'like', $like)->orWhere('phone', 'like', $like)->orWhere('contact_name', 'like', $like)->limit(10)->get()->map(fn ($c) => [route('customers.show', $c), $c->name, $c->city.' · '.$c->phone]),
            'Complaints' => Complaint::where('code', 'like', $like)->orWhere('location', 'like', $like)->orWhereHas('customer', fn ($c) => $c->where('name', 'like', $like))->with('customer')->latest('reported_at')->limit(10)->get()->map(fn ($c) => [route('complaints.show', $c), $c->code.' · '.$c->customer->name, $c->type.' · '.status_label($c->status)]),
            'Quotations' => Quotation::where('code', 'like', $like)->orWhere('subject', 'like', $like)->orWhereHas('customer', fn ($c) => $c->where('name', 'like', $like))->with('customer')->latest('date')->limit(10)->get()->map(fn ($x) => [route('quotations.show', $x), $x->ref.' · '.$x->customer->name, money($x->total, true).' · '.status_label($x->status)]),
            'Invoices' => Invoice::where('code', 'like', $like)->orWhere('lpo_number', 'like', $like)->orWhereHas('customer', fn ($c) => $c->where('name', 'like', $like))->with('customer')->latest('date')->limit(10)->get()->map(fn ($x) => [route('invoices.show', $x), $x->code.' · '.$x->customer->name, money($x->total, true).' · '.status_label($x->status)]),
            'AMC contracts' => AmcContract::where('code', 'like', $like)->orWhere('site_name', 'like', $like)->orWhereHas('customer', fn ($c) => $c->where('name', 'like', $like))->with('customer')->limit(10)->get()->map(fn ($x) => [route('contracts.show', $x), $x->code.' · '.$x->customer->name, $x->site_name.' · '.status_label($x->status)]),
        ];
        $areas = ['Customers' => 'customers', 'Complaints' => 'complaints', 'Quotations' => 'quotations', 'Invoices' => 'invoices', 'AMC contracts' => 'contracts'];
        $results = array_filter($results, fn ($k) => \App\Http\Middleware\EnsureRole::allows($request->user(), $areas[$k]), ARRAY_FILTER_USE_KEY);
        $all = collect($results)->flatten(1);
        if ($all->count() === 1) {
            return redirect($all->first()[0]);
        }

        return view('search', ['q' => $q, 'results' => array_filter($results, fn ($r) => $r->isNotEmpty())]);
    }
}
