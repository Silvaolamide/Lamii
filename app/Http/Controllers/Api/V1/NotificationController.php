<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Controllers\NotificationController as WebNotificationController;
use Illuminate\Http\Request;

class NotificationController extends Controller
{
    public function index(Request $request)
    {
        return app(WebNotificationController::class)->index($request);
    }

    public function read(Request $request, string $id)
    {
        return app(WebNotificationController::class)->read($request, $id);
    }

    public function readAll(Request $request)
    {
        return app(WebNotificationController::class)->readAll($request);
    }
}
