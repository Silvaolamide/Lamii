<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class DiscoverController extends Controller
{
    public function index(Request $request)
    {
        return view('discover.index', ['user' => $request->user()]);
    }
}
