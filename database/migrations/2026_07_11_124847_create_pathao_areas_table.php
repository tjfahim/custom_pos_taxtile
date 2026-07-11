<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('pathao_areas', function (Blueprint $table) {
            $table->id();
            $table->string('area_id')->unique()->index();
            $table->string('area_name');
            $table->string('zone_id')->index();
            $table->boolean('home_delivery_available')->default(true);
            $table->boolean('pickup_available')->default(true);
            $table->timestamps();
            
            // Foreign key constraint
            $table->foreign('zone_id')->references('zone_id')->on('pathao_zones')->onDelete('cascade');
            
            // Add indexes for faster searches
            $table->index('area_name');
            $table->index(['zone_id', 'area_name']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('pathao_areas');
    }
};