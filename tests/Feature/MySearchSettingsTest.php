<?php

namespace Tests\Feature;

use App\Livewire\My\Jobs;
use App\Models\Complaint;
use App\Models\Setting;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class MySearchSettingsTest extends TestCase
{
    use RefreshDatabase;

    protected bool $seed = true;

    public function test_technician_closes_own_job_from_my_jobs(): void
    {
        $c = Complaint::whereNotNull('technician_id')->where('status', '!=', 'closed')->first();
        $tech = $c->technician;
        $this->actingAs($tech)->get('/my')->assertOk()->assertSee($c->code);
        Livewire::actingAs($tech)->test(Jobs::class)->call('status', $c->id, 'visited');
        $this->assertSame('visited', $c->fresh()->status);
        Livewire::actingAs($tech)->test(Jobs::class)->call('status', $c->id, 'closed')->assertSet('closing', $c->id)
            ->call('close')->assertHasErrors('note')->set('note', 'Replaced capacitor')->call('close')->assertHasNoErrors();
        $this->assertSame('closed', $c->fresh()->status);
        $this->assertSame('Replaced capacitor', $c->fresh()->closing_note);
    }

    public function test_search_and_settings(): void
    {
        $admin = User::where('role', 'admin')->first();
        $c = Complaint::first();
        $this->actingAs($admin)->get('/search?q='.$c->code)->assertRedirect(route('complaints.show', $c));
        $this->actingAs($admin)->get('/search?q=a')->assertOk();
        $this->actingAs($admin)->get('/settings')->assertOk();
        $this->actingAs(User::where('role', 'sales')->first())->get('/settings')->assertForbidden();
        $this->actingAs($admin)->put('/settings', ['company_name' => 'Catalyst AC Units Fix Cont.', 'vat_pct' => 5, 'quote_valid_days' => 20, 'invoice_due_days' => 30, 'complaint_sla_hours' => 36])->assertRedirect();
        $this->assertEquals(20, Setting::get('quote_valid_days'));
        $this->actingAs($admin)->post('/settings/users', ['name' => 'New Tech', 'email' => 'newtech@example.com', 'role' => 'technician', 'password' => 'password123'])->assertRedirect();
        $this->assertTrue(User::where('email', 'newtech@example.com')->exists());
    }
}
