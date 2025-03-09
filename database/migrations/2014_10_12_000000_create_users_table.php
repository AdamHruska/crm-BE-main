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
        Schema::create('users', function (Blueprint $table) {
            $table->id();
            $table->string('username')->unique();
            $table->string('first_name');
            $table->string('last_name');
            $table->string('email')->unique();
            $table->integer('tokens')->default(100);
            $table->string('password');
            $table->json('share_user_id')->nullable()->default('[]');
            $table->json('confirmed_share_user_id')->nullable()->default('[]'); // New column

            // $table->text('microsoft_access_token')->nullable();
            // $table->text('microsoft_refresh_token')->nullable();
            // $table->timestamp('microsoft_token_expires_at')->nullable();

            $table->rememberToken();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('users');
    }
};