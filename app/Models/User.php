<?php

namespace App\Models;

use Illuminate\Auth\Authenticatable;
use Illuminate\Database\Eloquent\Model;
use Tymon\JWTAuth\Contracts\JWTSubject;
use Illuminate\Contracts\Auth\Authenticatable as AuthenticatableContract;

class User extends Model implements JWTSubject, AuthenticatableContract
{
    use Authenticatable;

    protected $primaryKey = 'user_id';  // Specify your custom primary key here

    protected $fillable = ['name', 'email', 'password'];

    protected $hidden = ['password'];

    public function getJWTIdentifier()
    {
        return $this->getKey();
    }

    public function getJWTCustomClaims()
    {
        return [];
    }

    public $timestamps = false;
}
