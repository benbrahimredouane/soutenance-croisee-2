<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Colocation extends Model
{
    //
    protected $fillable = [
        'owner_id',
        'name',
        'status',
        'cancelled_at',
    ];

    public function owner()
    {
        return $this->belongsTo(User::class, 'owner_id');
    }

    public function memberships()
    {
        return $this->hasMany(Membership::class);
    }
    public function members()
    {
        return $this->belongsToMany(User::class, 'memberships')->withPivot(['role', 'left_at'])->withTimestamps();
    }
    public function invitations()
    {
        return $this->hasMany(Invitation::class);
    }
    public function categories()
    {
        return $this->hasMany(Category::class);
    }
    public function expenses()
{
    return $this->hasMany(Expense::class);
}
}
