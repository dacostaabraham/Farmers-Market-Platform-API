<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('repayments', function (Blueprint $table) {
            $table->id();
            $table->string('reference')->unique(); // e.g. REP-20240101-0001
            $table->foreignId('farmer_id')->constrained()->restrictOnDelete();
            $table->foreignId('operator_id')->constrained('users')->restrictOnDelete();
            $table->decimal('commodity_kg', 10, 3);         // kg of commodity received
            $table->unsignedBigInteger('commodity_rate_fcfa'); // rate used at repayment time (FCFA per kg)
            $table->unsignedBigInteger('total_fcfa_credited'); // commodity_kg * commodity_rate_fcfa
            $table->text('notes')->nullable();
            $table->timestamps();
        });

        // Pivot: which debts were affected by each repayment
        Schema::create('debt_repayment', function (Blueprint $table) {
            $table->id();
            $table->foreignId('repayment_id')->constrained()->cascadeOnDelete();
            $table->foreignId('debt_id')->constrained()->cascadeOnDelete();
            $table->unsignedBigInteger('amount_applied_fcfa'); // how much of this repayment went to this debt
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('debt_repayment');
        Schema::dropIfExists('repayments');
    }
};
