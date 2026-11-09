<?php

namespace Tests\Feature;

use App\Models\Complaint;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class RoleTest extends TestCase
{
    use RefreshDatabase;

    protected bool $seed = true;

    public function test_roles_are_fenced(): void
    {
        $tech = User::where('role', 'technician')->first();
        $accounts = User::where('role', 'accounts')->first();
        $service = User::where('role', 'service')->first();
        $c = Complaint::first();

        $this->actingAs($tech)->get('/my')->assertOk();
        $this->actingAs($tech)->get(route('complaints.show', $c))->assertOk();
        $this->actingAs($tech)->get('/quotations')->assertForbidden();
        $this->actingAs($tech)->get('/invoices')->assertForbidden();
        $this->actingAs($tech)->post(route('complaints.status', $c), ['status' => 'closed'])->assertForbidden();

        $this->actingAs($tech)->get('/dashboard')->assertRedirect(route('my'));
        $this->actingAs($accounts)->get('/dashboard')->assertOk()->assertDontSee(route('complaints.assign', $c));
        $this->actingAs($accounts)->get('/invoices')->assertOk();
        $this->actingAs($accounts)->get('/complaints')->assertForbidden();
        $this->actingAs($accounts)->get('/settings')->assertForbidden();

        $this->actingAs($accounts)->get('/search?q=CMP-')->assertDontSee('CMP-0');
        $draft = \App\Models\Quotation::where('status', 'sent')->first();
        \Livewire\Livewire::actingAs($accounts)->test(\App\Livewire\Quotations\Index::class)->call('approve', $draft->id)->assertForbidden();
        \Livewire\Livewire::actingAs($tech)->test(\App\Livewire\Complaints\Index::class)->call('assign', $c->id, (string) $tech->id)->assertForbidden();
        \Livewire\Livewire::actingAs($service)->test(\App\Livewire\Complaints\Index::class)->call('setStatus', $c->id, 'closed')->assertStatus(422);
        $this->assertSame('sent', $draft->fresh()->status);
        \Livewire\Livewire::actingAs($tech)->test(\App\Livewire\Complaints\Show::class, ['complaint' => $c])->call('assign', (string) $tech->id)->assertForbidden();
        $this->actingAs($service)->get('/complaints')->assertOk();
        $this->actingAs($service)->get('/invoices')->assertForbidden();
        $this->actingAs($service)->post(route('complaints.status', $c), ['status' => 'in_progress'])->assertRedirect();
    }
}
