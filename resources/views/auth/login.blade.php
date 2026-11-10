<x-layouts.guest title="Sign in">
    <div class="w-full max-w-sm">
        <div class="mb-6 text-center">
            <div class="mx-auto mb-3 flex h-12 w-12 items-center justify-center rounded-lg bg-brand-700 text-xl font-bold text-white">C</div>
            <h1>Catalyst Works</h1>
            <p class="text-[13px] text-slate-500">Sales and service, in one place.</p>
        </div>
        <form method="post" action="{{ route('login') }}" class="card card-b space-y-4">
            @csrf
            <div>
                <label for="email">Email</label>
                <input id="email" name="email" type="email" class="input" value="{{ old('email') }}" autofocus required>
                @error('email') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
            </div>
            <div>
                <label for="password">Password</label>
                <input id="password" name="password" type="password" class="input" required>
            </div>
            <label class="flex items-center gap-2 !mb-0 font-normal"><input type="checkbox" name="remember" class="rounded border-slate-300"> Keep me signed in on this device</label>
            <button class="btn btn-primary w-full justify-center btn-lg">Sign in</button>
        </form>
        <p class="mt-4 text-center text-xs text-slate-500">Forgot your password? Ask the admin to reset it from Settings → Users.</p>
    </div>
</x-layouts.guest>
