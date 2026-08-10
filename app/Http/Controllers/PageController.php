<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;

class PageController extends Controller
{
    
public function dashboard()
    {
        return view('pages.dashboard');
    }

public function messages()
    {
        return view('pages.messages');
    }
    
public function profile()
    {
        return view('pages.profile');
    }
    
public function connections()
    {
        return view('pages.connections');
    }
    
    
public function classifieds()
    {
        return view('pages.classifieds');
    }
    
public function events()
    {
        return view('pages.events');
    }
    

public function media()
    {
        return view('pages.media');
    }
    
public function groups()
    {
        return view('pages.groups');
    }


public function landing()
    {
        return view('landing');
    }
    
public function terms()
    {
        return view('pages.terms');
    }







public function showChangePassword()
    {
        return view('pages.change-password');
    }

public function changePassword(Request $request)
    {
        $request->validate([
            'current_password'      => ['required'],
            'password'              => ['required', 'min:8', 'confirmed'],
        ]);

        $user = Auth::user();

        if (! Hash::check($request->current_password, $user->password)) {
            return back()->withErrors(['current_password' => 'Current password is incorrect (if unknown, logout & request a reset link).']);
        }

        $user->update(['password' => Hash::make($request->password)]);

        return back()->with('success', 'Password updated successfully.');
    }
}
