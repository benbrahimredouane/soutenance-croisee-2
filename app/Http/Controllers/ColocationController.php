<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Colocation;
use App\Models\Membership;
use Illuminate\Support\Facades\DB;
use App\Services\SettlementService;
use App\Models\Settlement;


class ColocationController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        //
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        //
        return view('colocations.create');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        //
        $user = $request->user();

        // RULE: user can only have 1 active colocation
        $hasActiveMembership = $user->memberships()
            ->whereNull('left_at')
            ->whereHas('colocation', function ($q) {
                $q->where('status', 'active');
            })
            ->exists();

        if ($hasActiveMembership) {
            return back()->withErrors([
                'colocation' => 'You already have an active colocation.',
            ]);
        }

        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
        ]);

        // Use transaction so both colocation + membership succeed together
        $colocation = DB::transaction(function () use ($user, $data) {
            $colocation = Colocation::create([
                'name' => $data['name'],
                'owner_id' => $user->id,
                'status' => 'active',
            ]);

            Membership::create([
                'user_id' => $user->id,
                'colocation_id' => $colocation->id,
                'role' => 'owner',
                'left_at' => null,
            ]);

            return $colocation;
        });

        return redirect()->route('colocations.show', $colocation);
    }


    /**
     * Display the specified resource.
     */
public function show(Colocation $colocation, SettlementService $settlementService)
{
    $isMember = $colocation->members()
        ->where('users.id', auth()->id())
        ->wherePivotNull('left_at')
        ->exists();

    if (! $isMember) {
        abort(403);
    }

    $month = request('month', 'all');

   
    if ($month !== 'all' && !preg_match('/^\d{4}-\d{2}$/', $month)) {
        $month = 'all';
    }

    $expensesQuery = $colocation->expenses()
        ->with(['category', 'payer'])
        ->orderBy('date', 'desc');

    if ($month !== 'all') {
        $start = \Carbon\Carbon::createFromFormat('Y-m', $month)->startOfMonth();
        $end = (clone $start)->endOfMonth();
        $expensesQuery->whereBetween('date', [$start->toDateString(), $end->toDateString()]);
    }

    $expenses = $expensesQuery->get();

    $members = $colocation->members()
        ->wherePivotNull('left_at')
        ->get();

    $categories = $colocation->categories()->orderBy('name')->get();

    $summaries = $settlementService->getMemberSummaries($colocation, $month);

    $settlements = $colocation->settlements()
        ->where('month', $month)
        ->with(['fromUser', 'toUser'])
        ->orderBy('is_paid')
        ->orderByDesc('amount')
        ->get();

    return view('colocations.show', compact(
        'colocation',
        'members',
        'categories',
        'expenses',
        'month',
        'summaries',
        'settlements'
    ));
}

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        //
    }
    public function cancel(Colocation $colocation)
    {
        $user = auth()->user();

        // Check if current user is owner
        $membership = $colocation->memberships()
            ->where('user_id', $user->id)
            ->whereNull('left_at')
            ->first();

        if (! $membership || $membership->role !== 'owner') {
            abort(403);
        }

        $colocation->update([
            'status' => 'cancelled',
            'cancelled_at' => now(),
        ]);

        return redirect()->route('dashboard')
            ->with('success', 'Colocation cancelled successfully.');
    }
    public function leave(Colocation $colocation, SettlementService $settlementService)
{
    $user = auth()->user();

    $membership = $colocation->members()
        ->where('users.id', $user->id)
        ->wherePivotNull('left_at')
        ->first();

    if (! $membership) {
        abort(403);
    }

   
    if ($membership->pivot->role === 'owner') {
        return back()->withErrors(['error' => 'Owner cannot leave the colocation.']);
    }

    $summaries = $settlementService->getMemberSummaries($colocation, 'all');

    $balance = (float) $summaries[$user->id]['balance'];

    
    if ($balance < 0) {
        $user->decrement('reputation_score');
    } else {
        $user->increment('reputation_score');
    }

    
    $colocation->members()
        ->updateExistingPivot($user->id, [
            'left_at' => now(),
        ]);

    
    $settlementService->refreshPendingSettlements($colocation, 'all');

    return redirect()->route('dashboard')
        ->with('success', 'You have left the colocation.');
}
public function removeMember(Colocation $colocation, User $user, SettlementService $settlementService)
{
    $ownerId = auth()->id();

    if ($colocation->owner_id !== $ownerId) {
        abort(403);
    }

    // cannot remove owner
    if ($user->id === $ownerId) {
        return back()->withErrors(['error' => 'Owner cannot remove themselves.']);
    }

    // must be active member
    $membership = $colocation->members()
        ->where('users.id', $user->id)
        ->wherePivotNull('left_at')
        ->first();

    if (! $membership) {
        return back()->withErrors(['error' => 'User is not an active member.']);
    }

    $month = request('month', 'all');

    // compute balances for debt check (ALL months is usually what you want for reputation)
    $summariesAll = $settlementService->getMemberSummaries($colocation, 'all');
    $balanceAll = isset($summariesAll[$user->id]) ? (float)$summariesAll[$user->id]['balance'] : 0.0;

    DB::transaction(function () use ($colocation, $user, $ownerId, $balanceAll, $month) {

        // Reputation of removed member
        if ($balanceAll < 0) {
            $user->decrement('reputation_score');
        } else {
            $user->increment('reputation_score');
        }

        $pending = Settlement::query()
            ->where('colocation_id', $colocation->id)
            ->where('month', $month)
            ->where('is_paid', false)
            ->where('from_user_id', $user->id)
            ->get();

        foreach ($pending as $st) {
            // Owner pays creditor (record as paid to reduce owner's balance)
            Settlement::create([
                'colocation_id' => $colocation->id,
                'from_user_id' => $ownerId,
                'to_user_id' => $st->to_user_id,
                'amount' => $st->amount,
                'is_paid' => true,
                'paid_at' => now(),
                'month' => $st->month,
            ]);

            // Mark member's debt as paid (clears their debt)
            $st->update([
                'is_paid' => true,
                'paid_at' => now(),
            ]);
        }

        
        $colocation->members()
            ->updateExistingPivot($user->id, ['left_at' => now()]);
    });

    // Refresh pending after changes 
    $settlementService->refreshPendingSettlements($colocation, $month);

    return back()->with('success', 'Member removed.');
}
}
