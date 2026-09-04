<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Controllers\SafetyController as WebSafetyController;
use App\Models\User;
use Illuminate\Http\Request;

class SafetyController extends Controller
{
    public function block(Request $request, User $user) { return app(WebSafetyController::class)->block($request, $user); }
    public function unblock(Request $request, User $user) { return app(WebSafetyController::class)->unblock($request, $user); }
    public function report(Request $request, User $user) { return app(WebSafetyController::class)->report($request, $user); }
}
