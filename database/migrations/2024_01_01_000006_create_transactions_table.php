<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('transactions', function (Blueprint $table) {
            $table->id();
            $table->string('reference')->unique(); // e.g. TXN-20240101-0001
            $table->foreignId('farmer_id')->constrained()->restrictOnDelete();
            $table->foreignId('operator_id')->constrained('users')->restrictOnDelete();
            $table->unsignedBigInteger('subtotal_fcfa');    // Sum of products (no interest)
            $table->unsignedBigInteger('total_fcfa');       // Final amount (with interest if credit)
            $table->enum('payment_method', ['cash', 'credit']);
            $table->decimal('interest_rate', 5, 2)->nullable(); // e.g. 30.00 for 30%
            $table->unsignedBigInteger('interest_amount_fcfa')->default(0);
            $table->enum('status', ['completed', 'pending'])->default('completed');
            $table->text('notes')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('transactions');
    }
};
