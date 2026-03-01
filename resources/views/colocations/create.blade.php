<x-app-layout>
    <x-slot name="header">
        <h2 class="text-2xl font-semibold tracking-tight text-slate-900">
            Create Colocation
        </h2>
    </x-slot>

    <div class="py-6">
        <div class="mx-auto max-w-xl px-4 sm:px-6 lg:px-8">

            <div class="rounded-xl border border-slate-200 bg-white p-6 shadow-sm">

                {{-- Errors --}}
                @if ($errors->any())
                    <div class="mb-4 rounded-lg border border-rose-200 bg-rose-50 px-4 py-3 text-sm text-rose-700">
                        <ul class="list-disc pl-5 space-y-1">
                            @foreach ($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                @endif

                <form method="POST" action="{{ route('colocations.store') }}" class="space-y-5">
                    @csrf

                    <div>
                        <label class="block text-sm font-medium text-slate-700 mb-1">
                            Colocation Name
                        </label>
                        <input type="text"
                               name="name"
                               value="{{ old('name') }}"
                               placeholder="e.g. Downtown Apartment"
                               class="block w-full rounded-lg border border-slate-300 bg-white px-3 py-2 text-sm
                                      focus:border-emerald-500 focus:ring-1 focus:ring-emerald-500"
                               required>
                    </div>

                    <div class="pt-1">
                        <button
                            class="w-full rounded-lg bg-emerald-600 px-4 py-2.5 text-sm font-semibold text-white
                                   hover:bg-emerald-700 focus:outline-none focus:ring-2 focus:ring-emerald-500 focus:ring-offset-1 transition">
                            Create Colocation
                        </button>
                    </div>
                </form>

            </div>

        </div>
    </div>
</x-app-layout>