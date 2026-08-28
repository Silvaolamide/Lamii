<?php
namespace App\Http\Controllers;
use App\Models\{Wave,Connection,UserLocation};
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
class WaveController extends Controller {
 public function store(Request $r){$data=$r->validate(['to_user_id'=>'required|integer|exists:users,id']); if($data['to_user_id']===$r->user()->id)return response()->json(['message'=>'You cannot wave at yourself'],422); $target=UserLocation::where('user_id',$data['to_user_id'])->where('visible',true)->first(); if(!$target)return response()->json(['message'=>'This person is not currently visible'],403); $wave=Wave::firstOrCreate(['from_user_id'=>$r->user()->id,'to_user_id'=>$data['to_user_id']],['status'=>'pending']); return response()->json(['wave'=>$wave->load('fromUser')],$wave->wasRecentlyCreated?201:200);}
 public function incoming(Request $r){return response()->json(['waves'=>Wave::with('fromUser')->where('to_user_id',$r->user()->id)->where('status','pending')->latest()->get()->map(fn($w)=>['id'=>$w->id,'from_user_id'=>$w->from_user_id,'from_name'=>$w->fromUser->name,'picture'=>$w->fromUser->avatar])]);}
 public function respond(Request $r,Wave $wave){abort_unless($wave->to_user_id===$r->user()->id,403); if($wave->status!=='pending')return response()->json(['message'=>'Wave already handled'],409); $status=$r->validate(['status'=>'required|in:accepted,declined'])['status']; DB::transaction(function()use($wave,$status){$wave->update(['status'=>$status,'responded_at'=>now()]); if($status==='accepted'){[$a,$b]=Connection::orderedPair($wave->from_user_id,$wave->to_user_id);Connection::firstOrCreate(['user_one_id'=>$a,'user_two_id'=>$b]);}}); return response()->json(['status'=>$status,'connected'=>$status==='accepted']);}
 public function connections(Request $r){$id=$r->user()->id; $rows=Connection::where('user_one_id',$id)->orWhere('user_two_id',$id)->get();$ids=$rows->map(fn($c)=>$c->user_one_id===$id?$c->user_two_id:$c->user_one_id);return response()->json(['people'=>\App\Models\User::whereIn('id',$ids)->get()->map(fn($u)=>['id'=>$u->id,'name'=>$u->name,'picture'=>$u->avatar])]);}
}
