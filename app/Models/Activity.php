<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Activity extends Model
{
    use HasFactory;

    protected $fillable = [
        'contact_id',
        'aktivita',
        'datumCas',
        'koniec',
        'poznamka',
        'volane',
        'dovolane',
        'dohodnute',
        'miesto_stretnutia',
        'online_meeting'
    ];

    public function contact()
    {
        return $this->belongsTo(Contact::class);
    }
}
