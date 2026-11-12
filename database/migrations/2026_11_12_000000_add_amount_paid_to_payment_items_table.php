<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasColumn('payment_items', 'amount_paid')) {
            Schema::table('payment_items', function (Blueprint $table) {
                $table->decimal('amount_paid', 15, 2)->nullable();
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasColumn('payment_items', 'amount_paid')) {
            Schema::table('payment_items', function (Blueprint $table) {
                $table->dropColumn('amount_paid');
            });
        }
    }
};
