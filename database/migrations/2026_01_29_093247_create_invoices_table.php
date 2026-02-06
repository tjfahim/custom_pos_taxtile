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
       Schema::create('invoices', function (Blueprint $table) {
    $table->id();
    $table->string('invoice_number')->unique();
    $table->string('store_location');
    $table->string('product_type')->nullable();
    $table->string('merchant_order_id')->nullable();
    
    // Recipient Information
    $table->foreignId('customer_id')->constrained()->onDelete('cascade');
    $table->string('recipient_name');
    $table->string('recipient_phone');
    $table->string('recipient_secondary_phone')->nullable();
    $table->text('recipient_address');
    $table->string('delivery_area');
    
    // Delivery Information
    $table->string('delivery_type');
    $table->decimal('delivery_charge', 10, 2)->default(0);
    $table->decimal('total_weight', 10, 2)->default(0);
    $table->text('special_instructions')->nullable();
    
    // Financial Information
    $table->date('invoice_date')->default(now());
    $table->decimal('subtotal', 10, 2)->default(0); // Changed from total to subtotal
    $table->decimal('total', 10, 2)->default(0); // Subtotal + delivery_charge
    $table->decimal('amount_to_collect', 10, 2)->default(0);
    $table->decimal('paid_amount', 10, 2)->default(0);
    $table->decimal('due_amount', 10, 2)->default(0);
    $table->string('payment_status')->default('unpaid');
    $table->string('status')->default('pending');
    $table->integer('pathao_city_id')->nullable();
    $table->integer('pathao_zone_id')->nullable();
    $table->integer('pathao_area_id')->nullable();
    $table->string('payment_method')->nullable();
    $table->text('payment_details')->nullable();
    
    // Notes
    $table->text('notes')->nullable();
    
    $table->timestamps();
    $table->softDeletes();
});
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('invoices');
    }
};
