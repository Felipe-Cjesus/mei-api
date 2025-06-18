<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\User;
use App\Models\Alert;
use App\Models\DasPayment;
use App\Models\Income;
use Carbon\Carbon;

class GenerateAlerts extends Command
{
    protected $signature = 'alerts:generate';
    protected $description = 'Gera alertas de vencimento da DAS e limite de faturamento do MEI';

    public function handle(): void
    {
        $users = User::all();
        $today = Carbon::today();
        $year  = now()->year;

        foreach ($users as $user) {
            // ---------- DAS vencendo em at� 5 dias ----------
            $dasPayments = DasPayment::where('user_id', $user->id)
                ->where('status', '!=', 'paid')
                ->whereBetween('due_date', [$today, $today->copy()->addDays(5)])
                ->get();

            foreach ($dasPayments as $das) {
                $title = "DAS vencendo em breve";
                $message = "Sua guia DAS referente a {$das->reference} vence em " . $das->due_date->format('d/m/Y') . ".";
                $this->createAlertIfNotExists($user->id, $title, $message, 'das');
            }

            // ---------- Limite de faturamento ----------
            $limit = 81000; // MEI Ref-2025
            // $year = $today->year;
            $totalIncome = Income::where('user_id', $user->id)
                ->whereYear('date', $year)
                ->sum('amount');

            $percent = ($totalIncome / $limit) * 100;

            if ($percent >= 80 && $percent < 100) {
                $title = "Atenção: 80% do limite de faturamento atingido";
                $message = "Você já atingiu R$ " . number_format($totalIncome, 2, ',', '.') . " em receitas este ano. O limite anual do MEI é de R$ 81.000,00.";
                $this->createAlertIfNotExists($user->id, $title, $message, 'income');
            }

            if ($percent == 100) {
                $title = "Limite de faturamento atingido!";
                $message = "Você atingiu o limite anual de R$ 81.000,00. Não gere mais faturas, isso pode gerar obrigações como migração para ME.";
                $this->createAlertIfNotExists($user->id, $title, $message, 'income');
            }

            if ($percent > 100) {
                $title = "Limite de faturamento ultrapassado!";
                $message = "Você ultrapassou o limite anual de R$ 81.000,00. Contate sua contabilidade.";
                $this->createAlertIfNotExists($user->id, $title, $message, 'income');
            }
        }

        $this->info('Alertas gerados com sucesso.');
    }

    protected function createAlertIfNotExists($userId, $title, $message, $type): void
    {
        $alreadyExists = Alert::where('user_id', $userId)
            ->where('title', $title)
            ->whereDate('created_at', Carbon::today())
            ->exists();

        if (!$alreadyExists) {
            Alert::create([
                'user_id' => $userId,
                'title' => $title,
                'message' => $message,
                'type' => $type,
            ]);
        }
    }
}
