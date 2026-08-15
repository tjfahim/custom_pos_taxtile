<?php
// database/migrations/2024_01_01_create_attendances_table.php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('attendances', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->date('attendance_date');
            $table->time('in_time')->nullable();
            $table->time('out_time')->nullable();
            $table->boolean('is_friday')->default(false);
            $table->boolean('is_govt_holiday')->default(false);
            $table->boolean('on_leave')->default(false);
            $table->text('note')->nullable();
            $table->enum('status', ['present', 'absent', 'late', 'half_day', 'holiday', 'friday', 'leave'])->default('present');
            $table->timestamps();
            
            // Unique constraint to prevent duplicate attendance per user per day
            $table->unique(['user_id', 'attendance_date']);
            
            // Indexes for better performance
            $table->index('attendance_date');
            $table->index('status');
            $table->index(['is_friday', 'is_govt_holiday', 'on_leave']);
        });
    }

    public function down()
    {
        Schema::dropIfExists('attendances');
    }
};