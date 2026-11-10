<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Wholesale discount.
     *
     * discount_type   : null | 'percent' | 'fixed'
     * discount_value  : the percent (e.g. 20, 30, 50) or the fixed taka amount that was entered
     * discount_amount : the resulting taka discount that was applied to the invoice
     *
     * invoices.subtotal is stored net of returns AND of this discount, so the existing
     * identity total = subtotal + delivery_charge (used by the dashboard and reports)
     * keeps holding without touching those queries.
     */
    public function up(): void
    {
        Schema::table('invoices', function (Blueprint $table) {
            $table->string('discount_type', 20)->nullable()->after('delivery_charge');
            $table->decimal('discount_value', 10, 2)->default(0)->after('discount_type');
            $table->decimal('discount_amount', 10, 2)->default(0)->after('discount_value');
        });
    }

    public function down(): void
    {
        Schema::table('invoices', function (Blueprint $table) {
            $table->dropColumn(['discount_type', 'discount_value', 'discount_amount']);
        });
    }
};
