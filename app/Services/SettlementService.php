<?php

namespace App\Services;

use App\Models\Colocation;
use Illuminate\Support\Collection;

class SettlementService
{
    public function calculate(Colocation $colocation, ?string $month = null): array
    {
        $members = $colocation->members()
            ->wherePivotNull('left_at')
            ->get();

        $expensesQuery = $colocation->expenses();

        if ($month && $month !== 'all') {
            $start = \Carbon\Carbon::createFromFormat('Y-m', $month)->startOfMonth();
            $end = (clone $start)->endOfMonth();

            $expensesQuery->whereBetween('date', [
                $start->toDateString(),
                $end->toDateString(),
            ]);
        }

        $expenses = $expensesQuery->get();

        $totalExpenses = $expenses->sum('amount');
        $memberCount = $members->count();

        if ($memberCount === 0) {
            return [
                'balances' => collect(),
                'settlements' => collect(),
                'total' => 0,
                'share' => 0,
            ];
        }

        $individualShare = $totalExpenses / $memberCount;

        // Calculate balances
        $balances = $members->map(function ($member) use ($expenses, $individualShare) {
            $paid = $expenses
                ->where('payer_id', $member->id)
                ->sum('amount');

            $balance = $paid - $individualShare;

            return [
                'user' => $member,
                'paid' => $paid,
                'balance' => round($balance, 2),
            ];
        });

        // Split creditors and debtors
        $creditors = $balances->filter(fn ($b) => $b['balance'] > 0)->values();
        $debtors   = $balances->filter(fn ($b) => $b['balance'] < 0)->values();

        $settlements = collect();

        foreach ($debtors as &$debtor) {
            foreach ($creditors as &$creditor) {
                if ($debtor['balance'] == 0) continue;
                if ($creditor['balance'] == 0) continue;

                $amount = min(
                    abs($debtor['balance']),
                    $creditor['balance']
                );

                $settlements->push([
                    'from' => $debtor['user'],
                    'to' => $creditor['user'],
                    'amount' => round($amount, 2),
                ]);

                $debtor['balance'] += $amount;
                $creditor['balance'] -= $amount;
            }
        }

        return [
            'balances' => $balances,
            'settlements' => $settlements,
            'total' => $totalExpenses,
            'share' => round($individualShare, 2),
        ];
    }
}