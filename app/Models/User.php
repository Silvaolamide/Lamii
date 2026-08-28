<?php
namespace App\Models;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
class User extends Authenticatable { use Notifiable; protected $fillable=['name','email','password','avatar','provider','provider_id']; protected $hidden=['password','remember_token']; public function location(){return $this->hasOne(UserLocation::class);} }
