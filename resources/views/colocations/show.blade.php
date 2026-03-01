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
                    <path d="M10 .5a9.5 9.5 0 1 0 9.5 9.5A9.51 9.51 0 0 0 10 .5Zm3.707 8.207-4 4a1 1 0 0 1-1.414 0l-2-2a1 1 0 0 1 1.414-1.414L9 10.586l3.293-3.293a1 1 0 0 1 1.414 1.414Z" />
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
                            <ul class="list-disc ml-5 mt-3">
                                @foreach ($members as $member)
                                @php
                                $bal = isset($summaries[$member->id]) ? (float)$summaries[$member->id]['balance'] : 0;
                                @endphp

                                <li class="flex items-center justify-between py-1">
                                    <div>
                                        {{ $member->name }}
                                        <span class="text-gray-600">({{ $member->pivot->role }})</span>

                                        {{-- Reputation --}}
                                        <span class="ml-2 text-sm">
                                            Rep: <strong>{{ $member->reputation_score }}</strong>
                                        </span>

                                        {{-- Balance --}}
                                        <span class="ml-2 text-sm">
                                            Balance:
                                            @if ($bal > 0)
                                            <span class="text-green-600 font-semibold">+{{ number_format($bal, 2) }}</span>
                                            @elseif ($bal < 0)
                                                <span class="text-red-600 font-semibold">{{ number_format($bal, 2) }}</span>
                                        @else
                                        <span class="text-gray-700">0.00</span>
                                        @endif
                                        </span>
                                    </div>
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
                    {{-- Categories --}}
                    <div class="mb-6 p-4 bg-white rounded shadow">
                        <div class="flex items-center justify-between">
                            <h3 class="font-semibold">Categories</h3>
                        </div>

                        {{-- Add category (owner only) --}}
                        @if ($colocation->owner_id === auth()->id() && $colocation->status === 'active')
                        <form method="POST" action="{{ route('categories.store', $colocation) }}" class="mt-4">
                            @csrf

                            <div class="flex flex-col gap-2 sm:flex-row">
                                <div class="flex-1">
                                    <label class="sr-only">Category name</label>
                                    <input
                                        name="name"
                                        class="w-full rounded-xl border border-slate-200 bg-white px-3 py-2.5 text-sm shadow-sm
                           placeholder:text-slate-400 focus:border-emerald-500 focus:ring-emerald-500"
                                        placeholder="Category name"
                                        required>
                                </div>

                                <button
                                    class="inline-flex items-center justify-center rounded-xl bg-emerald-600 px-4 py-2.5 text-sm font-semibold text-white
                       shadow-sm hover:bg-emerald-700 focus:outline-none focus:ring-2 focus:ring-emerald-500 focus:ring-offset-2">
                                    Add
                                </button>
                            </div>
                        </form>
                        @endif

                        {{-- List --}}
                        <ul class="mt-5 space-y-3">
                            @forelse ($categories as $cat)
                            <li class="flex items-center justify-between gap-3 rounded-2xl border border-slate-200 bg-white p-4 shadow-sm hover:bg-slate-50/60 transition">
                                <div class="min-w-0">
                                    <div class="flex items-center gap-2">
                                        <span class="truncate text-sm font-semibold text-slate-900">{{ $cat->name }}</span>

                                        @if ($cat->color)
                                        <span class="inline-flex items-center rounded-full bg-slate-100 px-2.5 py-1 text-xs font-medium text-slate-700 ring-1 ring-slate-200">
                                            {{ $cat->color }}
                                        </span>
                                        @endif
                                    </div>
                                </div>

                                @if ($colocation->owner_id === auth()->id() && $colocation->status === 'active')
                                <form method="POST" action="{{ route('categories.destroy', [$colocation, $cat]) }}"
                                    onsubmit="return confirm('Delete this category?')">
                                    @csrf
                                    @method('DELETE')
                                    <button
                                        class="inline-flex items-center justify-center rounded-xl px-3 py-2 text-sm font-semibold
                               text-rose-700 hover:bg-rose-50 focus:outline-none focus:ring-2 focus:ring-rose-400 focus:ring-offset-2">
                                        Delete
                                    </button>
                                </form>
                                @endif
                            </li>
                            @empty
                            <li class="rounded-2xl border border-dashed border-slate-200 bg-slate-50 p-6 text-center">
                                <p class="text-sm font-semibold text-slate-900">No categories yet</p>
                                <p class="mt-1 text-sm text-slate-500">Create your first category to organize expenses.</p>
                            </li>
                            @endforelse
                        </ul>
                        </div>

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
                                                        <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 20 20">
                                                            <path fill-rule="evenodd" d="M9 2a1 1 0 00-.894.553L7.382 4H4a1 1 0 000 2v10a2 2 0 002 2h8a2 2 0 002-2V6a1 1 0 100-2h-3.382l-.724-1.447A1 1 0 0011 2H9zM7 8a1 1 0 012 0v6a1 1 0 11-2 0V8zm5-1a1 1 0 00-1 1v6a1 1 0 102 0V8a1 1 0 00-1-1z" clip-rule="evenodd" />
                                                        </svg>
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
                        <div class="bg-white shadow-sm sm:rounded-2xl overflow-hidden border border-slate-200">
                            <div class="p-6 border-b border-slate-100 flex items-start justify-between gap-4">
                                <div class="flex items-center gap-3">
                                    <div class="h-10 w-10 rounded-xl bg-slate-900 text-white flex items-center justify-center shadow-sm">
                                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-6 9l2 2 4-4" />
                                        </svg>
                                    </div>

                                    <div>
                                        <h3 class="text-lg font-semibold text-slate-900">Settlements</h3>
                                        <p class="text-sm text-slate-500">Settle up what’s owed between roommates.</p>
                                    </div>
                                </div>
                            </div>

                            <div class="p-6">
                                @if ($settlements->isEmpty())
                                <div class="rounded-2xl border border-slate-200 bg-slate-50 p-6 text-center">
                                    <div class="mx-auto mb-3 flex h-12 w-12 items-center justify-center rounded-2xl bg-emerald-100 text-emerald-700">
                                        <svg class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                d="M5 13l4 4L19 7" />
                                        </svg>
                                    </div>
                                    <p class="text-sm font-semibold text-slate-900">All settled!</p>
                                    <p class="mt-1 text-sm text-slate-500">Everything is balanced. No pending debts.</p>
                                </div>
                                @else
                                <ul class="space-y-3">
                                    @foreach ($settlements as $st)
                                    <li class="flex flex-col gap-4 rounded-2xl border border-slate-200 bg-white p-5 sm:flex-row sm:items-center sm:justify-between hover:bg-slate-50/60 transition">
                                        <div class="min-w-0">
                                            <div class="flex flex-wrap items-center gap-2 text-sm">
                                                <span class="font-semibold text-slate-900">{{ $st->fromUser->name }}</span>
                                                <span class="text-slate-500">owes</span>
                                                <span class="font-semibold text-slate-900">{{ $st->toUser->name }}</span>
                                            </div>

                                            <div class="mt-2 inline-flex items-center rounded-xl bg-slate-900 px-3 py-1.5 text-white">
                                                <span class="text-xs font-medium opacity-80">$</span>
                                                <span class="ml-1 text-xl font-extrabold tracking-tight">
                                                    {{ number_format($st->amount, 2) }}
                                                </span>
                                            </div>
                                        </div>

                                        <div class="shrink-0">
                                            @if ($st->is_paid)
                                            <span class="inline-flex items-center gap-2 rounded-full bg-emerald-100 px-3 py-1.5 text-xs font-semibold text-emerald-800 ring-1 ring-emerald-200">
                                                <span class="inline-flex h-5 w-5 items-center justify-center rounded-full bg-emerald-600 text-white text-[10px]">✓</span>
                                                PAID
                                            </span>
                                            @elseif ($colocation->status === 'active')
                                            <form method="POST" action="{{ route('settlements.paid', $st) }}">
                                                @csrf @method('PATCH')
                                                <button
                                                    class="w-full sm:w-auto inline-flex items-center justify-center rounded-xl bg-emerald-600 px-4 py-2.5 text-sm font-semibold text-white shadow-sm hover:bg-emerald-700 focus:outline-none focus:ring-2 focus:ring-emerald-500 focus:ring-offset-2">
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