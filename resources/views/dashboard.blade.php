<x-app-layout>
    <x-slot name="header">
        <h2 class="text-xl font-semibold">
            Dashboard
        </h2>
    </x-slot>

    <div class="p-6">

        @if (!$activeMembership)

            <div class="p-4 bg-yellow-100 rounded mb-4">
                <h3 class="font-bold mb-2">You are not in any colocation yet.</h3>
                <p class="mb-4">Create one or join using an invitation.</p>

                <a href="{{ route('colocations.create') }}"
                   class="bg-black text-white px-4 py-2 rounded mr-2">
                    Create Colocation
                </a>

            </div>

        @else

            <div class="p-4 bg-green-100 rounded">
                <h3 class="font-bold mb-2">
                    Your Active Colocation:
                </h3>

                <p class="mb-4">
                    {{ $activeMembership->colocation->name }}
                </p>

                <a href="{{ route('colocations.show', $activeMembership->colocation) }}"
                   class="bg-black text-white px-4 py-2 rounded">
                    Open Colocation
                </a>
            </div>

        @endif

    </div>
</x-app-layout>