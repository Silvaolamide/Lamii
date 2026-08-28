<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;
class Connection extends Model { protected $fillable=['user_one_id','user_two_id']; public static function orderedPair(int $a,int $b): array { return $a<$b?[$a,$b]:[$b,$a]; } }
