<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <h2 class="text-2xl font-bold text-gray-800 leading-tight">
                {{ __('Dashboard') }}
            </h2>
            <div class="flex items-center gap-2">
                <span class="px-3 py-1 text-xs font-bold uppercase rounded-full {{ $colocation->status === 'active' ? 'bg-green-100 text-green-700' : 'bg-red-100 text-red-700' }}">
                    {{ $colocation->status }}
                </span>
            </div>
        </div>
    </x-slot>

    <div class="py-12 bg-gray-50">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-8">
            
            {{-- Success Notification --}}
            @if (session('success'))
                <div class="flex items-center p-4 mb-4 text-green-800 border-t-4 border-green-300 bg-green-50 rounded-lg shadow-sm" role="alert">
                    <svg class="flex-shrink-0 w-4 h-4" aria-hidden="true" xmlns="http://www.w3.org/2000/svg" fill="currentColor" viewBox="0 0 20 20">
                        <path d="M10 .5a9.5 9.5 0 1 0 9.5 9.5A9.51 9.51 0 0 0 10 .5Zm3.707 8.207-4 4a1 1 0 0 1-1.414 0l-2-2a1 1 0 0 1 1.414-1.414L9 10.586l3.293-3.293a1 1 0 0 1 1.414 1.414Z"/>
                    </svg>
                    <div class="ms-3 text-sm font-medium">{{ session('success') }}</div>
                </div>
            @endif

            <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
                
                {{-- LEFT COLUMN: Members & Filters --}}
                <div class="space-y-6">
                    {{-- Members Card --}}
                    <div class="bg-white overflow-hidden shadow-sm sm:rounded-xl border border-gray-100">
                        <div class="p-6">
                            <div class="flex items-center justify-between mb-4">
                                <h3 class="text-lg font-bold text-gray-900">Roommates</h3>
                                @if ($colocation->owner_id === auth()->id() && $colocation->status === 'active')
                                    <a href="{{ route('invitations.create', $colocation) }}" class="text-indigo-600 hover:text-indigo-800 text-sm font-semibold flex items-center gap-1">
                                        <span>+ Invite</span>
                                    </a>
                                @endif
                            </div>
                            <ul class="divide-y divide-gray-100">
                                @foreach ($members as $member)
                                    <li class="py-3 flex items-center justify-between">
                                        <div class="flex items-center gap-3">
                                            <div class="h-8 w-8 rounded-full bg-indigo-100 flex items-center justify-center text-indigo-700 font-bold text-xs">
                                                {{ substr($member->name, 0, 1) }}
                                            </div>
                                            <div>
                                                <p class="text-sm font-medium text-gray-900">{{ $member->name }}</p>
                                                <p class="text-xs text-gray-500 capitalize">{{ $member->pivot->role }}</p>
                                            </div>
                                        </div>
                                        @if ($colocation->owner_id === auth()->id() && $member->id !== auth()->id() && $colocation->status === 'active')
                                            <form method="POST" action="{{ route('colocations.members.remove', [$colocation, $member]) }}" onsubmit="return confirm('Remove this member?')">
                                                @csrf @method('PATCH')
                                                <button class="text-gray-400 hover:text-red-600 transition-colors">
                                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" /></svg>
                                                </button>
                                            </form>
                                        @endif
                                    </li>
                                @endforeach
                            </ul>
                            
                            @if ($colocation->status === 'active')
                                @php $currentMember = $members->firstWhere('id', auth()->id()); @endphp
                                @if ($currentMember && $currentMember->pivot->role !== 'owner')
                                    <form method="POST" action="{{ route('colocations.leave', $colocation) }}" class="mt-4 pt-4 border-t">
                                        @csrf @method('PATCH')
                                        <button class="w-full text-center text-xs text-red-500 hover:underline">Leave Colocation</button>
                                    </form>
                                @endif
                            @endif
                        </div>
                    </div>

                    {{-- Month Filter --}}
                    <div class="bg-white p-6 shadow-sm sm:rounded-xl border border-gray-100">
                        <label class="block text-sm font-bold text-gray-700 mb-3 uppercase tracking-wider">History Filter</label>
                        <form method="GET" action="{{ route('colocations.show', $colocation) }}" class="flex flex-col gap-2">
                            <select name="month" class="rounded-lg border-gray-200 text-sm focus:ring-indigo-500 focus:border-indigo-500">
                                <option value="all" {{ $month === 'all' ? 'selected' : '' }}>All Time</option>
                                @php $now = \Carbon\Carbon::now()->startOfMonth(); @endphp
                                @for ($i = 0; $i < 12; $i++)
                                    @php $m=$now->copy()->subMonths($i)->format('Y-m'); @endphp
                                    <option value="{{ $m }}" {{ $month === $m ? 'selected' : '' }}>{{ $now->copy()->subMonths($i)->format('F Y') }}</option>
                                @endfor
                            </select>
                            <button class="w-full bg-gray-800 text-white py-2 rounded-lg text-sm font-semibold hover:bg-black transition">Apply Filter</button>
                        </form>
                    </div>
                </div>

                {{-- RIGHT COLUMN: Expenses & Settlements --}}
                <div class="lg:col-span-2 space-y-6">
                    
                    {{-- Add Expense Card --}}
                    @if ($colocation->status === 'active')
                    <div class="bg-white shadow-sm sm:rounded-xl border border-gray-100 overflow-hidden">
                        <div class="p-6 border-b border-gray-50 bg-gray-50/50">
                            <h3 class="text-lg font-bold text-gray-900">Quick Add Expense</h3>
                        </div>
                        <div class="p-6">
                            <form method="POST" action="{{ route('expenses.store', $colocation) }}" class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                @csrf
                                <div class="md:col-span-2">
                                    <label class="block text-xs font-bold text-gray-500 uppercase">Title</label>
                                    <input name="title" placeholder="Grocery, Electricity..." class="mt-1 block w-full border-gray-200 rounded-lg focus:ring-indigo-500" required>
                                </div>
                                <div>
                                    <label class="block text-xs font-bold text-gray-500 uppercase">Amount ($)</label>
                                    <input name="amount" type="number" step="0.01" class="mt-1 block w-full border-gray-200 rounded-lg" required>
                                </div>
                                <div>
                                    <label class="block text-xs font-bold text-gray-500 uppercase">Date</label>
                                    <input name="date" type="date" value="{{ date('Y-m-d') }}" class="mt-1 block w-full border-gray-200 rounded-lg" required>
                                </div>
                                <div>
                                    <label class="block text-xs font-bold text-gray-500 uppercase">Category</label>
                                    <select name="category_id" class="mt-1 block w-full border-gray-200 rounded-lg">
                                        <option value="">General</option>
                                        @foreach ($categories as $category)
                                            <option value="{{ $category->id }}">{{ $category->name }}</option>
                                        @endforeach
                                    </select>
                                </div>
                                <div>
                                    <label class="block text-xs font-bold text-gray-500 uppercase">Paid By</label>
                                    <select name="payer_id" class="mt-1 block w-full border-gray-200 rounded-lg">
                                        @foreach ($members as $member)
                                            <option value="{{ $member->id }}" {{ auth()->id() == $member->id ? 'selected' : '' }}>{{ $member->name }}</option>
                                        @endforeach
                                    </select>
                                </div>
                                <div class="md:col-span-2 pt-2">
                                    <button class="w-full bg-indigo-600 text-white py-3 rounded-lg font-bold hover:bg-indigo-700 shadow-md transition-all transform hover:-translate-y-0.5">
                                        Add Expense
                                    </button>
                                </div>
                            </form>
                        </div>
                    </div>
                    @endif

                    {{-- Expenses Table Card --}}
                    <div class="bg-white shadow-sm sm:rounded-xl border border-gray-100 overflow-hidden">
                        <div class="p-6 border-b border-gray-50 flex justify-between items-center">
                            <h3 class="text-lg font-bold text-gray-900">Recent Transactions</h3>
                        </div>
                        <div class="overflow-x-auto">
                            <table class="w-full text-left">
                                <thead class="bg-gray-50">
                                    <tr>
                                        <th class="px-6 py-3 text-xs font-bold text-gray-500 uppercase">Date</th>
                                        <th class="px-6 py-3 text-xs font-bold text-gray-500 uppercase">Description</th>
                                        <th class="px-6 py-3 text-xs font-bold text-gray-500 uppercase text-right">Amount</th>
                                        <th class="px-6 py-3"></th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-gray-100">
                                    @forelse ($expenses as $expense)
                                    <tr class="hover:bg-gray-50/50 transition">
                                        <td class="px-6 py-4 text-sm text-gray-500">{{ $expense->date->format('M d, Y') }}</td>
                                        <td class="px-6 py-4">
                                            <p class="text-sm font-semibold text-gray-900">{{ $expense->title }}</p>
                                            <p class="text-xs text-gray-400">{{ $expense->payer->name }} • {{ $expense->category?->name ?? 'General' }}</p>
                                        </td>
                                        <td class="px-6 py-4 text-sm font-bold text-gray-900 text-right">
                                            ${{ number_format($expense->amount, 2) }}
                                        </td>
                                        <td class="px-6 py-4 text-right">
                                            <form method="POST" action="{{ route('expenses.destroy', $expense) }}">
                                                @csrf @method('DELETE')
                                                <button class="text-gray-300 hover:text-red-500" onclick="return confirm('Delete?')">
                                                    <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M9 2a1 1 0 00-.894.553L7.382 4H4a1 1 0 000 2v10a2 2 0 002 2h8a2 2 0 002-2V6a1 1 0 100-2h-3.382l-.724-1.447A1 1 0 0011 2H9zM7 8a1 1 0 012 0v6a1 1 0 11-2 0V8zm5-1a1 1 0 00-1 1v6a1 1 0 102 0V8a1 1 0 00-1-1z" clip-rule="evenodd" /></svg>
                                                </button>
                                            </form>
                                        </td>
                                    </tr>
                                    @empty
                                    <tr>
                                        <td colspan="4" class="px-6 py-10 text-center text-gray-400 italic">No expenses recorded for this period.</td>
                                    </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                    </div>

                    {{-- Settlements Card --}}
                    <div class="bg-indigo-900 text-white shadow-xl sm:rounded-xl overflow-hidden">
                        <div class="p-6 border-b border-indigo-800 flex items-center gap-2">
                            <svg class="w-5 h-5 text-indigo-300" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-6 9l2 2 4-4" /></svg>
                            <h3 class="text-lg font-bold">Settlements</h3>
                        </div>
                        <div class="p-6">
                            @if ($settlements->isEmpty())
                                <p class="text-indigo-300 italic text-sm text-center">Everything is balanced! No pending debts.</p>
                            @else
                                <ul class="space-y-4">
                                    @foreach ($settlements as $st)
                                    <li class="flex flex-col sm:flex-row sm:items-center justify-between p-4 rounded-lg bg-indigo-800/50 border border-indigo-700">
                                        <div class="mb-3 sm:mb-0">
                                            <span class="font-bold text-indigo-200">{{ $st->fromUser->name }}</span>
                                            <span class="text-indigo-400 mx-2 text-sm">owes</span>
                                            <span class="font-bold text-white">{{ $st->toUser->name }}</span>
                                            <div class="text-2xl font-black mt-1 text-white">${{ number_format($st->amount, 2) }}</div>
                                        </div>
                                        <div>
                                            @if ($st->is_paid)
                                                <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-bold bg-green-500 text-white">
                                                    ✓ PAID
                                                </span>
                                            @elseif ($colocation->status === 'active')
                                                <form method="POST" action="{{ route('settlements.paid', $st) }}">
                                                    @csrf @method('PATCH')
                                                    <button class="w-full sm:w-auto px-4 py-2 bg-white text-indigo-900 rounded-lg text-sm font-bold hover:bg-indigo-100 transition shadow-sm">
                                                        Mark as Paid
                                                    </button>
                                                </form>
                                            @endif
                                        </div>
                                    </li>
                                    @endforeach
                                </ul>
                            @endif
                        </div>
                    </div>

                </div>
            </div>
        </div>
    </div>
</x-app-layout>