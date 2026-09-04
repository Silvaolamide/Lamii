<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Controllers\LocationController as WebLocationController;
use Illuminate\Http\Request;

class LocationController extends Controller
{
    public function update(Request $request)
    {
        return app(WebLocationController::class)->update($request);
    }

    public function destroy(Request $request)
    {
        return app(WebLocationController::class)->destroy($request);
    }
}
