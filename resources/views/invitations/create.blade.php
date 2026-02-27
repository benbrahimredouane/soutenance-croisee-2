<x-app-layout>
    <x-slot name="header">
        <h2 class="text-xl font-semibold">Invite to {{ $colocation->name }}</h2>
    </x-slot>

    <div class="p-6">
        @if (session('success'))
            <div class="mb-4 text-green-600">{{ session('success') }}</div>
        @endif

        @if ($errors->any())
            <div class="mb-4 text-red-600">
                <ul>
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <form method="POST" action="{{ route('invitations.store', $colocation) }}">
            @csrf

            <div class="mb-4">
                <label>Email</label>
                <input type="email" name="email" class="border p-2 w-full" required>
            </div>

            <button class="bg-black text-white px-4 py-2 rounded">Send Invitation</button>
        </form>
    </div>
</x-app-layout>