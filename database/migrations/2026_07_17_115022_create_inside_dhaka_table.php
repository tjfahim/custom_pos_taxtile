<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('inside_dhaka', function (Blueprint $table) {
            $table->id();
            $table->string('zone_id')->unique()->index();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
            
            // Foreign key constraint
            $table->foreign('zone_id')->references('zone_id')->on('pathao_zones')->onDelete('cascade');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('inside_dhaka');
    }
};