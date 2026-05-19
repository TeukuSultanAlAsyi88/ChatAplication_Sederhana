<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ChatGroup extends Model
{
    protected $fillable = [
        'name',
        'description',
        'created_by',
    ];

    public function members()
    {
        return $this->belongsToMany(User::class, 'group_members');
    }

    public function messages()
    {
        return $this->hasMany(GroupMessage::class, 'group_id');
    }
}