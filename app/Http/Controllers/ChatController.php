<?php
namespace App\Http\Controllers;
use App\Models\{Connection,Message};
use Illuminate\Http\Request;
class ChatController extends Controller { private function connection(Request $r,int $peer){$id=$r->user()->id;[$a,$b]=Connection::orderedPair($id,$peer);return Connection::where(['user_one_id'=>$a,'user_two_id'=>$b])->firstOrFail();} public function show(Request $r,int $user){$c=$this->connection($r,$user);return response()->json(['messages'=>Message::where('connection_id',$c->id)->oldest()->get()]);} public function store(Request $r,int $user){$c=$this->connection($r,$user);$body=$r->validate(['body'=>'required|string|max:2000'])['body'];$m=Message::create(['connection_id'=>$c->id,'sender_id'=>$r->user()->id,'body'=>$body]);return response()->json(['message'=>$m],201);} }
