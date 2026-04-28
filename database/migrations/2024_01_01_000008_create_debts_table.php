<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('debts', function (Blueprint $table) {
            $table->id();
            $table->foreignId('farmer_id')->constrained()->restrictOnDelete();
            $table->foreignId('transaction_id')->constrained()->restrictOnDelete();
            $table->unsignedBigInteger('original_amount_fcfa');  // total with interest
            $table->unsignedBigInteger('remaining_amount_fcfa'); // decreases as repaid
            $table->enum('status', ['open', 'partially_paid', 'paid'])->default('open');
            $table->timestamp('due_date')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('debts');
    }
};
