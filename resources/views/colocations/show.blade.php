<x-app-layout>
    <x-slot name="header">
        <h2 class="text-xl font-semibold">
            Dashboard
        </h2>
    </x-slot>
    {{-- Members --}}
    <div class="mb-6 p-4 bg-white rounded shadow">
        <div class="flex items-center justify-between">
            <h3 class="font-semibold">Members</h3>

            {{-- Invite button (owner only) --}}
            @if ($colocation->owner_id === auth()->id() && $colocation->status === 'active')
            <x-primary-button class="ms-4">
                <a href="{{ route('invitations.create', $colocation) }}"
                    class="bg-black text-white px-4 py-2 rounded">
                    Invite Member
                </a>
            </x-primary-button>

            @endif
        </div>

        <ul class="list-disc ml-5 mt-3">
            @foreach ($members as $member)
            <li>
                {{ $member->name }}
                <span class="text-gray-600">({{ $member->pivot->role }})</span>
            </li>
            @endforeach
        </ul>
    </div>
    {{-- Success message --}}
    @if (session('success'))
    <div class="p-3 mb-4 bg-green-100 text-green-800 rounded">
        {{ session('success') }}
    </div>
    @endif

    {{-- Month filter --}}
    <div class="mb-6 p-4 bg-white rounded shadow">
        <form method="GET" action="{{ route('colocations.show', $colocation) }}" class="flex gap-3 items-end">
            <div>
                <label class="block text-sm mb-1">Filter by month</label>
                <select name="month" class="border p-2 rounded">
                    <option value="all" {{ $month === 'all' ? 'selected' : '' }}>All</option>
                    @php
                    // show last 12 months in dropdown
                    $now = \Carbon\Carbon::now()->startOfMonth();
                    @endphp
                    @for ($i = 0; $i < 12; $i++)
                        @php $m=$now->copy()->subMonths($i)->format('Y-m'); @endphp
                        <option value="{{ $m }}" {{ $month === $m ? 'selected' : '' }}>{{ $m }}</option>
                        @endfor
                </select>
            </div>

            <x-primary-button class="bg-green-600 text-white px-3 py-1 rounded">
                Apply
            </x-primary-button>
        </form>
    </div>

    {{-- Add expense --}}
    @if ($colocation->status === 'active')
    <div class="mb-6 p-4 bg-white rounded shadow">
        <h3 class="font-semibold mb-4">Add Expense</h3>

        @if ($errors->any())
        <div class="mb-4 text-red-600">
            <ul class="list-disc ml-5">
                @foreach ($errors->all() as $error)
                <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
        @endif

        <form method="POST" action="{{ route('expenses.store', $colocation) }}" class="space-y-4">
            @csrf

            <div>
                <label class="block text-sm mb-1">Title</label>
                <input name="title" class="border p-2 w-full rounded" required>
            </div>

            <div>
                <label class="block text-sm mb-1">Amount</label>
                <input name="amount" type="number" step="0.01" min="0.01" class="border p-2 w-full rounded" required>
            </div>

            <div>
                <label class="block text-sm mb-1">Date</label>
                <input name="date" type="date" class="border p-2 w-full rounded" required>
            </div>

            <div>
                <label class="block text-sm mb-1">Category</label>
                <select name="category_id" class="border p-2 w-full rounded">
                    <option value="">No category</option>
                    @foreach ($categories as $category)
                    <option value="{{ $category->id }}">{{ $category->name }}</option>
                    @endforeach
                </select>
            </div>

            <div>
                <label class="block text-sm mb-1">Paid by</label>
                <select name="payer_id" class="border p-2 w-full rounded" required>
                    @foreach ($members as $member)
                    <option value="{{ $member->id }}">{{ $member->name }}</option>
                    @endforeach
                </select>
            </div>

            <x-primary-button class="bg-black text-white px-4 py-2 rounded">
                Add
            </x-primary-button>
        </form>
    </div>
    @endif

    {{-- Expenses list --}}
    <div class="p-4 bg-white rounded shadow">
        <h3 class="font-semibold mb-4">Expenses</h3>

        @if ($expenses->isEmpty())
        <p>No expenses found.</p>
        @else
        <table class="w-full text-left">
            <thead>
                <tr class="border-b">
                    <th class="py-2">Date</th>
                    <th class="py-2">Title</th>
                    <th class="py-2">Category</th>
                    <th class="py-2">Paid by</th>
                    <th class="py-2">Amount</th>
                    <th class="py-2"></th>
                </tr>
            </thead>
            <tbody>
                @foreach ($expenses as $expense)
                <tr class="border-b">
                    <td class="py-2">{{ $expense->date->format('Y-m-d') }}</td>
                    <td class="py-2">{{ $expense->title }}</td>
                    <td class="py-2">{{ $expense->category?->name ?? '-' }}</td>
                    <td class="py-2">{{ $expense->payer->name }}</td>
                    <td class="py-2">{{ number_format($expense->amount, 2) }}</td>
                    <td class="py-2">
                        <form method="POST" action="{{ route('expenses.destroy', $expense) }}">
                            @csrf
                            @method('DELETE')
                            <button class="text-red-600" onclick="return confirm('Delete this expense?')">
                                Delete
                            </button>
                        </form>
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
        @endif
    </div>
    {{-- Settlements --}}
    <div class="p-4 bg-white rounded shadow mt-6">
        <h3 class="font-semibold mb-2">Settlements</h3>

        @if ($settlements->isEmpty())
        <p>No settlements</p>
        @else
        <ul class="space-y-2">
            @foreach ($settlements as $st)
            <li class="flex items-center justify-between border-b py-2">
                <div>
                    <strong>{{ $st->fromUser->name }}</strong>
                    owes
                    <strong>{{ $st->toUser->name }}</strong>
                    <span class="font-semibold">{{ number_format($st->amount, 2) }}</span>

                    @if ($st->is_paid)
                    <span class="ml-2 text-green-600 font-semibold">(paid)</span>
                    @endif
                </div>

                @if (! $st->is_paid && $colocation->status === 'active')
                <form method="POST" action="{{ route('settlements.paid', $st) }}">
                    @csrf
                    @method('PATCH')
                    <x-primary-button class="bg-green-600 text-white px-3 py-1 rounded">
                   
                            Mark paid
                        
                    </x-primary-button>
                </form>
                @endif
            </li>
            @endforeach
        </ul>
        @endif
    </div>
</x-app-layout>