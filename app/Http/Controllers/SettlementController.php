<?php

namespace App\Http\Controllers;

use App\Models\Settlement;
use Illuminate\Http\Request;

class SettlementController extends Controller
{
    public function markPaid(Request $request, Settlement $settlement)
    {
        $colocation = $settlement->colocation;

        
        $isMember = $colocation->members()
            ->where('users.id', $request->user()->id)
            ->wherePivotNull('left_at')
            ->exists();

        if (! $isMember) {
            abort(403);
        }

        if (! $settlement->is_paid) {
            $settlement->update([
                'is_paid' => true,
                'paid_at' => now(),
            ]);
        }

        return back()->with('success', 'Marked as paid.');
    }
}