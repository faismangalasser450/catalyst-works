<?php

namespace Tests\Feature;

use App\Livewire\Complaints\Index;
use App\Livewire\Complaints\Show;
use App\Models\Complaint;
use App\Models\Customer;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class SmokeTest extends TestCase
{
    use RefreshDatabase;

    protected bool $seed = true;

    private User $admin;

    protected function setUp(): void
    {
        parent::setUp();
        $this->admin = User::where('role', 'admin')->firstOrFail();
    }

    public function test_main_pages_render_for_admin(): void
    {
        $c = Complaint::first();
        foreach ([
            route('dashboard'), route('complaints.index'), route('complaints.create'),
            route('complaints.show', $c), route('complaints.edit', $c),
        ] as $url) {
            $this->actingAs($this->admin)->get($url)->assertOk();
        }
    }

    public function test_short_form_registers_complaint_as_open(): void
    {
        $this->actingAs($this->admin)->post(route('complaints.store'), [
            'customer_id' => Customer::first()->id, 'location' => 'Flat 12', 'type' => 'Not cooling', 'equipment' => 'Split',
            'description' => 'No cooling since morning', 'priority' => 'normal',
        ])->assertRedirect();
        $c = Complaint::latest('id')->first();
        $this->assertSame('open', $c->status);
        $this->assertSame('Flat 12', $c->location);
        $this->assertNotNull($c->reported_at);
    }

    public function test_status_from_list_and_close_from_page(): void
    {
        $c = Complaint::where('status', 'assigned')->firstOrFail();
        Livewire::actingAs($this->admin)->test(Index::class)->call('setStatus', $c->id, 'in_progress');
        $this->assertSame('in_progress', $c->fresh()->status);

        Livewire::actingAs($this->admin)->test(Show::class, ['complaint' => $c->fresh()])
            ->call('logVisit')->assertHasErrors('notes')
            ->set('notes', 'Cleaned drain')->call('logVisit')->assertHasNoErrors()
            ->set('closing_note', 'Done')->call('close');
        $this->assertSame('closed', $c->fresh()->status);
        $this->assertSame(1, $c->fresh()->visits()->count());
    }
}
