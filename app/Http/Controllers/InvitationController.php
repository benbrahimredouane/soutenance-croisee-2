<?php

namespace App\Http\Controllers;

use App\Models\Colocation;
use App\Models\Invitation;
use App\Models\Membership;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;
use App\Mail\ColocationInvitationMail;

class InvitationController extends Controller
{
    // Show invite form (owner only)
    public function create(Colocation $colocation)
    {
        $this->ensureOwner($colocation);

        return view('invitations.create', compact('colocation'));
    }

    // Send invitation email (owner only)
    public function store(Request $request, Colocation $colocation)
    {
        $this->ensureOwner($colocation);

        $data = $request->validate([
            'email' => ['required', 'email', 'max:255'],
        ]);

        // generate a strong unique token
        $token = Str::random(32);

        $email = strtolower(trim($data['email']));

        $invitation = Invitation::create([
            'colocation_id' => $colocation->id,
            'email' => $email,
            'token' => $token,
        ]);

        // Send email containing token
        Mail::to($invitation->email)->send(new ColocationInvitationMail(
            colocationName: $colocation->name,
            token: $invitation->token
        ));

        return back()->with('success', 'Invitation sent successfully.');
    }

    // Join page where user pastes token
    public function joinForm()
    {
        return view('invitations.join');
    }

    public function join(Request $request)
    {
        $user = $request->user();

        $data = $request->validate([
            'token' => ['required', 'string'],
        ]);

        $invitation = Invitation::where('token', $data['token'])->first();

        if (! $invitation) {
            return back()->withErrors(['token' => 'Invalid token.']);
        }

      
        $invitedEmail = strtolower(trim($invitation->email));
        $userEmail    = strtolower(trim($user->email));

        if ($invitedEmail !== $userEmail) {
            return back()->withErrors(['token' => 'This token is not for your email address.']);
        }

        $colocation = $invitation->colocation;

        if (! $colocation || $colocation->status !== 'active') {
            return back()->withErrors(['token' => 'This colocation is not active.']);
        }

       
        $hasActiveMembership = $user->memberships()
            ->whereNull('left_at')
            ->whereHas('colocation', function ($q) {
                $q->where('status', 'active');
            })
            ->exists();

        if ($hasActiveMembership) {
            return back()->withErrors(['token' => 'You already have an active colocation.']);
        }

        DB::transaction(function () use ($user, $colocation, $invitation) {

            Membership::updateOrCreate(
                [
                    'user_id' => $user->id,
                    'colocation_id' => $colocation->id,
                ],
                [
                    'role' => 'member',
                    'left_at' => null,
                ]
            );

           
            $invitation->delete();
        });

        return redirect()->route('colocations.show', $colocation)
            ->with('success', 'You joined the colocation!');
    }

    private function ensureOwner(Colocation $colocation)
    {

        if ($colocation->owner_id !== auth()->id()) {
            abort(403);
        }
    }
}
