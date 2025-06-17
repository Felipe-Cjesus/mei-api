<?php

namespace App\Providers;

use App\Models\Invoice;
use App\Policies\InvoicePolicy;
use App\Models\Expense;
use App\Policies\ExpensePolicy;
use App\Models\Income;
use App\Policies\IncomePolicy;
use App\Models\DasPayment;
use App\Policies\DasPaymentPolicy;
use Illuminate\Support\ServiceProvider;

class AuthServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap services.
     */
    public function boot(): void
    {
        //
    }

    protected $policies = [
        Invoice::class => InvoicePolicy::class,
        Expense::class => ExpensePolicy::class,
        Income::class => IncomePolicy::class,
        DasPayment::class => DasPaymentPolicy::class,
    ];
}
