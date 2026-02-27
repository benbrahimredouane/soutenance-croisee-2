<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Colocation;
use App\Models\Membership;
use Illuminate\Support\Facades\DB;
use App\Services\SettlementService;


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

        $expensesQuery = $colocation->expenses()->with(['category', 'payer'])->orderBy('date', 'desc');

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
        $calculation = $settlementService->calculate($colocation, $month);

        $settlementService->generateAndStore($colocation, $month);
        $storedSettlements = $settlementService->getStoredSettlements($colocation, $month);

        return view('colocations.show', compact('colocation', 'members', 'categories', 'expenses', 'month', 'calculation', 'storedSettlements'));
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
}
