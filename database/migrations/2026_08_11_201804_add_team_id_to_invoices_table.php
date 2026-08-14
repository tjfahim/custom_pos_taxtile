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
            // Add team_id column after created_by
            $table->foreignId('team_id')
                  ->nullable()
                  ->after('created_by')
                  ->constrained('users')
                  ->onDelete('set null');
            
            // Add index for better query performance
            $table->index('team_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('invoices', function (Blueprint $table) {
            // Drop foreign key constraint first
            $table->dropForeign(['team_id']);
            
            // Drop the column
            $table->dropColumn('team_id');
        });
    }
};