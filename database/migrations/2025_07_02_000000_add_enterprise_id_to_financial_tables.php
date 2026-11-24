<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('incomes', function (Blueprint $table) {
            $table->foreignId('enterprise_id')->nullable()->after('user_id')->constrained('enterprises')->onDelete('cascade');
        });

        Schema::table('expenses', function (Blueprint $table) {
            $table->foreignId('enterprise_id')->nullable()->after('user_id')->constrained('enterprises')->onDelete('cascade');
        });

        Schema::table('invoices', function (Blueprint $table) {
            $table->foreignId('enterprise_id')->nullable()->after('user_id')->constrained('enterprises')->onDelete('cascade');
        });

        Schema::table('das_payments', function (Blueprint $table) {
            $table->foreignId('enterprise_id')->nullable()->after('user_id')->constrained('enterprises')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('das_payments', function (Blueprint $table) {
            $table->dropConstrainedForeignId('enterprise_id');
        });

        Schema::table('invoices', function (Blueprint $table) {
            $table->dropConstrainedForeignId('enterprise_id');
        });

        Schema::table('expenses', function (Blueprint $table) {
            $table->dropConstrainedForeignId('enterprise_id');
        });

        Schema::table('incomes', function (Blueprint $table) {
            $table->dropConstrainedForeignId('enterprise_id');
        });
    }
};

