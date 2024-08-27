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
            $table->timestamp('datumCas');
            $table->text('poznamka')->nullable();
            // Removed `aktivitaZadana`
            $table->integer('volane')->default(0);
            $table->integer('dovolane')->default(0);
            $table->integer('dohodnute')->default(0);
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
