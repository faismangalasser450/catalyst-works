@php($edit = $enquiry->exists)
<x-layouts.app :title="$edit ? 'Edit '.$enquiry->code : 'New enquiry'">
<form method="post" action="{{ $edit ? route('enquiries.update', $enquiry) : route('enquiries.store') }}" class="mx-auto max-w-3xl">
    @csrf @if($edit) @method('put') @endif
    <x-page :title="$edit ? 'Edit '.$enquiry->code : 'New enquiry'" :sub="$edit ? null : 'Who asked, what they want. You can quote straight after saving.'" :back="$edit ? route('enquiries.show', $enquiry) : route('enquiries.index')">
        <x-slot:actions>
            <button class="btn btn-primary">{{ $edit ? 'Save changes' : 'Save' }}</button>
            @unless($edit)<button name="then" value="quote" class="btn">Save & quote now</button>@endunless
        </x-slot:actions>
    </x-page>
    <div class="card card-b grid gap-4 sm:grid-cols-2">
        <x-field label="Customer" name="customer_id" required class="sm:col-span-2">
            <select name="customer_id" class="input" required autofocus><option value="">Choose customer…</option>@foreach($customers as $cu)<option value="{{ $cu->id }}" @selected(old('customer_id', $enquiry->customer_id) == $cu->id)>{{ $cu->name }}{{ $cu->city ? ' — '.$cu->city : '' }}</option>@endforeach</select>
            <p class="mt-1 text-xs text-slate-500">Not in the list? <a href="{{ route('customers.create', ['then' => 'enquiry']) }}" class="text-brand-700">Add customer</a>.</p>
        </x-field>
        <x-field label="What do they need" name="type" required class="sm:col-span-2">
            <div class="flex flex-wrap gap-1.5">@foreach(\App\Models\Enquiry::TYPES as $t)<label class="!mb-0 cursor-pointer"><input type="radio" name="type" value="{{ $t }}" class="peer sr-only" @checked(old('type', $enquiry->type) === $t) required><span class="chip">{{ $t }}</span></label>@endforeach</div>
        </x-field>
        <x-field label="Details" name="description" required class="sm:col-span-2"><textarea name="description" rows="3" class="input" required placeholder="e.g. 6 ducted units for a new villa in Al Qasimia, site visit requested">{{ old('description', $enquiry->description) }}</textarea></x-field>
        <x-field label="Came in via" name="source"><div class="flex flex-wrap gap-1.5">@foreach(\App\Models\Enquiry::SOURCES as $t)<label class="!mb-0 cursor-pointer"><input type="radio" name="source" value="{{ $t }}" class="peer sr-only" @checked(old('source', $enquiry->source) === $t)><span class="chip">{{ $t }}</span></label>@endforeach</div></x-field>
        <x-field label="Date" name="date" required><input type="date" name="date" class="input" value="{{ old('date', $enquiry->date?->format('Y-m-d')) }}" required></x-field>
        <x-field label="Handled by" name="user_id" required><select name="user_id" class="input">@foreach($owners as $o)<option value="{{ $o->id }}" @selected(old('user_id', $enquiry->user_id) == $o->id)>{{ $o->name }}</option>@endforeach</select></x-field>
        <x-field label="Customer reference" name="client_ref" hint="Their RFQ / PO number, if any"><input name="client_ref" class="input" value="{{ old('client_ref', $enquiry->client_ref) }}"></x-field>
        <x-field label="Follow up on" name="follow_up_on"><input type="date" name="follow_up_on" class="input" value="{{ old('follow_up_on', $enquiry->follow_up_on?->format('Y-m-d')) }}"></x-field>
    </div>
</form>
</x-layouts.app>
