<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Group extends Model
{
    protected $table = 'chat_groups';

    protected $fillable = [
        'name',
        'description',
        'created_by',
    ];

    public function members()
    {
        return $this->belongsToMany(
            User::class,
            'group_members',
            'chat_group_id',
            'user_id'
        );
    }

    public function messages()
    {
        return $this->hasMany(GroupMessage::class, 'group_id');
    }
}