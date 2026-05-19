<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Contact extends Model
{
    protected $fillable = [
        'owner_user_id',
        'target_user_id',
        'saved_name',
        'phone',
        'status',
    ];

    public function owner()
    {
        return $this->belongsTo(User::class, 'owner_user_id');
    }

    public function target()
    {
        return $this->belongsTo(User::class, 'target_user_id');
    }
}
