<?php
namespace App\Http\Controllers;
use Illuminate\Http\Request;
class NotificationController extends Controller { public function index(Request $request) { return response()->json(['notifications'=>$request->user()->notifications()->latest()->take(30)->get(),'unread_count'=>$request->user()->unreadNotifications()->count()]); } public function read(Request $request, string $id) { $notification=$request->user()->notifications()->findOrFail($id); $notification->markAsRead(); return response()->json(['ok'=>true]); } public function readAll(Request $request) { $request->user()->unreadNotifications->markAsRead(); return response()->json(['ok'=>true]); } }
