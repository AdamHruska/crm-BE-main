<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Contact extends Model
{
    use HasFactory;

    protected $table = 'contacts';

    // Specify which attributes are mass assignable
    protected $fillable = [
        'meno',
        'priezvisko',
        'poradca',
        'cislo',
        'email',
        'odporucitel',
        'adresa',
        'vek',
        'zamestanie',
        'poznamka',
        'Investicny_dotaznik',
        'author_id',
    ];
   

    // Add timestamps if you want to use Laravel's automatic created_at and updated_at fields
    public $timestamps = true; // Set to true if your table uses created_at and updated_at
}
