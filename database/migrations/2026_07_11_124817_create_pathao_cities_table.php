<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('pathao_cities', function (Blueprint $table) {
            $table->id();
            $table->string('city_id')->unique()->index();
            $table->string('city_name');
            $table->timestamps();
            // Add indexes for faster searches
            $table->index('city_name');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('pathao_cities');
    }
};