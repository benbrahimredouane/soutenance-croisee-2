<?php

namespace App\Http\Controllers;

use App\Models\Colocation;
use App\Models\Expense;
use Illuminate\Http\Request;

class ExpenseController extends Controller
{
    public function store(Request $request, Colocation $colocation)
    {
        // Only active members can add expenses
        $isMember = $colocation->members()
            ->where('users.id', $request->user()->id)
            ->wherePivotNull('left_at')
            ->exists();

        if (! $isMember || $colocation->status !== 'active') {
            abort(403);
        }

        $data = $request->validate([
            'title' => ['required', 'string', 'max:255'],
            'amount' => ['required', 'numeric', 'min:0.01'],
            'date' => ['required', 'date'],
            'category_id' => ['nullable', 'exists:categories,id'],
            'payer_id' => ['required', 'exists:users,id'],
        ]);

        // Extra safety: payer must be an active member of this colocation
        $payerIsMember = $colocation->members()
            ->where('users.id', $data['payer_id'])
            ->wherePivotNull('left_at')
            ->exists();

        if (! $payerIsMember) {
            return back()->withErrors(['payer_id' => 'Selected payer is not a member of this colocation.']);
        }

        $colocation->expenses()->create($data);
        app(\App\Services\SettlementService::class)->refreshPendingSettlements($colocation, request('month', 'all'));

        return back()->with('success', 'Expense added.');
    }

    public function destroy(Request $request, Expense $expense)
    {
        $colocation = $expense->colocation;

        // Only active members can delete 
        $isMember = $colocation->members()
            ->where('users.id', $request->user()->id)
            ->wherePivotNull('left_at')
            ->exists();

        if (! $isMember) {
            abort(403);
        }

        $expense->delete();
        app(\App\Services\SettlementService::class)
            ->refreshPendingSettlements($colocation, request('month', 'all'));

        return back()->with('success', 'Expense deleted.');
    }
}
