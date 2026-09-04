<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Controllers\DiscoverController as WebDiscoverController;
use Illuminate\Http\Request;

class DiscoverController extends Controller
{
    public function nearby(Request $request)
    {
        return app(WebDiscoverController::class)->nearby($request);
    }
}
