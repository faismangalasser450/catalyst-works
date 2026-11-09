@php($edit = $customer->exists)
<x-layouts.app :title="$edit ? 'Edit customer' : 'New customer'">
<form method="post" action="{{ $edit ? route('customers.update', $customer) : route('customers.store') }}" class="mx-auto max-w-2xl">
    @csrf @if($edit) @method('put') @endif
    <input type="hidden" name="then" value="{{ request('then') }}">
    <x-page :title="$edit ? $customer->name : 'New customer'" :sub="$edit ? 'Edit details' : 'Only the name is required.'" :back="$edit ? route('customers.show', $customer) : route('customers.index')">
        <x-slot:actions><button class="btn btn-primary">{{ $edit ? 'Save changes' : (request('then') ? 'Save & continue' : 'Save') }}</button></x-slot:actions>
    </x-page>
    <div class="card card-b grid gap-4 sm:grid-cols-2">
        <x-field label="Company / customer name" name="name" required class="sm:col-span-2"><input name="name" class="input" value="{{ old('name', $customer->name) }}" required autofocus></x-field>
        <x-field label="Phone" name="phone"><input name="phone" class="input" value="{{ old('phone', $customer->phone) }}"></x-field>
        <x-field label="Email" name="email"><input name="email" type="email" class="input" value="{{ old('email', $customer->email) }}"></x-field>
        <x-field label="Contact person" name="contact_name"><input name="contact_name" class="input" value="{{ old('contact_name', $customer->contact_name) }}"></x-field>
        <x-field label="Contact phone" name="contact_phone"><input name="contact_phone" class="input" value="{{ old('contact_phone', $customer->contact_phone) }}"></x-field>
        <x-field label="Address" name="address" class="sm:col-span-2"><input name="address" class="input" value="{{ old('address', $customer->address) }}" placeholder="Building, street"></x-field>
        <x-field label="Area / city" name="city"><input name="city" class="input" value="{{ old('city', $customer->city) }}"></x-field>
        <x-field label="Emirate" name="emirate"><select name="emirate" class="input">@foreach(['Sharjah','Dubai','Ajman','Abu Dhabi','Umm Al Quwain','Ras Al Khaimah','Fujairah'] as $e)<option @selected(old('emirate', $customer->emirate) === $e)>{{ $e }}</option>@endforeach</select></x-field>
        <x-field label="TRN" name="trn" hint="Shown on quotations and tax invoices"><input name="trn" class="input" value="{{ old('trn', $customer->trn) }}"></x-field>
        <x-field label="Notes" name="notes"><input name="notes" class="input" value="{{ old('notes', $customer->notes) }}" placeholder="Gate pass needed, call before visit…"></x-field>
    </div>
</form>
</x-layouts.app>
