<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->string('role')->default('sales'); // admin, sales, service, accounts, technician
            $table->string('phone')->nullable();
            $table->string('designation')->nullable();
            $table->boolean('active')->default(true);
        });

        Schema::create('settings', function (Blueprint $table) {
            $table->string('key')->primary();
            $table->text('value')->nullable();
        });

        Schema::create('customers', function (Blueprint $table) {
            $table->id();
            $table->string('code')->unique();
            $table->string('name');
            $table->string('phone')->nullable();
            $table->string('email')->nullable();
            $table->string('trn')->nullable();
            $table->string('address')->nullable();
            $table->string('city')->nullable();
            $table->string('emirate')->nullable();
            $table->string('contact_name')->nullable();
            $table->string('contact_phone')->nullable();
            $table->text('notes')->nullable();
            $table->timestamps();
        });

        Schema::create('products', function (Blueprint $table) {
            $table->id();
            $table->string('code')->unique();
            $table->string('brand')->nullable();
            $table->string('name');
            $table->string('model')->nullable();
            $table->string('capacity')->nullable(); // e.g. 3.0 TR
            $table->string('category')->default('Unit'); // Unit, Spare, Service
            $table->decimal('unit_price', 12, 2)->default(0);
            $table->boolean('active')->default(true);
            $table->timestamps();
        });

        Schema::create('enquiries', function (Blueprint $table) {
            $table->id();
            $table->string('code')->unique();
            $table->foreignId('customer_id')->constrained();
            $table->foreignId('user_id')->constrained(); // sales person
            $table->string('kind')->default('sales'); // sales, amc
            $table->string('type')->nullable(); // Supply & install, Supply only, Service, AMC
            $table->string('source')->nullable(); // Phone, WhatsApp, Email, Walk-in, Referral
            $table->string('client_ref')->nullable();
            $table->text('description')->nullable();
            $table->string('status')->default('open'); // open, quoted, won, lost
            $table->date('date');
            $table->date('follow_up_on')->nullable();
            $table->timestamps();
        });

        Schema::create('quotations', function (Blueprint $table) {
            $table->id();
            $table->string('code');
            $table->unsignedSmallInteger('revision')->default(0);
            $table->string('kind')->default('sales'); // sales, amc
            $table->foreignId('enquiry_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('customer_id')->constrained();
            $table->foreignId('user_id')->constrained();
            $table->date('date');
            $table->unsignedSmallInteger('valid_days')->default(15);
            $table->string('status')->default('draft'); // draft, sent, approved, rejected, ordered, invoiced
            $table->string('subject')->nullable();
            $table->decimal('subtotal', 12, 2)->default(0);
            $table->decimal('discount_pct', 5, 2)->default(0);
            $table->decimal('discount_amt', 12, 2)->default(0);
            $table->decimal('vat_pct', 5, 2)->default(5);
            $table->decimal('vat_amt', 12, 2)->default(0);
            $table->decimal('total', 12, 2)->default(0);
            $table->string('payment_terms')->nullable();
            $table->string('delivery_terms')->nullable();
            $table->string('warranty_terms')->nullable();
            $table->string('availability')->nullable();
            $table->text('notes')->nullable();
            $table->timestamp('sent_at')->nullable();
            $table->timestamp('decided_at')->nullable();
            $table->string('lost_reason')->nullable();
            $table->timestamps();
            $table->unique(['code', 'revision']);
        });

        Schema::create('quotation_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('quotation_id')->constrained()->cascadeOnDelete();
            $table->foreignId('product_id')->nullable()->constrained()->nullOnDelete();
            $table->string('brand')->nullable();
            $table->string('description');
            $table->string('capacity')->nullable();
            $table->decimal('qty', 10, 2)->default(1);
            $table->decimal('unit_price', 12, 2)->default(0);
            $table->decimal('total', 12, 2)->default(0);
            $table->unsignedSmallInteger('sort')->default(0);
        });

        Schema::create('orders', function (Blueprint $table) {
            $table->id();
            $table->string('code')->unique();
            $table->foreignId('quotation_id')->constrained();
            $table->foreignId('customer_id')->constrained();
            $table->foreignId('user_id')->constrained();
            $table->foreignId('technician_id')->nullable()->constrained('users')->nullOnDelete();
            $table->string('po_number')->nullable();
            $table->date('po_date')->nullable();
            $table->date('start_date')->nullable();
            $table->date('due_date')->nullable();
            $table->date('delivered_on')->nullable();
            $table->string('status')->default('confirmed'); // confirmed, in_progress, delivered, closed
            $table->decimal('total', 12, 2)->default(0);
            $table->text('notes')->nullable();
            $table->timestamps();
        });

        Schema::create('invoices', function (Blueprint $table) {
            $table->id();
            $table->string('code')->unique();
            $table->string('kind')->default('sales'); // sales, amc
            $table->foreignId('customer_id')->constrained();
            $table->foreignId('quotation_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('order_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('amc_contract_id')->nullable();
            $table->foreignId('user_id')->constrained();
            $table->date('date');
            $table->date('due_date')->nullable();
            $table->string('status')->default('unpaid'); // unpaid, part_paid, paid, cancelled
            $table->decimal('subtotal', 12, 2)->default(0);
            $table->decimal('discount_amt', 12, 2)->default(0);
            $table->decimal('vat_pct', 5, 2)->default(5);
            $table->decimal('vat_amt', 12, 2)->default(0);
            $table->decimal('total', 12, 2)->default(0);
            $table->decimal('paid_amount', 12, 2)->default(0);
            $table->string('lpo_number')->nullable();
            $table->text('notes')->nullable();
            $table->timestamps();
        });

        Schema::create('invoice_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('invoice_id')->constrained()->cascadeOnDelete();
            $table->string('description');
            $table->decimal('qty', 10, 2)->default(1);
            $table->decimal('unit_price', 12, 2)->default(0);
            $table->decimal('total', 12, 2)->default(0);
        });

        Schema::create('payments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('invoice_id')->constrained()->cascadeOnDelete();
            $table->date('date');
            $table->decimal('amount', 12, 2);
            $table->string('method')->default('Bank transfer');
            $table->string('reference')->nullable();
            $table->foreignId('user_id')->constrained();
            $table->timestamps();
        });

        Schema::create('amc_contracts', function (Blueprint $table) {
            $table->id();
            $table->string('code')->unique();
            $table->foreignId('customer_id')->constrained();
            $table->foreignId('quotation_id')->nullable()->constrained()->nullOnDelete();
            $table->string('site_name')->nullable();
            $table->string('equipment')->nullable(); // e.g. 12 x Ducted split
            $table->date('start_date');
            $table->date('end_date');
            $table->unsignedSmallInteger('visits_per_year')->default(4);
            $table->decimal('value', 12, 2)->default(0);
            $table->string('status')->default('active'); // active, expired, renewed, cancelled
            $table->foreignId('renewed_to_id')->nullable();
            $table->text('notes')->nullable();
            $table->timestamps();
        });

        Schema::create('ppm_visits', function (Blueprint $table) {
            $table->id();
            $table->foreignId('amc_contract_id')->constrained()->cascadeOnDelete();
            $table->foreignId('technician_id')->nullable()->constrained('users')->nullOnDelete();
            $table->date('scheduled_on');
            $table->date('done_on')->nullable();
            $table->string('status')->default('due'); // due, done, missed
            $table->text('notes')->nullable();
            $table->timestamps();
        });

        Schema::create('complaints', function (Blueprint $table) {
            $table->id();
            $table->string('code')->unique();
            $table->foreignId('customer_id')->constrained();
            $table->foreignId('amc_contract_id')->nullable()->constrained()->nullOnDelete();
            $table->string('location')->nullable(); // flat / villa / site
            $table->string('type'); // Not cooling, Water leak, Noise, Not starting, Bad smell, Remote/thermostat, Other
            $table->string('equipment')->nullable(); // Split, Ducted split, Cassette, Package unit, Chiller, FCU
            $table->text('description');
            $table->string('priority')->default('normal'); // low, normal, high
            $table->string('status')->default('open'); // open, assigned, visited, in_progress, closed
            $table->foreignId('technician_id')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('created_by')->constrained('users');
            $table->timestamp('reported_at');
            $table->timestamp('closed_at')->nullable();
            $table->string('closing_note')->nullable();
            $table->boolean('chargeable')->default(false);
            $table->timestamps();
        });

        Schema::create('complaint_visits', function (Blueprint $table) {
            $table->id();
            $table->foreignId('complaint_id')->constrained()->cascadeOnDelete();
            $table->foreignId('technician_id')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('visited_at');
            $table->decimal('hours', 5, 1)->default(1);
            $table->decimal('expense', 10, 2)->default(0);
            $table->text('notes')->nullable();
            $table->timestamps();
        });

        Schema::create('complaint_materials', function (Blueprint $table) {
            $table->id();
            $table->foreignId('complaint_id')->constrained()->cascadeOnDelete();
            $table->string('description');
            $table->decimal('qty', 10, 2)->default(1);
            $table->decimal('unit_cost', 12, 2)->default(0);
            $table->timestamps();
        });

        Schema::create('activities', function (Blueprint $table) {
            $table->id();
            $table->morphs('subject');
            $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete();
            $table->string('action'); // created, status, note, assigned, visit, sent ...
            $table->text('note')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        foreach (['activities', 'complaint_materials', 'complaint_visits', 'complaints', 'ppm_visits', 'amc_contracts', 'payments', 'invoice_items', 'invoices', 'orders', 'quotation_items', 'quotations', 'enquiries', 'products', 'customers', 'settings'] as $t) {
            Schema::dropIfExists($t);
        }
    }
};
