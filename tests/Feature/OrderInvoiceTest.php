<?php

namespace Tests\Feature;

use App\Livewire\Invoices\Index as Invoices;
use App\Livewire\Orders\Index as Orders;
use App\Models\Invoice;
use App\Models\Order;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class OrderInvoiceTest extends TestCase
{
    use RefreshDatabase;

    protected bool $seed = true;

    public function test_pages_render_and_actions_work(): void
    {
        $admin = User::where('role', 'admin')->first();
        $o = Order::whereIn('status', ['confirmed', 'in_progress'])->first() ?? Order::first();
        $i = Invoice::where('status', 'unpaid')->first() ?? Invoice::first();
        foreach ([route('orders.index'), route('orders.show', $o), route('invoices.index'), route('invoices.show', $i)] as $url) {
            $this->actingAs($admin)->get($url)->assertOk();
        }
        $this->actingAs($admin)->get(route('invoices.pdf', $i))->assertOk()->assertHeader('content-type', 'application/pdf');

        Livewire::actingAs($admin)->test(Orders::class)->call('setStatus', $o->id, 'delivered');
        $this->assertSame('delivered', $o->fresh()->status);
        $this->assertNotNull($o->fresh()->delivered_on);

        Livewire::actingAs($admin)->test(Invoices::class)->call('startPay', $i->id)->assertSet('amount', (string) $i->balance)
            ->set('amount', round($i->total / 2, 2))->call('pay')->assertHasNoErrors();
        $this->assertSame('part_paid', $i->fresh()->status);
        $this->actingAs($admin)->post(route('invoices.pay', $i), ['date' => today()->format('Y-m-d'), 'amount' => $i->fresh()->balance, 'method' => 'Cash'])->assertRedirect();
        $this->assertSame('paid', $i->fresh()->status);
        $this->assertEquals(0, $i->fresh()->balance);
    }
}
