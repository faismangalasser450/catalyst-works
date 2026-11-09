<?php

namespace Tests\Feature;

use App\Livewire\Quotations\Editor;
use App\Livewire\Quotations\Index;
use App\Models\Customer;
use App\Models\Enquiry;
use App\Models\Quotation;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class QuotationTest extends TestCase
{
    use RefreshDatabase;

    protected bool $seed = true;

    public function test_pages_render(): void
    {
        $admin = User::where('role', 'admin')->first();
        $q = Quotation::first();
        $e = Enquiry::first();
        $c = Customer::first();
        foreach ([route('quotations.index'), route('quotations.create'), route('quotations.create', ['enquiry' => $e->id]), route('quotations.show', $q), route('quotations.edit', $q),
            route('enquiries.index'), route('enquiries.create'), route('enquiries.show', $e), route('customers.index'), route('customers.create'), route('customers.show', $c)] as $url) {
            $this->actingAs($admin)->get($url)->assertOk();
        }
        $this->actingAs($admin)->get(route('quotations.pdf', $q))->assertOk()->assertHeader('content-type', 'application/pdf');
    }

    public function test_quotation_from_enquiry_is_prefilled_totals_and_flow(): void
    {
        $admin = User::where('role', 'admin')->first();
        $e = Enquiry::where('status', 'open')->first() ?? Enquiry::first();
        Livewire::actingAs($admin)->test(Editor::class, ['enquiry' => $e->id])
            ->assertSet('customer_id', $e->customer_id)->assertSet('subject', $e->description)
            ->set('items.0.description', 'Ducted split 3 TR')->set('items.0.qty', 2)->set('items.0.unit_price', 1000)
            ->set('discount_pct', '10')->set('vat_pct', '5')
            ->call('save')->assertHasNoErrors()->assertRedirect();
        $q = Quotation::latest('id')->first();
        $this->assertSame('draft', $q->status);
        $this->assertEquals(2000, $q->subtotal);
        $this->assertEquals(200, $q->discount_amt);
        $this->assertEquals(90, $q->vat_amt);
        $this->assertEquals(1890, $q->total);
        $this->assertSame('quoted', $e->fresh()->status);

        Livewire::actingAs($admin)->test(Index::class)->call('markSent', $q->id)->call('approve', $q->id);
        $this->assertSame('approved', $q->fresh()->status);
        $this->assertSame('won', $e->fresh()->status);

        $this->actingAs($admin)->get(route('quotations.order', $q))->assertRedirect();
        $this->assertSame('ordered', $q->fresh()->status);
        $this->assertEquals(1890, $q->fresh()->order->total);
        $this->actingAs($admin)->get(route('quotations.invoice', $q))->assertRedirect();
        $this->assertSame('invoiced', $q->fresh()->status);
        $this->assertEquals(1890, $q->fresh()->invoice->total);
        $this->assertCount(1, $q->fresh()->invoice->items);
    }

    public function test_reject_needs_reason_and_revision_copies_items(): void
    {
        $admin = User::where('role', 'admin')->first();
        $q = Quotation::where('status', 'sent')->first();
        Livewire::actingAs($admin)->test(Index::class)->set('rejecting', $q->id)->call('reject')->assertHasErrors('reason')
            ->set('reason', 'Too expensive')->call('reject')->assertHasNoErrors();
        $this->assertSame('rejected', $q->fresh()->status);
        $this->actingAs($admin)->get(route('quotations.revise', $q))->assertRedirect();
        $new = Quotation::latest('id')->first();
        $this->assertSame($q->code, $new->code);
        $this->assertSame($q->revision + 1, $new->revision);
        $this->assertCount($q->items()->count(), $new->items);
    }

    public function test_next_code_skips_revisions_of_older_quotations(): void
    {
        $old = \App\Models\Quotation::where('kind', 'sales')->orderBy('id')->first();
        $old->update(['status' => 'sent']);
        app(\App\Services\QuotationActions::class)->revise($old);
        $code = \App\Models\Quotation::nextCode('sales');
        $this->assertDatabaseMissing('quotations', ['code' => $code]);
    }
}
