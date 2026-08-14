<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('user_team_members', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->foreignId('team_member_id')->constrained('users')->onDelete('cascade');
            $table->timestamps();
            
            $table->unique(['user_id', 'team_member_id']);
        });

        // Add default_team_mate_id column to users table
        Schema::table('users', function (Blueprint $table) {
            $table->foreignId('default_team_mate_id')->nullable()->constrained('users')->onDelete('set null');
        });
    }

    public function down()
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropForeign(['default_team_mate_id']);
            $table->dropColumn('default_team_mate_id');
        });
        
        Schema::dropIfExists('user_team_members');
    }
};