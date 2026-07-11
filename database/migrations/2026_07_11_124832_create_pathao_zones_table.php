<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('pathao_zones', function (Blueprint $table) {
            $table->id();
            $table->string('zone_id')->unique()->index();
            $table->string('zone_name');
            $table->string('city_id')->index();
            $table->timestamps();
            
            // Foreign key constraint
            $table->foreign('city_id')->references('city_id')->on('pathao_cities')->onDelete('cascade');
            
            // Add indexes for faster searches
            $table->index('zone_name');
            $table->index(['city_id', 'zone_name']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('pathao_zones');
    }
};