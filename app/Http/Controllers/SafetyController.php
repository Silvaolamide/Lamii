<?php
namespace App\Http\Controllers;
use App\Models\Block;
use App\Models\Connection;
use App\Models\Report;
use App\Models\User;
use Illuminate\Http\Request;
class SafetyController extends Controller {
 public function block(Request $request, User $user) { abort_if($user->id===$request->user()->id,422); $me=$request->user(); Block::firstOrCreate(['blocker_id'=>$me->id,'blocked_id'=>$user->id]); Connection::where(fn($q)=>$q->where(fn($q)=>$q->where('sender_id',$me->id)->where('recipient_id',$user->id))->orWhere(fn($q)=>$q->where('sender_id',$user->id)->where('recipient_id',$me->id)))->delete(); return response()->json(['ok'=>true,'message'=>'User blocked.']); }
 public function unblock(Request $request, User $user) { Block::where('blocker_id',$request->user()->id)->where('blocked_id',$user->id)->delete(); return response()->json(['ok'=>true]); }
 public function report(Request $request, User $user) { abort_if($user->id===$request->user()->id,422); $data=$request->validate(['reason'=>['required','string','max:100'],'details'=>['nullable','string','max:2000']]); Report::create(['reporter_id'=>$request->user()->id,'reported_id'=>$user->id,...$data]); return response()->json(['ok'=>true,'message'=>'Report submitted. Thank you for helping keep Lamii safe.']); }
}
