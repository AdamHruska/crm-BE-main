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
        Schema::create('contacts', function (Blueprint $table) {
            $table->id(); // Auto-incrementing ID
            $table->string('meno'); // Contact's first name
            $table->string('priezvisko'); // Contact's last name
            $table->string('poradca'); // Advisor or contact person
            $table->string('cislo')->nullable()->default(null); // Phone number
            $table->string('email')->unique()->nullable()->default(null);   // Contact's email address
            $table->string('odporucitel'); // Referrer
            $table->string('adresa'); // Address
            $table->year('rok_narodenia'); // age
            $table->string('zamestanie')->nullable()->default(null); // Occupation or employment status
            $table->text('poznamka')->nullable()->default(null); // Additional notes
            $table->date('Investicny_dotaznik')->nullable()->default(null); // Investment questionnaire date
            $table->string('author_id'); // Author's ID
            $table->timestamps(); // Created_at and updated_at columns
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::disableForeignKeyConstraints();

        // Drop the contacts table
        Schema::dropIfExists('contacts');

        // Re-enable foreign key checks after dropping the table
        Schema::enableForeignKeyConstraints();
    }
};
