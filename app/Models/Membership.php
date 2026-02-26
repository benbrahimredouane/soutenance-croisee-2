<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Membership extends Model
{
    //
    protected $fillable = [
        'user_id',
        'colocation_id',
        'role',
        'left_at',
    ];
    protected $casts = [
        'left_at' => 'datetime',
    ];
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function colocation()
    {
        return $this->belongsTo(Colocation::class);
    }


      public function isOwner(): bool
    {
        return $this->role === 'owner';
    }

    public function isActive(): bool
    {
        return is_null($this->left_at);
    }
}
