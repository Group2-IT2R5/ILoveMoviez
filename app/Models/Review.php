<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Review extends Model
{
    protected $fillable = ['user_id', 'movie_title', 'rating', 'review'];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
