<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CalendarSharingRequest extends Model
{
    use HasFactory;

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'calendar_sharing_requests';

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'requester_name',
        'requester_id',
        'target_user_name',
        'target_user_id',
        'type',
        'status',
    ];

    /**
     * Relationships: Link to the `User` model for requester.
     */
    public function requester()
    {
        return $this->belongsTo(User::class, 'requester_id');
    }

    /**
     * Relationships: Link to the `User` model for target user.
     */
    public function targetUser()
    {
        return $this->belongsTo(User::class, 'target_user_id');
    }
}
