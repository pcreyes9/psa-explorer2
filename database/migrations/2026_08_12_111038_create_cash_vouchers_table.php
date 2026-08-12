<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('cash_vouchers', function (Blueprint $table) {
            $table->id();

            $table->string('voucher_no')->unique();
            $table->date('date');

            $table->string('pay_to');
            $table->text('address')->nullable();
            $table->string('check_no')->nullable();

            $table->decimal('total_amount', 15, 2)->default(0);

            $table->string('approved_by')->nullable();
            $table->string('checked_by')->nullable();
            $table->string('received_by')->nullable();

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('cash_vouchers');
    }
};