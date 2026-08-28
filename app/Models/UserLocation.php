<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
class UserLocation extends Model {
    protected $fillable=['user_id','latitude','longitude','visible','last_seen'];
    protected $casts=['latitude'=>'float','longitude'=>'float','visible'=>'boolean','last_seen'=>'datetime'];
    public function user(): BelongsTo { return $this->belongsTo(User::class); }
}
