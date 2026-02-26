<x-app-layout>
    <x-slot name="header">
        <h2 class="text-xl font-semibold">
            Create Colocation
        </h2>
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

        <form method="POST" action="{{ route('colocations.store') }}">
            @csrf

            <div class="mb-4">
                <label>Name</label>
                <input type="text"
                       name="name"
                       value="{{ old('name') }}"
                       class="border p-2 w-full"
                       required>
            </div>

            <button class="bg-black text-white px-4 py-2 rounded">
                Create
            </button>
        </form>
    </div>
</x-app-layout>