<x-app-layout>
    <x-slot name="header">
        <h2 class="text-xl font-semibold">Admin Dashboard</h2>
    </x-slot>

    <div class="p-6 space-y-6">
        @if (session('success'))
        <div class="p-3 bg-green-100 text-green-800 rounded">{{ session('success') }}</div>
        @endif
        @if ($errors->any())
        <div class="p-3 bg-red-100 text-red-800 rounded">
            @foreach ($errors->all() as $e) <div>{{ $e }}</div> @endforeach
        </div>
        @endif

        <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">

           
            <div class="rounded-xl border border-slate-200 bg-white p-5 shadow-sm hover:shadow-md transition">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-sm font-medium text-slate-500">Total Users</p>
                        <p class="mt-1 text-3xl font-semibold tracking-tight text-slate-900">
                            {{ $usersCount }}
                        </p>
                    </div>

                  
                </div>
            </div>

            <div class="rounded-xl border border-slate-200 bg-white p-5 shadow-sm hover:shadow-md transition">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-sm font-medium text-slate-500">Colocations</p>
                        <p class="mt-1 text-3xl font-semibold tracking-tight text-slate-900">
                            {{ $colocationsCount }}
                        </p>
                    </div>

                    
                </div>
            </div>

         
            <div class="rounded-xl border border-slate-200 bg-white p-5 shadow-sm hover:shadow-md transition">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-sm font-medium text-slate-500">Total Expenses</p>
                        <p class="mt-1 text-3xl font-semibold tracking-tight text-slate-900">
                            {{ $expensesCount }}
                        </p>
                    </div>

                  
                </div>
            </div>

        </div>

        <div class="p-4 bg-white rounded shadow">
            <h3 class="font-semibold mb-3">Users</h3>
            @if (session('success'))
            <div class="p-3 bg-green-100 text-green-800 rounded">{{ session('success') }}</div>
            @endif

            @if ($errors->any())
            <div class="p-3 bg-red-100 text-red-800 rounded">
                @foreach ($errors->all() as $e) <div>{{ $e }}</div> @endforeach
            </div>
            @endif

            <table class="w-full text-left">
                <thead>
                    <tr class="border-b">
                        <th class="py-2">Name</th>
                        <th class="py-2">Email</th>
                        <th class="py-2">Banned</th>
                        <th class="py-2"></th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($users as $u)
                    <tr class="border-b">
                        <td class="py-2">{{ $u->name }}</td>
                        <td class="py-2">{{ $u->email }}</td>
                        <td class="py-2">{{ $u->is_banned ? 'Yes' : 'No' }}</td>
                        <td class="py-2">
                            <form method="POST" action="{{ route('admin.users.toggleBan', $u) }}">
                                @csrf
                                @method('PATCH')
                                <button type="submit" class="px-3 py-1 rounded {{ $u->is_banned ? 'bg-blue-600 text-white' : 'bg-red-600 text-white' }}">
                                    {{ $u->is_banned ? 'Unban' : 'Ban' }}
                                </button>
                            </form>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>

            <div class="mt-4">
                {{ $users->links() }}
            </div>
        </div>
    </div>
</x-app-layout>