<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

use App\Models\User;
use App\Services\AuditLogService;

class AuthController extends Controller
{
    public function login(Request $request, AuditLogService $audit)
    {
        $user = User::where('pbu_email', $request->email)->first();
        if (!$user) {
            return redirect('/login')->with('failed', 'Error!, User not found.');
        }

        $credentials = [
            'pbu_email' => $request->email,
            'password'  => $request->password,
        ];
        
        if (auth()->attempt($credentials)) {
            $audit->log('User authorized Successfully!', $user, null, $user->getChanges());
            if (!auth()->user()->pbu_email_verified_at) {
                return redirect('/login')->with('failed', 'Error!, You are not authorized.');
            }else{
                return redirect('/dashboard')->with('success', 'You are authorized Successfully!');
            }
        } else {
            $audit->log('Invalid credentials', $user, null, $user->getChanges());
            return redirect('/login')->with('failed', 'Invalid credentials');
        }
    }
}
