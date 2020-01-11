<?php

namespace App;

use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Spatie\Permission\Traits\HasRoles;
use Tymon\JWTAuth\Contracts\JWTSubject;

class User extends Authenticatable implements JWTSubject
{
    use Notifiable, HasRoles;

    // Rest omitted for brevity

    /**
     * Get the identifier that will be stored in the subject claim of the JWT.
     *
     * @return mixed
     */
    public function getJWTIdentifier()
    {
        return $this->getKey();
    }

    /**
     * Return a key value array, containing any custom claims to be added to the JWT.
     *
     * @return array
     */
    public function getJWTCustomClaims()
    {
        return [];
    }
    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'name', 'email', 'password',
    ];

    /**
     * The attributes that should be hidden for arrays.
     *
     * @var array
     */
    protected $hidden = [
        'password', 'remember_token',
    ];

    /**
     * The attributes that should be cast to native types.
     *
     * @var array
     */
    protected $casts = [
        'email_verified_at' => 'datetime',
    ];

    public function userPreference() {
        return $this->hasOne('App\UserPreference');
    }

    public function scopeFilterByEmail($q, $email = null, $s = 0) {
        if (!$email) {
            return $q;
        }

        return ($s) ? $q->where('email', '=', $email) : $q->where('email', 'like', '%' . $email . '%');
    }

    public function scopeFilterByUsername($q, $username = null, $s = 0) {
        if (!$username) {
            return $q;
        }

        return ($s) ? $q->where('username', '=', $username) : $q->where('username', 'like', '%' . $username . '%');
    }
}
