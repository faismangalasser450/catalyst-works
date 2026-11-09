<?php

namespace Database\Seeders;

use App\Models\AmcContract;
use App\Models\Complaint;
use App\Models\Customer;
use App\Models\Enquiry;
use App\Models\Invoice;
use App\Models\Order;
use App\Models\Product;
use App\Models\Quotation;
use App\Models\Setting;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Carbon;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        mt_srand(7);

        foreach ([
            'company_name' => 'Catalyst AC Units Fix Cont.',
            'company_address' => 'Warehouse No 8, Industrial Area 13, Sharjah 61495, UAE',
            'company_phone' => '+971 50 192 9869',
            'company_email' => 'info@catalystgroup.com',
            'company_website' => 'www.catalystgroup.ae',
            'company_trn' => '100520508100003',
            'bank_name' => 'Abu Dhabi Commercial Bank',
            'bank_branch' => 'Sharjah',
            'bank_account' => '0000 0000 0000',
            'bank_iban' => 'AE00 0000 0000 0000 0000 000',
            'vat_pct' => '5',
            'quote_valid_days' => '15',
            'payment_terms' => '100% advance',
            'delivery_terms' => 'To site, within 7 working days of confirmation',
            'warranty_terms' => 'One year on the complete unit and five years on the compressor',
            'availability' => 'Ex-stock subject to prior sale',
            'complaint_sla_hours' => '48',
        ] as $k => $v) {
            Setting::put($k, $v);
        }

        $pwd = 'password';
        $users = collect([
            ['Sanjeev Nair', 'admin', 'Operations manager'],
            ['Abin Lal', 'sales', 'Sales engineer'],
            ['Rashid Al Hammadi', 'sales', 'Sales engineer'],
            ['Priya Menon', 'service', 'Service coordinator'],
            ['Fathima Noor', 'accounts', 'Accounts'],
            ['Aqil Khan', 'technician', 'AC technician'],
            ['Manikandan R.', 'technician', 'AC technician'],
            ['Satwinder Singh', 'technician', 'AC technician'],
            ['Jahangir Akon', 'technician', 'Helper'],
        ])->map(fn ($u, $i) => User::create([
            'name' => $u[0], 'role' => $u[1], 'designation' => $u[2],
            'email' => strtolower(explode(' ', $u[0])[0]).'@catalystgroup.ae',
            'password' => $pwd, 'phone' => '050 '.rand(100, 999).' '.rand(1000, 9999),
        ]));
        $sales = $users->where('role', 'sales')->values();
        $techs = $users->where('role', 'technician')->values();
        $admin = $users->first();

        $products = collect([
            ['O General', 'Ducted split, on/off', 'ARGA36FMTD', '3.0 TR', 7200],
            ['O General', 'Ducted split, on/off', 'ARGA45FHTB', '4.0 TR', 8400],
            ['O General', 'Ducted split, on/off', 'ARGA60FHTB', '5.0 TR', 9600],
            ['Carrier', 'Ducted split, inverter', '42QTD036', '3.0 TR', 8900],
            ['Carrier', 'Ducted split, inverter', '42QTD060', '5.0 TR', 11800],
            ['Midea', 'Wall split, inverter', 'MSAG-18', '1.5 TR', 1850],
            ['Midea', 'Wall split, inverter', 'MSAG-24', '2.0 TR', 2300],
            ['Daikin', 'Cassette', 'FCQ60', '2.0 TR', 5600],
            ['Mitsubishi Electric', 'Package unit', 'PUH-P5', '5.0 TR', 14500],
            ['Super General', 'Wall split, on/off', 'SGS-182', '1.5 TR', 1350],
            ['', 'Copper piping with insulation, per m', '', '', 85, 'Spare'],
            ['', 'Thermostat, digital', '', '', 220, 'Spare'],
            ['', 'Compressor replacement labour', '', '', 650, 'Service'],
            ['', 'Gas top-up R410A', '', '', 350, 'Service'],
            ['', 'Deep cleaning per unit', '', '', 180, 'Service'],
        ])->map(fn ($p, $i) => Product::create([
            'code' => 'P'.str_pad($i + 1, 4, '0', STR_PAD_LEFT), 'brand' => $p[0], 'name' => $p[1], 'model' => $p[2],
            'capacity' => $p[3], 'unit_price' => $p[4], 'category' => $p[5] ?? 'Unit',
        ]));

        $customerRows = [
            ['Al Nahda Tower Management', 'Sharjah', 'Sharjah', 'Mr. Khalid'],
            ['Bin Hadah Real Estate', 'Al Majaz', 'Sharjah', 'Mr. Omar Bin Hadah'],
            ['Golden Steel Industries LLC', 'Industrial Area 10', 'Sharjah', 'Mr. Rajesh'],
            ['Marina Pearl Residences', 'Dubai Marina', 'Dubai', 'Ms. Lara'],
            ['Al Qasimia Villas', 'Al Qasimia', 'Sharjah', 'Mr. Saeed'],
            ['Gulf Fresh Supermarket', 'Al Nuaimiya', 'Ajman', 'Mr. Shafeeq'],
            ['Horizon International School', 'Muwaileh', 'Sharjah', 'Mrs. Anita'],
            ['Pearl Dental Clinic', 'Al Khan', 'Sharjah', 'Dr. Noor'],
            ['Emirates Auto Garage', 'Industrial Area 6', 'Sharjah', 'Mr. Faisal'],
            ['Crystal Plaza Offices', 'Al Majaz 3', 'Sharjah', 'Mr. George'],
            ['Sahara Logistics FZE', 'SAIF Zone', 'Sharjah', 'Mr. Imran'],
            ['Sunrise Family Restaurant', 'Rolla', 'Sharjah', 'Mr. Basheer'],
            ['Al Taawun Residence', 'Al Taawun', 'Sharjah', 'Mr. Yousef'],
            ['Blue Lagoon Spa', 'JLT', 'Dubai', 'Ms. Dana'],
            ['Oasis Mosque Committee', 'Al Rashidiya', 'Ajman', 'Mr. Hamdan'],
            ['Cash customer', '', 'Sharjah', ''],
        ];
        $customers = collect($customerRows)->map(fn ($c, $i) => Customer::create([
            'code' => 'C'.str_pad($i + 1, 4, '0', STR_PAD_LEFT), 'name' => $c[0], 'address' => $c[1] ? 'Near '.$c[1] : null,
            'city' => $c[1] ?: null, 'emirate' => $c[2], 'contact_name' => $c[3] ?: null,
            'phone' => '05'.rand(0, 8).' '.rand(100, 999).' '.rand(1000, 9999),
            'email' => $i < 12 ? 'info@'.preg_replace('/[^a-z]/', '', strtolower(explode(' ', $c[0])[0])).'.ae' : null,
            'trn' => $i < 10 ? '1003'.rand(10000000, 99999999).'03' : null,
            'created_at' => now()->subDays(rand(60, 700)),
        ]));

        // Sales pipeline over the last 6 months
        $units = $products->where('category', 'Unit')->values();
        $labour = $products->where('category', '!=', 'Unit')->values();
        for ($i = 0; $i < 42; $i++) {
            $days = rand(0, 180);
            $date = now()->subDays($days);
            $cust = $customers[rand(0, 14)];
            $sp = $sales[rand(0, 1)];
            $isAmc = rand(0, 5) === 0;
            $enq = Enquiry::create([
                'code' => Enquiry::nextCode('ENQ-'), 'customer_id' => $cust->id, 'user_id' => $sp->id,
                'kind' => $isAmc ? 'amc' : 'sales', 'type' => $isAmc ? 'AMC' : Enquiry::TYPES[rand(0, 2)],
                'source' => Enquiry::SOURCES[rand(0, 5)], 'date' => $date,
                'description' => $isAmc ? 'Annual maintenance for '.rand(4, 24).' units, '.rand(2, 4).' visits a year' : ['Replace old units in '.rand(2, 8).' flats', 'New villa, '.rand(3, 9).' ducted units', 'Showroom cooling not sufficient', 'Office fit-out, supply and install'][rand(0, 3)],
                'client_ref' => rand(0, 2) ? null : 'PO-'.rand(1000, 9999),
                'created_at' => $date, 'updated_at' => $date,
            ]);
            $enq->activities()->create(['user_id' => $sp->id, 'action' => 'created', 'created_at' => $date]);
            if ($days < 4 && rand(0, 1)) {
                $enq->update(['follow_up_on' => now()->addDays(rand(0, 3))]);
                continue;
            }

            $q = Quotation::create([
                'code' => Quotation::nextCode($enq->kind), 'kind' => $enq->kind, 'enquiry_id' => $enq->id,
                'customer_id' => $cust->id, 'user_id' => $sp->id, 'date' => $date->copy()->addDays(rand(0, 2)),
                'valid_days' => 15, 'vat_pct' => 5, 'discount_pct' => [0, 0, 2, 5][rand(0, 3)],
                'subject' => $enq->description, 'payment_terms' => Setting::get('payment_terms'),
                'delivery_terms' => Setting::get('delivery_terms'), 'warranty_terms' => Setting::get('warranty_terms'),
                'availability' => Setting::get('availability'), 'status' => 'draft',
                'created_at' => $date, 'updated_at' => $date,
            ]);
            $n = $isAmc ? 1 : rand(1, 3);
            for ($k = 0; $k < $n; $k++) {
                if ($isAmc) {
                    $qty = rand(4, 24);
                    $q->items()->create(['description' => 'Annual maintenance contract, '.$qty.' units, '.rand(2, 4).' PPM visits + breakdown calls', 'qty' => 1, 'unit_price' => $qty * rand(180, 260), 'total' => 0, 'sort' => $k]);
                } else {
                    $p = $units[rand(0, 9)];
                    $qty = rand(1, 6);
                    $q->items()->create(['product_id' => $p->id, 'brand' => $p->brand, 'description' => $p->name.' '.$p->model, 'capacity' => $p->capacity, 'qty' => $qty, 'unit_price' => $p->unit_price, 'total' => $qty * $p->unit_price, 'sort' => $k]);
                }
            }
            $q->items()->each(fn ($it) => $it->update(['total' => $it->qty * $it->unit_price]));
            $q->recalculate();
            $enq->update(['status' => 'quoted']);

            $roll = rand(1, 10);
            if ($days < 7 || $roll <= 2) {
                $q->update(['status' => $days < 2 ? 'draft' : 'sent', 'sent_at' => $date->copy()->addDay()]);
                continue;
            }
            if ($roll <= 4) {
                $q->update(['status' => 'rejected', 'decided_at' => $date->copy()->addDays(rand(3, 12)), 'lost_reason' => ['Price too high', 'Went with another supplier', 'Project postponed'][rand(0, 2)]]);
                $enq->update(['status' => 'lost']);
                continue;
            }
            $decided = $date->copy()->addDays(rand(2, 10));
            $q->update(['status' => 'approved', 'sent_at' => $date->copy()->addDay(), 'decided_at' => $decided]);
            $enq->update(['status' => 'won']);
            if ($days < 14 && rand(0, 1)) {
                continue;
            }

            if ($isAmc) {
                $start = $decided->copy()->startOfDay();
                $contract = AmcContract::create([
                    'code' => AmcContract::nextCode('AMC-'), 'customer_id' => $cust->id, 'quotation_id' => $q->id,
                    'site_name' => $cust->name.', '.$cust->city, 'equipment' => $q->items->first()->description,
                    'start_date' => $start, 'end_date' => $start->copy()->addYear()->subDay(), 'visits_per_year' => rand(2, 4),
                    'value' => $q->total, 'status' => 'active', 'created_at' => $decided,
                ]);
                $contract->schedulevisits();
                $contract->visits()->where('scheduled_on', '<', now())->get()->each(fn ($v) => $v->update(['status' => rand(0, 7) ? 'done' : 'missed', 'done_on' => $v->scheduled_on->copy()->addDays(rand(0, 3)), 'technician_id' => $techs[rand(0, 2)]->id]));
                $q->update(['status' => 'invoiced']);
                $this->invoice($q, $decided, $admin, ['amc_contract_id' => $contract->id, 'kind' => 'amc']);
                continue;
            }

            $order = Order::create([
                'code' => Order::nextCode('SO-'), 'quotation_id' => $q->id, 'customer_id' => $cust->id, 'user_id' => $sp->id,
                'technician_id' => $techs[rand(0, 2)]->id, 'po_number' => 'LPO-'.rand(1000, 9999), 'po_date' => $decided,
                'start_date' => $decided->copy()->addDays(2), 'due_date' => $decided->copy()->addDays(rand(7, 21)),
                'status' => 'confirmed', 'total' => $q->total, 'created_at' => $decided, 'updated_at' => $decided,
            ]);
            $q->update(['status' => 'ordered']);
            if ($days < 25) {
                $order->update(['status' => rand(0, 1) ? 'in_progress' : 'confirmed']);
                continue;
            }
            $order->update(['status' => 'delivered', 'delivered_on' => $order->due_date->copy()->subDays(rand(0, 5))]);
            $this->invoice($q, $order->delivered_on, $admin, ['order_id' => $order->id]);
            $q->update(['status' => 'invoiced']);
        }

        // A few older contracts expiring soon / expired, for renewal KPIs
        foreach ([[20, 'active'], [45, 'active'], [80, 'active'], [-30, 'expired'], [-200, 'expired']] as [$daysToEnd, $status]) {
            $cust = $customers[rand(0, 14)];
            $end = now()->addDays($daysToEnd)->startOfDay();
            $c = AmcContract::create([
                'code' => AmcContract::nextCode('AMC-'), 'customer_id' => $cust->id, 'site_name' => $cust->name.', '.$cust->city,
                'equipment' => rand(6, 30).' x ducted split', 'start_date' => $end->copy()->subYear()->addDay(), 'end_date' => $end,
                'visits_per_year' => 4, 'value' => rand(6, 30) * 220, 'status' => $status, 'created_at' => $end->copy()->subYear(),
            ]);
            $c->schedulevisits();
            $c->visits()->where('scheduled_on', '<', now())->get()->each(fn ($v) => $v->update(['status' => 'done', 'done_on' => $v->scheduled_on, 'technician_id' => $techs[rand(0, 2)]->id]));
        }

        // Complaints: ~90 over 4 months
        $contracts = AmcContract::all();
        for ($i = 0; $i < 90; $i++) {
            $hours = rand(0, 2) ? rand(1, 120 * 24) : rand(1, 5 * 24);
            $reported = now()->subHours($hours);
            $contract = rand(0, 2) ? $contracts[rand(0, $contracts->count() - 1)] : null;
            $cust = $contract ? $contract->customer : $customers[rand(0, 15)];
            $type = Complaint::TYPES[rand(0, 7)];
            $c = Complaint::create([
                'code' => Complaint::nextCode('CMP-'), 'customer_id' => $cust->id, 'amc_contract_id' => $contract?->id,
                'location' => ['Flat '.rand(101, 1208), 'Villa '.rand(1, 40), 'Ground floor shop', 'Office '.rand(1, 12), 'Pump room'][rand(0, 4)],
                'type' => $type, 'equipment' => Complaint::EQUIPMENT[rand(0, 6)],
                'description' => ['Unit running but no cooling since yesterday', 'Water dripping from the indoor unit', 'Loud noise from outdoor unit at night', 'Unit does not switch on', 'Smell when AC starts', 'Display shows error E4'][rand(0, 5)],
                'priority' => ['low', 'normal', 'normal', 'normal', 'high'][rand(0, 4)],
                'status' => 'open', 'created_by' => $users[3]->id, 'reported_at' => $reported, 'chargeable' => ! $contract,
                'created_at' => $reported, 'updated_at' => $reported,
            ]);
            $c->activities()->create(['user_id' => $users[3]->id, 'action' => 'created', 'created_at' => $reported]);
            if ($hours < 48 && rand(0, 1)) {
                continue;
            }
            $tech = $techs[rand(0, 3)];
            $c->update(['technician_id' => $tech->id, 'status' => 'assigned']);
            $c->activities()->create(['user_id' => $users[3]->id, 'action' => 'assigned', 'note' => $tech->name, 'created_at' => $reported->copy()->addMinutes(rand(10, 240))]);
            if ($hours < 72 && rand(0, 1)) {
                continue;
            }
            $visit = $reported->copy()->addHours(rand(2, 40));
            $c->visits()->create(['technician_id' => $tech->id, 'visited_at' => $visit, 'hours' => [1, 1.5, 2, 3][rand(0, 3)], 'notes' => ['Checked gas pressure, topped up', 'Cleaned drain line and tray', 'Fan motor bearing worn, needs part', 'Replaced thermostat', 'Capacitor replaced'][rand(0, 4)], 'created_at' => $visit]);
            $c->update(['status' => 'visited']);
            $c->activities()->create(['user_id' => $tech->id, 'action' => 'visit', 'note' => 'Site visit logged', 'created_at' => $visit]);
            if ($hours < 120 && rand(0, 1)) {
                $c->update(['status' => rand(0, 1) ? 'in_progress' : 'visited']);
                continue;
            }
            if (rand(0, 2) === 0) {
                $c->materials()->create(['description' => ['Capacitor 45uF', 'Thermostat, digital', 'R410A gas, kg', 'Drain pump', 'Fan motor'][rand(0, 4)], 'qty' => rand(1, 2), 'unit_cost' => rand(60, 480)]);
            }
            $closed = $visit->copy()->addHours(rand(1, 72));
            $c->update(['status' => 'closed', 'closed_at' => $closed, 'closing_note' => 'Resolved, customer confirmed cooling OK', 'updated_at' => $closed]);
            $c->activities()->create(['user_id' => $tech->id, 'action' => 'status', 'note' => 'Closed', 'created_at' => $closed]);
        }
    }

    private function invoice(Quotation $q, Carbon $date, User $by, array $extra = []): void
    {
        $inv = Invoice::create(array_merge([
            'code' => Invoice::nextCode('INV-'), 'customer_id' => $q->customer_id, 'quotation_id' => $q->id, 'user_id' => $by->id,
            'date' => $date, 'due_date' => $date->copy()->addDays(30), 'subtotal' => $q->subtotal, 'discount_amt' => $q->discount_amt,
            'vat_pct' => $q->vat_pct, 'vat_amt' => $q->vat_amt, 'total' => $q->total, 'lpo_number' => 'LPO-'.rand(1000, 9999),
            'created_at' => $date, 'updated_at' => $date,
        ], $extra));
        foreach ($q->items as $it) {
            $inv->items()->create(['description' => trim(($it->brand ? $it->brand.' ' : '').$it->description.($it->capacity ? ', '.$it->capacity : '')), 'qty' => $it->qty, 'unit_price' => $it->unit_price, 'total' => $it->total]);
        }
        $roll = rand(1, 10);
        if ($roll <= 5) {
            $inv->payments()->create(['date' => $date->copy()->addDays(rand(5, 40)), 'amount' => $inv->total, 'user_id' => $by->id, 'reference' => 'TRF'.rand(10000, 99999)]);
        } elseif ($roll <= 7) {
            $inv->payments()->create(['date' => $date->copy()->addDays(rand(5, 20)), 'amount' => round($inv->total / 2, 2), 'user_id' => $by->id, 'reference' => 'CHQ'.rand(1000, 9999), 'method' => 'Cheque']);
        }
        $inv->refreshPaidStatus();
    }
}
