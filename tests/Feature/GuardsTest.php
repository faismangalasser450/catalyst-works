<?php

namespace Tests\Feature;

use App\Models\Invoice;
use App\Models\Quotation;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class GuardsTest extends TestCase
{
    use RefreshDatabase;

    protected bool $seed = true;

    public function test_complaint_registers_with_site_contact(): void
    {
        $this->actingAs(User::where('role', 'service')->first())
            ->post(route('complaints.store'), ['customer_id' => 1, 'location' => 'Flat 604', 'contact_name' => 'Mr. Ali', 'contact_phone' => '0501234567', 'type' => 'Not cooling', 'equipment' => 'Split', 'description' => 'Bedroom unit not cooling', 'priority' => 'normal'])
            ->assertSessionHasNoErrors()->assertRedirect();
        $this->assertDatabaseHas('complaints', ['location' => 'Flat 604', 'contact_name' => 'Mr. Ali', 'contact_phone' => '0501234567']);
    }

    public function test_payment_cannot_exceed_balance(): void
    {
        $inv = Invoice::where('status', 'unpaid')->first();
        $acc = User::where('role', 'accounts')->first();
        $this->actingAs($acc)->post(route('invoices.pay', $inv), ['date' => today()->toDateString(), 'amount' => $inv->balance + 1, 'method' => 'Cash'])->assertSessionHasErrors('amount');
        Livewire::actingAs($acc)->test(\App\Livewire\Invoices\Index::class)->call('startPay', $inv->id)->set('amount', $inv->balance + 1)->call('pay')->assertHasErrors('amount');
        $this->assertSame(0.0, (float) $inv->fresh()->paid_amount);
    }

    public function test_quotation_line_needs_positive_qty(): void
    {
        $q = Quotation::where('status', 'draft')->first();
        Livewire::actingAs(User::where('role', 'sales')->first())->test(\App\Livewire\Quotations\Editor::class, ['quotation' => $q])
            ->set('items.0.qty', 0)->call('save')->assertHasErrors('items.0.qty')->assertSee('Qty');
    }
}
