<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Colocation;
use App\Models\Expense;
use Illuminate\Http\Request;

class AdminController extends Controller
{
    public function index()
    {
        return view('admin.dashboard', [
            'usersCount' => User::count(),
            'colocationsCount' => Colocation::count(),
            'expensesCount' => Expense::count(),
            'users' => User::orderByDesc('created_at')->paginate(15),
        ]);
    }

    public function toggleBan(User $user)
{
    if ($user->id === auth()->id()) {
        return back()->withErrors(['error' => 'You cannot ban yourself.']);
    }

    $user->is_banned = ! (bool) $user->is_banned;
    $user->save();
    $user->refresh(); 

    return back()->with('success', $user->is_banned ? 'User banned.' : 'User unbanned.');
}
}
