<?php

namespace App\Http\Controllers;

use App\Http\Middleware\EnsureRole;
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
            'Customers' => Customer::whereLike('name', $like)->orWhereLike('phone', $like)->orWhereLike('contact_name', $like)->limit(10)->get()->map(fn ($c) => [route('customers.show', $c), $c->name, $c->city.' · '.$c->phone]),
            'Complaints' => Complaint::whereLike('code', $like)->orWhereLike('location', $like)->orWhereHas('customer', fn ($c) => $c->whereLike('name', $like))->with('customer')->latest('reported_at')->limit(10)->get()->map(fn ($c) => [route('complaints.show', $c), $c->code.' · '.$c->customer->name, $c->type.' · '.status_label($c->status)]),
            'Quotations' => Quotation::whereLike('code', $like)->orWhereLike('subject', $like)->orWhereHas('customer', fn ($c) => $c->whereLike('name', $like))->with('customer')->latest('date')->limit(10)->get()->map(fn ($x) => [route('quotations.show', $x), $x->ref.' · '.$x->customer->name, money($x->total, true).' · '.status_label($x->status)]),
            'Invoices' => Invoice::whereLike('code', $like)->orWhereLike('lpo_number', $like)->orWhereHas('customer', fn ($c) => $c->whereLike('name', $like))->with('customer')->latest('date')->limit(10)->get()->map(fn ($x) => [route('invoices.show', $x), $x->code.' · '.$x->customer->name, money($x->total, true).' · '.status_label($x->status)]),
            'AMC contracts' => AmcContract::whereLike('code', $like)->orWhereLike('site_name', $like)->orWhereHas('customer', fn ($c) => $c->whereLike('name', $like))->with('customer')->limit(10)->get()->map(fn ($x) => [route('contracts.show', $x), $x->code.' · '.$x->customer->name, $x->site_name.' · '.status_label($x->status)]),
        ];
        $areas = ['Customers' => 'customers', 'Complaints' => 'complaints', 'Quotations' => 'quotations', 'Invoices' => 'invoices', 'AMC contracts' => 'contracts'];
        $results = array_filter($results, fn ($k) => EnsureRole::allows($request->user(), $areas[$k]), ARRAY_FILTER_USE_KEY);
        $all = collect($results)->flatten(1);
        if ($all->count() === 1) {
            return redirect($all->first()[0]);
        }

        return view('search', ['q' => $q, 'results' => array_filter($results, fn ($r) => $r->isNotEmpty())]);
    }
}
