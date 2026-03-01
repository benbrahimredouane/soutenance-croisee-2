<?php

namespace App\Services;

use App\Models\Colocation;
use App\Models\Settlement;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Collection;

class SettlementService
{
    /**
     * Returns per-member summary for UI:
     * paid / share / balance
     */
    public function getMemberSummaries(Colocation $colocation, string $month = 'all'): array
    {
        $state = $this->buildStateInCents($colocation, $month);

        $summaries = [];
        foreach ($state['member_ids'] as $memberId) {
            $summaries[$memberId] = [
                'paid' => $this->fromCents($state['paid'][$memberId]),
                'share' => $this->fromCents($state['share'][$memberId]),
                'balance' => $this->fromCents($state['balances'][$memberId]),
            ];
        }

        return $summaries;
    }

    /**
     * Deletes old UNPAID settlements for the month and regenerates them
     * from the current balances (which already include paid settlements).
     */
    public function refreshPendingSettlements(Colocation $colocation, string $month = 'all'): void
    {
        $state = $this->buildStateInCents($colocation, $month);
        $balances = $state['balances'];

        DB::transaction(function () use ($colocation, $month, $balances): void {

            // Delete only UNPAID settlements for this month (pending)
            Settlement::query()
                ->where('colocation_id', $colocation->id)
                ->where('month', $month)
                ->where('is_paid', false)
                ->delete();

            $creditors = [];
            $debtors = [];

            foreach ($balances as $userId => $balanceCents) {
                if ($balanceCents > 0) {
                    $creditors[] = ['user_id' => $userId, 'amount' => $balanceCents];
                } elseif ($balanceCents < 0) {
                    $debtors[] = ['user_id' => $userId, 'amount' => abs($balanceCents)];
                }
            }

            // Largest first helps produce simpler settlements
            usort($creditors, fn ($a, $b) => $b['amount'] <=> $a['amount']);
            usort($debtors, fn ($a, $b) => $b['amount'] <=> $a['amount']);

            $di = 0;
            $ci = 0;

            while (isset($debtors[$di], $creditors[$ci])) {
                $amountToSettle = min($debtors[$di]['amount'], $creditors[$ci]['amount']);

                if ($amountToSettle <= 0) {
                    break;
                }

                Settlement::create([
                    'colocation_id' => $colocation->id,
                    'from_user_id' => $debtors[$di]['user_id'],   // debtor
                    'to_user_id' => $creditors[$ci]['user_id'],   // creditor
                    'amount' => $this->fromCents($amountToSettle),
                    'is_paid' => false,
                    'paid_at' => null,
                    'month' => $month,
                ]);

                $debtors[$di]['amount'] -= $amountToSettle;
                $creditors[$ci]['amount'] -= $amountToSettle;

                if ($debtors[$di]['amount'] === 0) $di++;
                if ($creditors[$ci]['amount'] === 0) $ci++;
            }
        });
    }

    /**
     * Core: compute balances in cents:
     * balance = paid - share, then apply PAID settlements to reduce debt.
     */
    private function buildStateInCents(Colocation $colocation, string $month = 'all'): array
    {
        // Active members in this colocation
        $memberIds = $colocation->members()
            ->wherePivotNull('left_at')
            ->pluck('users.id')
            ->all();

        if ($memberIds === []) {
            return [
                'member_ids' => [],
                'paid' => [],
                'share' => [],
                'balances' => [],
            ];
        }

        // Init arrays
        $paid = $share = $balances = [];
        foreach ($memberIds as $id) {
            $paid[$id] = 0;
            $share[$id] = 0;
            $balances[$id] = 0;
        }

        // Get expenses filtered by month
        $expensesQuery = $colocation->expenses()->getQuery()->select(['payer_id', 'amount', 'date']);

        if ($month !== 'all') {
            // Validate month format to avoid exceptions
            if (!preg_match('/^\d{4}-\d{2}$/', $month)) {
                $month = 'all';
            } else {
                $start = \Carbon\Carbon::createFromFormat('Y-m', $month)->startOfMonth()->toDateString();
                $end = \Carbon\Carbon::createFromFormat('Y-m', $month)->endOfMonth()->toDateString();
                $expensesQuery->whereBetween('date', [$start, $end]);
            }
        }

        $expenses = $expensesQuery->get();

        // Total in cents
        $totalCents = 0;
        foreach ($expenses as $expense) {
            $totalCents += $this->toCents($expense->amount);
        }

        // Equal split share per member (in cents) with remainder distribution
        $count = count($memberIds);
        $baseShare = intdiv($totalCents, $count);
        $remainder = $totalCents % $count;

        // Deterministic: distribute remainder to first members (by id order)
        sort($memberIds);

        foreach ($memberIds as $idx => $memberId) {
            $memberShare = $baseShare + ($idx < $remainder ? 1 : 0);
            $share[$memberId] = $memberShare;
            $balances[$memberId] -= $memberShare; // everyone starts owing their share
        }

        // Add what each member paid
        $activeLookup = array_flip($memberIds);

        foreach ($expenses as $expense) {
            $amountCents = $this->toCents($expense->amount);

            if (isset($activeLookup[$expense->payer_id])) {
                $paid[$expense->payer_id] += $amountCents;
                $balances[$expense->payer_id] += $amountCents;
            }
        }

        // Apply PAID settlements (payments reduce debt)
        $paidSettlements = Settlement::query()
            ->where('colocation_id', $colocation->id)
            ->where('month', $month)
            ->where('is_paid', true)
            ->get(['from_user_id', 'to_user_id', 'amount']);

        foreach ($paidSettlements as $st) {
            // ignore if someone left
            if (!isset($activeLookup[$st->from_user_id]) || !isset($activeLookup[$st->to_user_id])) {
                continue;
            }

            $amt = $this->toCents($st->amount);

            // debtor paid -> less debt
            $balances[$st->from_user_id] += $amt;
            // creditor received -> less credit
            $balances[$st->to_user_id] -= $amt;
        }

        return [
            'member_ids' => $memberIds,
            'paid' => $paid,
            'share' => $share,
            'balances' => $balances,
        ];
    }

    private function toCents(float|string $amount): int
    {
        return (int) round(((float) $amount) * 100);
    }

    private function fromCents(int $cents): string
    {
        return number_format($cents / 100, 2, '.', '');
    }
}