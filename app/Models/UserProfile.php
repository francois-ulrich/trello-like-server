<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class UserProfile extends Model
{
    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'user_id',
        'profile_pic',
        'color'
    ];

    // Définissez la relation avec l'utilisateur
    public function user() : BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
