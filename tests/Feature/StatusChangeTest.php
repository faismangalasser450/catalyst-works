<?php

namespace Tests\Feature;

use App\Livewire\Complaints\Index;
use App\Livewire\Complaints\Show;
use App\Models\Complaint;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use PHPUnit\Framework\Attributes\TestWith;
use Tests\TestCase;

class StatusChangeTest extends TestCase
{
    use RefreshDatabase;

    protected bool $seed = true;

    #[TestWith(['closed', 'green'])]
    #[TestWith(['paid', 'green'])]
    #[TestWith(['approved', 'green'])]
    #[TestWith(['rejected', 'red'])]
    #[TestWith(['cancelled', 'red'])]
    #[TestWith(['overdue', 'red'])]
    #[TestWith(['open', 'red'])]
    #[TestWith(['assigned', 'amber'])]
    #[TestWith(['in_progress', 'blue'])]
    #[TestWith(['draft', 'slate'])]
    public function test_status_has_the_expected_colour(string $status, string $tone): void
    {
        $this->assertSame($tone, status_tone($status));
        $this->assertStringContainsString("bg-$tone-100", badge($status));
    }

    public function test_open_enquiry_is_amber_not_red(): void
    {
        $this->assertSame('amber', status_tone('open', 'enquiry'));
    }

    public function test_closing_from_the_list_asks_for_a_note_first(): void
    {
        $service = User::where('role', 'service')->firstOrFail();
        $complaint = Complaint::where('status', 'assigned')->firstOrFail();

        $list = Livewire::actingAs($service)->test(Index::class)->call('setStatus', $complaint->id, 'closed');

        $list->assertSet('closing', $complaint->id);
        $this->assertSame('assigned', $complaint->fresh()->status);

        $list->call('close')->assertHasErrors(['note' => 'required']);
        $this->assertSame('assigned', $complaint->fresh()->status);

        $list->set('note', 'Filter cleaned, cooling OK')->call('close')->assertHasNoErrors()->assertSet('closing', null);
        $this->assertSame('closed', $complaint->fresh()->status);
        $this->assertSame('Filter cleaned, cooling OK', $complaint->fresh()->closing_note);
        $this->assertNotNull($complaint->fresh()->closed_at);
    }

    public function test_technician_cannot_close_from_the_list(): void
    {
        $technician = User::where('role', 'technician')->firstOrFail();
        $complaint = Complaint::where('status', 'assigned')->firstOrFail();

        Livewire::actingAs($technician)->test(Index::class)
            ->set('closing', $complaint->id)->set('note', 'Done')->call('close')->assertForbidden();

        $this->assertSame('assigned', $complaint->fresh()->status);
    }

    public function test_closing_from_the_dashboard_opens_the_closing_note_on_the_complaint_page(): void
    {
        $service = User::where('role', 'service')->firstOrFail();
        $complaint = Complaint::where('status', 'assigned')->firstOrFail();

        $this->actingAs($service)->post(route('complaints.status', $complaint), ['status' => 'closed'])
            ->assertRedirect(route('complaints.show', [$complaint, 'close' => 1]));

        $this->assertSame('assigned', $complaint->fresh()->status);
        $this->actingAs($service)->get(route('complaints.show', [$complaint, 'close' => 1]))->assertOk()->assertSee('Closing note');
    }

    public function test_dashboard_status_menu_changes_status_in_one_post(): void
    {
        $service = User::where('role', 'service')->firstOrFail();
        $complaint = Complaint::where('status', 'assigned')->firstOrFail();

        $this->actingAs($service)->get('/dashboard')->assertOk()->assertSee('Change status to');
        $this->actingAs($service)->post(route('complaints.status', $complaint), ['status' => 'in_progress'])->assertRedirect();

        $this->assertSame('in_progress', $complaint->fresh()->status);
    }

    public function test_reopening_through_the_status_menu_clears_the_closed_date(): void
    {
        $service = User::where('role', 'service')->firstOrFail();
        $complaint = Complaint::where('status', 'closed')->firstOrFail();

        Livewire::actingAs($service)->test(Show::class, ['complaint' => $complaint])->call('setStatus', 'in_progress');

        $this->assertSame('in_progress', $complaint->fresh()->status);
        $this->assertNull($complaint->fresh()->closed_at);
    }
}
