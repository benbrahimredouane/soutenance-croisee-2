<x-app-layout>
    <x-slot name="header">
        <h2 class="text-2xl font-semibold tracking-tight text-slate-900">
            Join a Colocation
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

                <form method="POST" action="{{ route('invitations.join') }}" class="space-y-5">
                    @csrf

                    <div>
                        <label class="block text-sm font-medium text-slate-700 mb-1">
                            Invitation Token
                        </label>

                        <input
                            type="text"
                            name="token"
                            placeholder="Enter your invitation token"
                            class="block w-full rounded-lg border border-slate-300 bg-white px-3 py-2 text-sm
                                   focus:border-emerald-500 focus:ring-1 focus:ring-emerald-500"
                            required
                        >

                        <p class="mt-2 text-xs text-slate-500">
                            Paste the token you received from your roommate.
                        </p>
                    </div>

                    <div class="pt-1">
                        <button
                            class="w-full rounded-lg bg-emerald-600 px-4 py-2.5 text-sm font-semibold text-white
                                   hover:bg-emerald-700 focus:outline-none focus:ring-2 focus:ring-emerald-500 focus:ring-offset-1 transition">
                            Join Colocation
                        </button>
                    </div>

                </form>

            </div>

        </div>
    </div>
</x-app-layout>