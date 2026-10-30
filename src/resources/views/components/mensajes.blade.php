@if (session('status'))
    <div class="mx-auto max-w-7xl px-4 pt-6 sm:px-6 lg:px-8">
        <div class="rounded-md border border-green-300 bg-green-50 px-4 py-3 text-sm text-green-800">
            {{ session('status') }}
        </div>
    </div>
@endif

@if ($errors->has('regla'))
    <div class="mx-auto max-w-7xl px-4 pt-6 sm:px-6 lg:px-8">
        <div class="rounded-md border border-red-300 bg-red-50 px-4 py-3 text-sm text-red-800">
            {{ $errors->first('regla') }}
        </div>
    </div>
@endif