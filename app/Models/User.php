<?php

namespace App\Models;

use Illuminate\Notifications\Notifiable;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Support\Facades\Hash;

class User extends Authenticatable {

    use Notifiable;

    /**
     * The attributes that should be hidden for arrays.
     *
     * @var array
     */
    protected $hidden = [
        'password', 'remember_token',
    ];

    /**
     * @var array
     */
    protected $guarded = [];


    /**
     * Hash password on the fly
     *
     * @param $value
     */
    public function setPasswordAttribute($value) {
        $this->attributes['password'] = Hash::make($value);
    }
}
