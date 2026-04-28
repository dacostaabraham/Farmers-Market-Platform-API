<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('farmers', function (Blueprint $table) {
            $table->id();
            $table->string('identifier')->unique(); // ID from farmer card
            $table->string('firstname');
            $table->string('lastname');
            $table->string('phone')->unique();
            $table->string('village')->nullable();
            $table->unsignedBigInteger('credit_limit_fcfa')->default(500000); // 500,000 FCFA default
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('farmers');
    }
};
