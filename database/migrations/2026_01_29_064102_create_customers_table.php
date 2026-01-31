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
        Schema::create('customers', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->text('full_address');
            $table->string('phone_number_1');
            $table->string('phone_number_2')->nullable();
            $table->string('delivery_area')->nullable();
            $table->text('note')->nullable();
            $table->string('status')->default('active');
            $table->timestamps();
            $table->softDeletes(); 
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('customers');
    }
};
