{{-- Members --}}
<div class="mb-6 p-4 bg-white rounded shadow">
    <div class="flex items-center justify-between">
        <h3 class="font-semibold">Members</h3>

        {{-- Invite button (owner only) --}}
        @if ($colocation->owner_id === auth()->id() && $colocation->status === 'active')
            <a href="{{ route('invitations.create', $colocation) }}"
               class="bg-black text-white px-4 py-2 rounded">
                Invite Member
            </a>
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
                    @php $m = $now->copy()->subMonths($i)->format('Y-m'); @endphp
                    <option value="{{ $m }}" {{ $month === $m ? 'selected' : '' }}>{{ $m }}</option>
                @endfor
            </select>
        </div>

        <button class="bg-black text-white px-4 py-2 rounded">Apply</button>
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

        <button class="bg-black text-white px-4 py-2 rounded">Add</button>
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

{{-- Balances + Settlements --}}
<div class="mt-6 grid grid-cols-1 md:grid-cols-2 gap-6">

    {{-- Balances --}}
    <div class="p-4 bg-white rounded shadow">
        <h3 class="font-semibold mb-2">Balances</h3>

        <p class="text-sm text-gray-600 mb-4">
            Total expenses: <strong>{{ number_format($calculation['total'], 2) }}</strong>
            — Share per member: <strong>{{ number_format($calculation['share'], 2) }}</strong>
        </p>

        @if ($calculation['balances']->isEmpty())
            <p>No members or expenses to calculate.</p>
        @else
            <table class="w-full text-left">
                <thead>
                    <tr class="border-b">
                        <th class="py-2">Member</th>
                        <th class="py-2">Paid</th>
                        <th class="py-2">Balance</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($calculation['balances'] as $row)
                        <tr class="border-b">
                            <td class="py-2">{{ $row['user']->name }}</td>
                            <td class="py-2">{{ number_format($row['paid'], 2) }}</td>
                            <td class="py-2">
                                @if ($row['balance'] > 0)
                                    <span class="text-green-600 font-semibold">
                                        +{{ number_format($row['balance'], 2) }}
                                    </span>
                                @elseif ($row['balance'] < 0)
                                    <span class="text-red-600 font-semibold">
                                        {{ number_format($row['balance'], 2) }}
                                    </span>
                                @else
                                    <span class="text-gray-700">0.00</span>
                                @endif
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>

            {{-- Debug line: sum of balances should be ~ 0 --}}
            @php
                $sumBalances = $calculation['balances']->sum('balance');
            @endphp
            <p class="mt-3 text-xs text-gray-500">
                Debug: sum of balances = {{ number_format($sumBalances, 2) }}
                (should be close to 0.00)
            </p>
        @endif
    </div>

    {{-- Who owes who --}}
    <div class="p-4 bg-white rounded shadow">
        <h3 class="font-semibold mb-2">Who owes who</h3>

        @if ($calculation['settlements']->isEmpty())
            <p>No debts 🎉</p>
        @else
            <ul class="list-disc ml-5">
                @foreach ($calculation['settlements'] as $s)
                    <li class="py-1">
                        <strong>{{ $s['from']->name }}</strong>
                        owes
                        <strong>{{ $s['to']->name }}</strong>
                        <span class="font-semibold">
                            {{ number_format($s['amount'], 2) }}
                        </span>
                    </li>
                @endforeach
            </ul>
        @endif
    </div>

</div>