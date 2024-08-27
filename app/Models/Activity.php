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
        'poznamka',
        'volane',
        'dovolane',
        'dohodnute'
    ];

    public function contact()
    {
        return $this->belongsTo(Contact::class);
    }
}
