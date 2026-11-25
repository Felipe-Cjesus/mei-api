<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\User;
use App\Models\Enterprise;
use App\Models\Alert;
use App\Models\DasPayment;
use App\Models\Income;
use Carbon\Carbon;

class GenerateAlerts extends Command
{
    protected $signature = 'alerts:generate';
    protected $description = 'Gera alertas de vencimento da DAS e limite de faturamento do MEI por empresa';

    public function handle(): void
    {
        $users = User::with('enterprises')->get();
        $today = Carbon::today();
        $year  = now()->year;

        foreach ($users as $user) {
            // ---------- DAS vencendo em até 3 dias ----------
            $dasPayments = DasPayment::with('enterprise')
                ->where('user_id', $user->id)
                ->where('status', '!=', 'paid')
                ->whereBetween('due_date', [$today, $today->copy()->addDays(3)])
                ->get();

            foreach ($dasPayments as $das) {
                $enterpriseSuffix = $das->enterprise ? " ({$das->enterprise->company_name})" : "";
                $title = "DAS vencendo em breve{$enterpriseSuffix}";
                $message = "Sua guia DAS referente a {$das->reference}" . ($das->enterprise ? " da empresa {$das->enterprise->company_name}" : "") . " vence em " . $das->due_date->format('d/m/Y') . ".";
                $this->createAlertIfNotExists($user->id, $title, $message, 'das', $das->enterprise_id);
            }

            // ---------- DAS vencida ----------
            $dasPaymentsOverdue = DasPayment::with('enterprise')
                ->where('user_id', $user->id)
                ->where('status', '!=', 'paid')
                ->where('status', '!=', 'overdue')
                ->where('due_date', '<', $today)
                ->get();

            foreach ($dasPaymentsOverdue as $das) {
                $das->status = 'overdue';
                $das->save();

                $enterpriseSuffix = $das->enterprise ? " ({$das->enterprise->company_name})" : "";
                $title = "DAS vencida{$enterpriseSuffix}";
                $message = "Sua guia DAS referente a {$das->reference}" . ($das->enterprise ? " da empresa {$das->enterprise->company_name}" : "") . " venceu em " . $das->due_date->format('d/m/Y') . ".";
                $this->createAlertIfNotExists($user->id, $title, $message, 'das', $das->enterprise_id);
            }

            // ---------- Limite de faturamento por empresa ----------
            if ($user->enterprises->isNotEmpty()) {
                foreach ($user->enterprises as $enterprise) {
                    $totalIncome = (float) Income::where('user_id', $user->id)
                        ->where('enterprise_id', $enterprise->id)
                        ->whereYear('date', $year)
                        ->sum('amount');

                    $this->checkRevenueLimit($user->id, $totalIncome, $year, $enterprise);
                }

                // Receitas sem empresa atribuída (retrocompatibilidade)
                $unassignedIncome = (float) Income::where('user_id', $user->id)
                    ->whereNull('enterprise_id')
                    ->whereYear('date', $year)
                    ->sum('amount');

                if ($unassignedIncome > 0) {
                    $this->checkRevenueLimit($user->id, $unassignedIncome, $year, null);
                }
            } else {
                $totalIncome = (float) Income::where('user_id', $user->id)
                    ->whereYear('date', $year)
                    ->sum('amount');

                $this->checkRevenueLimit($user->id, $totalIncome, $year, null);
            }
        }

        $this->info('Alertas gerados com sucesso.');
    }

    protected function checkRevenueLimit(int $userId, float $totalIncome, int $year, ?Enterprise $enterprise): void
    {
        $limit = 81000; // Limite fiscal anual do MEI
        $percent = ($totalIncome / $limit) * 100;
        $enterpriseSuffix = $enterprise ? " ({$enterprise->company_name})" : "";
        $enterpriseSubject = $enterprise ? "A empresa {$enterprise->company_name}" : "Você";

        if ($percent >= 80 && $percent < 100) {
            $title = "Atenção: 80% do limite de faturamento atingido{$enterpriseSuffix}";
            $message = "{$enterpriseSubject} já atingiu R$ " . number_format($totalIncome, 2, ',', '.') . " em receitas este ano. O limite anual do MEI é de R$ 81.000,00.";
            $this->createAlertIfNotExists($userId, $title, $message, 'income', $enterprise?->id);
        }

        if ($percent == 100) {
            $title = "Limite de faturamento atingido!{$enterpriseSuffix}";
            $message = "{$enterpriseSubject} atingiu o limite anual de R$ 81.000,00. Não gere mais faturas, isso pode gerar obrigações como migração para ME.";
            $this->createAlertIfNotExists($userId, $title, $message, 'income', $enterprise?->id);
        }

        if ($percent > 100) {
            $title = "Limite de faturamento ultrapassado!{$enterpriseSuffix}";
            $message = "{$enterpriseSubject} ultrapassou o limite anual de R$ 81.000,00. Contate sua contabilidade.";
            $this->createAlertIfNotExists($userId, $title, $message, 'income', $enterprise?->id);
        }
    }

    protected function createAlertIfNotExists(int $userId, string $title, string $message, string $type, ?int $enterpriseId = null): void
    {
        $query = Alert::where('user_id', $userId)
            ->where('title', $title)
            ->whereDate('created_at', Carbon::today());

        if ($enterpriseId) {
            $query->where('enterprise_id', $enterpriseId);
        } else {
            $query->whereNull('enterprise_id');
        }

        if (!$query->exists()) {
            Alert::create([
                'user_id'       => $userId,
                'enterprise_id' => $enterpriseId,
                'title'         => $title,
                'message'       => $message,
                'type'          => $type,
            ]);
        }
    }
}
