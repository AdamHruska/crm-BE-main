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
        Schema::table('users', function (Blueprint $table) {
            $table->text('microsoft_access_token')->nullable();
            $table->text('microsoft_refresh_token')->nullable();
            $table->timestamp('microsoft_token_expires_at')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn('microsoft_access_token');
            $table->dropColumn('microsoft_refresh_token');
            $table->dropColumn('microsoft_token_expires_at');
        });
    }
};
