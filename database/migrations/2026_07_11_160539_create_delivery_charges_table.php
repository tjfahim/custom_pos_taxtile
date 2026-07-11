<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('delivery_charges', function (Blueprint $table) {
            $table->id();
            $table->decimal('from_range', 10, 2)->default(0);
            $table->decimal('to_range', 10, 2)->nullable();
            $table->decimal('inside_dhaka_price', 10, 2)->default(0);
            $table->decimal('outside_dhaka_price', 10, 2)->default(0);
            $table->timestamps();
            
            // Indexes for faster queries
            $table->index(['from_range', 'to_range']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('delivery_charges');
    }
};