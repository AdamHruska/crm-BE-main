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
        Schema::create('activities', function (Blueprint $table) {
            $table->id();
            $table->foreignId('contact_id')->constrained('contacts')->onDelete('cascade');
            $table->string('aktivita');
            $table->timestamp('datumCas')->nullable();
            $table->timestamp('koniec')->nullable();
            $table->text('poznamka')->nullable();
            // Removed `aktivitaZadana`
            $table->integer('volane')->nullable()->default(null);
            $table->integer('dovolane')->nullable()->default(null);
            $table->integer('dohodnute')->nullable()->default(null);
            $table->string('miesto_stretnutia')->nullable()->default(null);
            $table->boolean('online_meeting')->default(false);
            $table->timestamps(); // This includes `created_at` and `updated_at`
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('activities');
    }
};
