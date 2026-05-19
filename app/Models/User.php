<?php

namespace App\Models;

use App\Models\Group;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable;

    protected $fillable = [
        'name',
        'phone',
        'email',
        'birth_date',
        'status',
        'bio',
        'avatar',
        'is_online',
        'last_seen_at',
        'show_online',
        'show_last_seen',
        'password',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected $casts = [
        'email_verified_at' => 'datetime',
        'birth_date' => 'date',
        'last_seen_at' => 'datetime',
        'is_online' => 'boolean',
        'show_online' => 'boolean',
        'show_last_seen' => 'boolean',
    ];

    protected static function booted()
    {
        static::created(function ($user) {
            $group = Group::firstOrCreate([
                'name' => 'Grup Chat',
            ]);

            $group->members()->syncWithoutDetaching([
                $user->id,
            ]);
        });
    }

    public function groups()
    {
        return $this->belongsToMany(Group::class, 'group_user')
            ->withTimestamps();
    }

    public function messages()
    {
        return $this->hasMany(Message::class);
    }

    public function groupMessages()
    {
        return $this->hasMany(GroupMessage::class);
    }

    public function getAvatarUrlAttribute(): string
    {
        return $this->avatar
            ? asset('storage/'.$this->avatar)
            : asset('uploads/profile/default-avatar.svg');
    }

    public function getDisplayStatusAttribute(): string
    {
        return $this->bio ?: ($this->status ?: 'Belum ada bio');
    }
}