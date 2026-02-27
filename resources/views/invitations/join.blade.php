<x-app-layout>
    <x-slot name="header">
        <h2 class="text-xl font-semibold">Join a Colocation</h2>
    </x-slot>

    <div class="p-6">
        @if ($errors->any())
            <div class="mb-4 text-red-600">
                <ul>
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <form method="POST" action="{{ route('invitations.join') }}">
            @csrf

            <div class="mb-4">
                <label>Invitation Token</label>
                <input type="text" name="token" class="border p-2 w-full" required>
            </div>

            <button class="bg-black text-white px-4 py-2 rounded">Join</button>
        </form>
    </div>
</x-app-layout>