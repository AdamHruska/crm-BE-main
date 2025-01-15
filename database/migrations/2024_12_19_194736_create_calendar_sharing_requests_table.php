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
        Schema::create('calendar_sharing_requests', function (Blueprint $table) {
            $table->id(); // Primary key

            // Requester details
            $table->string('requester_name'); // Name of the requester
            $table->unsignedBigInteger('requester_id'); // ID of the requester

            // Target user details
            $table->string('target_user_name'); // Name of the user whose calendar is targeted
            $table->unsignedBigInteger('target_user_id'); // ID of the targeted user

            // Type: whether requester wants to view their calendar or let them view the requester's calendar
            $table->enum('type', ['view_their_calendar', 'let_them_view_mine']);

            // Invitation status
            $table->enum('status', ['pending', 'accepted'])->default('pending'); // Default status is pending

            $table->timestamps(); // created_at and updated_at

            // Foreign key constraints (optional)
            $table->foreign('requester_id')->references('id')->on('users')->onDelete('cascade');
            $table->foreign('target_user_id')->references('id')->on('users')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('calendar_sharing_requests');
    }
};
