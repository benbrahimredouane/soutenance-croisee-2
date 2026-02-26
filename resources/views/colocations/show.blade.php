<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ $colocation->name }}
        </h2>
    </x-slot>

    <div class="py-6 max-w-3xl mx-auto space-y-4">
        <div class="p-4 bg-white rounded shadow">
            <p><strong>Status:</strong> {{ $colocation->status }}</p>
            <p><strong>Owner ID:</strong> {{ $colocation->owner_id }}</p>
        </div>

        <div class="p-4 bg-white rounded shadow">
            <h3 class="font-semibold mb-2">Members</h3>
            <ul class="list-disc ml-5">
                @foreach ($members as $member)
                <li>{{ $member->name }} ({{ $member->pivot->role }})</li>
                @endforeach
            </ul>
        </div>
        @if ($colocation->owner_id === auth()->id() && $colocation->status === 'active')

        <form method="POST"
            action="{{ route('colocations.cancel', $colocation) }}"
            onsubmit="return confirm('Are you sure you want to cancel this colocation?')"
            class="mt-4">
            @csrf
            @method('PATCH')

            <button class="bg-red-600 text-white px-4 py-2 rounded">
                Cancel Colocation
            </button>
        </form>

        @endif
    </div>
</x-app-layout>