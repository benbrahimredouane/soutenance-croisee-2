<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class DashboardController extends Controller
{
    //
    public function index(){
       
        $user = auth()->user();

    $activeMembership = $user->memberships()
        ->whereNull('left_at')
        ->whereHas('colocation', function ($q) {
            $q->where('status', 'active');
        })
        ->with('colocation')
        ->first();

    return view('dashboard', compact('activeMembership'));
}
}
