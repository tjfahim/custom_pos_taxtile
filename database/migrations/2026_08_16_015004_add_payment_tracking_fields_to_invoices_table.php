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
        Schema::table('invoices', function (Blueprint $table) {
             $table->timestamp('payment_date')->nullable()->after('confirmed_at');
            $table->decimal('paid_amount2', 10, 2)->nullable()->default(0)->after('paid_amount');
            $table->string('payment_method2')->nullable()->after('payment_method');
            $table->text('payment_details2')->nullable()->after('payment_details');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('invoices', function (Blueprint $table) {
              $table->dropColumn('payment_date');
            $table->dropColumn('paid_amount2');
            $table->dropColumn('payment_method2');
            $table->dropColumn('payment_details2');
        });
    }
};
