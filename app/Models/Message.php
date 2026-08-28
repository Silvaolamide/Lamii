<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;
class Message extends Model { protected $fillable=['connection_id','sender_id','body']; }
