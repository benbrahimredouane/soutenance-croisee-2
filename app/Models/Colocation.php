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
    ];
}
