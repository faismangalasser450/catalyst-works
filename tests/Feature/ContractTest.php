<?php

namespace Tests\Feature;

use App\Livewire\Ppm\Index as Ppm;
use App\Models\AmcContract;
use App\Models\PpmVisit;
use App\Models\Quotation;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class ContractTest extends TestCase
{
    use RefreshDatabase;

    protected bool $seed = true;

    public function test_contracts_ppm_and_renewal(): void
    {
        $admin = User::where('role', 'admin')->first();
        $c = AmcContract::where('status', 'active')->first();
        foreach ([route('contracts.index'), route('contracts.show', $c), route('ppm.index')] as $url) {
            $this->actingAs($admin)->get($url)->assertOk();
        }
        $v = PpmVisit::where('status', 'due')->first();
        Livewire::actingAs($admin)->test(Ppm::class)->call('done', $v->id);
        $this->assertSame('done', $v->fresh()->status);

        $before = Quotation::count();
        $this->actingAs($admin)->get(route('contracts.renew', $c))->assertRedirect();
        $this->assertSame($before + 1, Quotation::count());
        $q = Quotation::latest('id')->first();
        $this->assertSame('amc', $q->kind);
        $this->assertSame($c->customer_id, $q->customer_id);
        $this->assertEqualsWithDelta($c->value, $q->total, 1.0);
    }
}
