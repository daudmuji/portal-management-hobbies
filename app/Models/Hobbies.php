<?php

namespace App\Models;

use App\Traits\IsActive;
use Illuminate\Database\Eloquent\Model;

class Hobbies extends Model
{

    use IsActive;

    protected $guarded = ['id'];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
