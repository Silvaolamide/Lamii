<?php
namespace App\Http\Controllers;
use App\Events\MessageSent;
use App\Models\Block;
use App\Models\Connection;
use App\Models\Conversation;
use App\Models\Message;
use App\Models\User;
use App\Notifications\NewMessageNotification;
use Illuminate\Http\Request;
class ChatController extends Controller {
 public function index(Request $request){$user=$request->user();$conversations=Conversation::with(['userOne','userTwo'])->where('user_one_id',$user->id)->orWhere('user_two_id',$user->id)->orderByDesc('last_message_at')->get();return view('chat.index',compact('conversations','user'));}
 public function start(Request $request,User $user){abort_if($user->id===$request->user()->id,422);$me=$request->user();abort_unless($this->connected($me->id,$user->id)&&!$this->blocked($me->id,$user->id),403,'You can only chat with accepted connections.');[$one,$two]=collect([$me->id,$user->id])->sort()->values()->all();$conversation=Conversation::firstOrCreate(['user_one_id'=>$one,'user_two_id'=>$two]);return redirect()->route('chat.show',$conversation);}
 public function show(Request $request,Conversation $conversation){$this->authorizeConversation($request,$conversation);$conversation->load(['userOne','userTwo']);Message::where('conversation_id',$conversation->id)->where('sender_id','!=',$request->user()->id)->whereNull('read_at')->update(['read_at'=>now()]);$messages=$conversation->messages()->with('sender')->oldest()->paginate(50);return view('chat.show',compact('conversation','messages'));}
 public function messages(Request $request,Conversation $conversation){$this->authorizeConversation($request,$conversation);$after=(int)$request->integer('after',0);$query=$conversation->messages()->with('sender')->oldest();if($after>0)$query->where('id','>',$after);$messages=$query->limit(100)->get();return response()->json(['messages'=>$messages->map(fn($m)=>['id'=>$m->id,'sender_id'=>$m->sender_id,'sender_name'=>$m->sender->name,'body'=>$m->body,'created_at'=>$m->created_at->toIso8601String()])->values()]);}
 public function store(Request $request,Conversation $conversation){$this->authorizeConversation($request,$conversation);$data=$request->validate(['body'=>['required','string','max:2000']]);$message=$conversation->messages()->create(['sender_id'=>$request->user()->id,'body'=>trim($data['body'])]);$conversation->update(['last_message_at'=>now()]);$message->load('sender');$recipientId=$message->sender_id===$conversation->user_one_id?$conversation->user_two_id:$conversation->user_one_id;User::find($recipientId)?->notify(new NewMessageNotification($message));broadcast(new MessageSent($message))->toOthers();return response()->json(['message'=>['id'=>$message->id,'sender_id'=>$message->sender_id,'body'=>$message->body,'created_at'=>$message->created_at->toIso8601String()]]);}
 private function authorizeConversation(Request $request,Conversation $conversation): void{abort_unless(in_array($request->user()->id,[$conversation->user_one_id,$conversation->user_two_id],true),403);abort_unless($this->connected($conversation->user_one_id,$conversation->user_two_id)&&!$this->blocked($conversation->user_one_id,$conversation->user_two_id),403);}
 private function connected(int $a,int $b): bool{return Connection::where('status',Connection::ACCEPTED)->where(fn($q)=>$q->where(fn($q)=>$q->where('sender_id',$a)->where('recipient_id',$b))->orWhere(fn($q)=>$q->where('sender_id',$b)->where('recipient_id',$a)))->exists();}
 private function blocked(int $a,int $b): bool{return Block::where(fn($q)=>$q->where('blocker_id',$a)->where('blocked_id',$b))->orWhere(fn($q)=>$q->where('blocker_id',$b)->where('blocked_id',$a))->exists();}
}
