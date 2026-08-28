<?php
namespace App\Http\Controllers;
use App\Models\UserLocation;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
class NearbyController extends Controller {
 private function haversine(float $lat1,float $lon1,float $lat2,float $lon2): float { $p=pi()/180; $a=.5-cos(($lat2-$lat1)*$p)/2+cos($lat1*$p)*cos($lat2*$p)*(1-cos(($lon2-$lon1)*$p))/2; return 12742*asin(sqrt($a)); }
 public function updateLocation(Request $r){ $data=$r->validate(['latitude'=>'required|numeric|between:-90,90','longitude'=>'required|numeric|between:-180,180','visible'=>'nullable|boolean']); $loc=UserLocation::updateOrCreate(['user_id'=>$r->user()->id],['latitude'=>$data['latitude'],'longitude'=>$data['longitude'],'visible'=>$data['visible']??true,'last_seen'=>now()]); return response()->json(['visible'=>$loc->visible,'people'=>$this->people($r)]); }
 public function visibility(Request $r){ $visible=$r->boolean('visible'); $loc=UserLocation::firstOrCreate(['user_id'=>$r->user()->id],['latitude'=>0,'longitude'=>0]); $loc->update(['visible'=>$visible]); return response()->json(['visible'=>$visible]); }
 public function index(Request $r){ return response()->json(['visible'=>(bool)optional(UserLocation::where('user_id',$r->user()->id)->first())->visible,'people'=>$this->people($r)]); }
 private function people(Request $r){ $me=UserLocation::where('user_id',$r->user()->id)->first(); if(!$me||!$me->visible||($me->latitude==0&&$me->longitude==0)) return []; $candidates=UserLocation::with('user')->where('user_id','!=',$me->user_id)->where('visible',true)->where('last_seen','>=',now()->subMinutes(10))->get(); return $candidates->map(function($l)use($me){return ['id'=>$l->user_id,'name'=>$l->user->name,'picture'=>$l->user->avatar,'distance'=>round($this->haversine($me->latitude,$me->longitude,$l->latitude,$l->longitude),1),'online'=>$l->last_seen->gt(now()->subMinutes(2))];})->filter(fn($p)=>$p['distance']<=10)->sortBy('distance')->values()->take(50)->all(); }
}
