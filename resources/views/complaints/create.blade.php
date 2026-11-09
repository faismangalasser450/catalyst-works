@php($edit = $complaint->exists)
<x-layouts.app :title="$edit ? 'Edit '.$complaint->code : 'New complaint'">
<form method="post" action="{{ $edit ? route('complaints.update', $complaint) : route('complaints.store') }}" class="mx-auto max-w-3xl">
    @csrf @if($edit) @method('put') @endif
    <x-page :title="$edit ? 'Edit '.$complaint->code : 'Register a complaint'" :sub="$edit ? null : 'Seven fields. Visit and material details come later, from the complaint page.'" :back="$edit ? route('complaints.show', $complaint) : route('complaints.index')">
        <x-slot:actions>
            <button class="btn btn-primary">{{ $edit ? 'Save changes' : 'Register' }}</button>
            @unless($edit)<button name="then" value="another" class="btn">Register & add another</button>@endunless
        </x-slot:actions>
    </x-page>

    <div class="card card-b grid gap-4 sm:grid-cols-2">
        <x-field label="Customer" name="customer_id" required class="sm:col-span-2">
            <select name="customer_id" class="input" required autofocus>
                <option value="">Choose customer…</option>
                @foreach($customers as $cu)<option value="{{ $cu->id }}" @selected(old('customer_id', $complaint->customer_id) == $cu->id)>{{ $cu->name }}{{ $cu->city ? ' — '.$cu->city : '' }}</option>@endforeach
            </select>
            <p class="mt-1 text-xs text-slate-500">Not in the list? <a href="{{ route('customers.create', ['then' => 'complaint']) }}" class="text-brand-700">Add customer</a>. AMC cover is detected automatically.</p>
        </x-field>
        <x-field label="Location" name="location" required hint="Flat / villa / shop / floor"><input name="location" class="input" value="{{ old('location', $complaint->location) }}" placeholder="e.g. Flat 604" required></x-field>
        <x-field label="Contact on site" name="contact_phone"><div class="flex gap-2"><input name="contact_name" class="input" value="{{ old('contact_name', $complaint->contact_name) }}" placeholder="Name"><input name="contact_phone" class="input" value="{{ old('contact_phone', $complaint->contact_phone) }}" placeholder="Phone"></div></x-field>

        <x-field label="Problem" name="type" required class="sm:col-span-2">
            <div class="flex flex-wrap gap-1.5">
                @foreach(\App\Models\Complaint::TYPES as $t)
                    <label class="!mb-0 cursor-pointer"><input type="radio" name="type" value="{{ $t }}" class="peer sr-only" @checked(old('type', $complaint->type) === $t) required><span class="chip ">{{ $t }}</span></label>
                @endforeach
            </div>
        </x-field>
        <x-field label="Equipment" name="equipment" class="sm:col-span-2">
            <div class="flex flex-wrap gap-1.5">
                @foreach(\App\Models\Complaint::EQUIPMENT as $t)
                    <label class="!mb-0 cursor-pointer"><input type="radio" name="equipment" value="{{ $t }}" class="peer sr-only" @checked(old('equipment', $complaint->equipment) === $t)><span class="chip ">{{ $t }}</span></label>
                @endforeach
            </div>
        </x-field>
        <x-field label="What the customer said" name="description" required class="sm:col-span-2"><textarea name="description" rows="2" class="input" required placeholder="e.g. Unit in the master bedroom not cooling since last night">{{ old('description', $complaint->description) }}</textarea></x-field>

        <x-field label="Priority" name="priority" required>
            <div class="flex gap-1.5">@foreach(\App\Models\Complaint::PRIORITIES as $k => $l)<label class="!mb-0 cursor-pointer"><input type="radio" name="priority" value="{{ $k }}" class="peer sr-only" @checked(old('priority', $complaint->priority) === $k)><span class="chip ">{{ $l }}</span></label>@endforeach</div>
        </x-field>
        <x-field label="Technician" name="technician_id" hint="Optional now, can be assigned from the list later">
            <select name="technician_id" class="input"><option value="">Assign later</option>@foreach($technicians as $t)<option value="{{ $t->id }}" @selected(old('technician_id', $complaint->technician_id) == $t->id)>{{ $t->name }}</option>@endforeach</select>
        </x-field>
    </div>

    <div class="sticky bottom-0 mt-4 flex gap-2 border-t border-slate-200 bg-slate-100/90 py-3 backdrop-blur sm:hidden">
        <button class="btn btn-primary flex-1 justify-center btn-lg">{{ $edit ? 'Save changes' : 'Register' }}</button>
    </div>
</form>
</x-layouts.app>
