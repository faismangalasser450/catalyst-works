<?php

namespace App\Http\Controllers;

use App\Models\AmcContract;
use App\Models\Complaint;
use App\Models\Enquiry;
use App\Models\Invoice;
use App\Models\PpmVisit;
use App\Models\Quotation;
use App\Models\User;
use Illuminate\Http\Request;

class DashboardController extends Controller
{
    public function __invoke(Request $request)
    {
        if ($request->user()->isTechnician()) {
            return redirect()->route('my');
        }
        $u = $request->user();
        $now = now();
        $monthStart = $now->copy()->startOfMonth();
        $lastMonthStart = $monthStart->copy()->subMonth();
        $sla = (int) \App\Models\Setting::get('complaint_sla_hours', 48);

        $openComplaints = Complaint::where('status', '!=', 'closed');
        $kpi = [
            'open' => (clone $openComplaints)->count(),
            'overdue' => (clone $openComplaints)->where('reported_at', '<', $now->copy()->subHours($sla))->count(),
            'unassigned' => (clone $openComplaints)->whereNull('technician_id')->count(),
            'closed_this' => Complaint::where('closed_at', '>=', $monthStart)->count(),
            'closed_last' => Complaint::whereBetween('closed_at', [$lastMonthStart, $monthStart])->count(),
            'ppm_due' => PpmVisit::where('status', 'due')->whereBetween('scheduled_on', [$monthStart, $monthStart->copy()->endOfMonth()])->count(),
            'ppm_done' => PpmVisit::where('status', 'done')->whereBetween('done_on', [$monthStart, $monthStart->copy()->endOfMonth()])->count(),
            'ppm_missed' => PpmVisit::where('status', 'due')->where('scheduled_on', '<', $now->copy()->startOfDay())->count(),
            'renew30' => AmcContract::where('status', 'active')->whereBetween('end_date', [$now, $now->copy()->addDays(30)])->count(),
            'renew90' => AmcContract::where('status', 'active')->whereBetween('end_date', [$now, $now->copy()->addDays(90)])->count(),
            'enq_this' => Enquiry::where('date', '>=', $monthStart)->count(),
            'enq_open' => Enquiry::where('status', 'open')->count(),
            'q_sent_this' => Quotation::where('sent_at', '>=', $monthStart)->count(),
            'q_sent_value' => Quotation::where('sent_at', '>=', $monthStart)->sum('total'),
            'q_awaiting' => Quotation::where('status', 'sent')->count(),
            'q_awaiting_value' => Quotation::where('status', 'sent')->sum('total'),
            'outstanding' => Invoice::whereIn('status', ['unpaid', 'part_paid'])->get()->sum('balance'),
            'overdue_inv' => Invoice::whereIn('status', ['unpaid', 'part_paid'])->where('due_date', '<', $now)->count(),
        ];
        $decided = Quotation::where('decided_at', '>=', $now->copy()->subDays(90))->whereIn('status', ['approved', 'rejected', 'ordered', 'invoiced']);
        $kpi['conv_total'] = (clone $decided)->count();
        $kpi['conv_won'] = (clone $decided)->where('status', '!=', 'rejected')->count();
        $kpi['conv_pct'] = $kpi['conv_total'] ? round($kpi['conv_won'] / $kpi['conv_total'] * 100) : 0;

        // 12-week complaints received vs closed
        $weeks = [];
        for ($i = 11; $i >= 0; $i--) {
            $start = $now->copy()->startOfWeek()->subWeeks($i);
            $end = $start->copy()->endOfWeek();
            $weeks[] = [
                'label' => $start->format('d M'),
                'in' => Complaint::whereBetween('reported_at', [$start, $end])->count(),
                'out' => Complaint::whereBetween('closed_at', [$start, $end])->count(),
            ];
        }
        $since = $now->copy()->subDays(90);
        $funnel = [
            'Enquiries' => Enquiry::where('date', '>=', $since)->count(),
            'Quoted' => Quotation::where('date', '>=', $since)->count(),
            'Approved' => Quotation::where('date', '>=', $since)->whereIn('status', ['approved', 'ordered', 'invoiced'])->count(),
            'Invoiced' => Quotation::where('date', '>=', $since)->where('status', 'invoiced')->count(),
        ];

        $openList = Complaint::with(['customer', 'technician'])->where('status', '!=', 'closed')
            ->orderByRaw("case priority when 'high' then 0 else 1 end")->orderBy('reported_at')->limit(8)->get();
        $awaiting = Quotation::with('customer')->where('status', 'sent')->orderBy('sent_at')->limit(6)->get();
        $renewals = AmcContract::with('customer')->where('status', 'active')->where('end_date', '<=', $now->copy()->addDays(90))->orderBy('end_date')->limit(6)->get();
        $followUps = Enquiry::with('customer')->where('status', 'open')->when($u->role === 'sales', fn ($q) => $q->where('user_id', $u->id))->orderBy('date')->limit(6)->get();
        $overdueInvoices = Invoice::with('customer')->whereIn('status', ['unpaid', 'part_paid'])->where('due_date', '<', $now)->orderBy('due_date')->limit(6)->get();
        $technicians = User::where('role', 'technician')->where('active', true)->orderBy('name')->get();

        return view('dashboard', compact('kpi', 'weeks', 'funnel', 'openList', 'awaiting', 'renewals', 'followUps', 'overdueInvoices', 'technicians', 'sla'));
    }
}
