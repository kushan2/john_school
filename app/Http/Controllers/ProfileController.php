<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;

class ProfileController extends Controller
{
    public function edit()
    {
        return view('pages.profile', [
            'user'     => Auth::user(),
            'campuses' => config('campuses.list'),
        ]);
    }

    public function update(Request $request)
    {
        $user = Auth::user();

        $validated = $request->validate([
            'name'   => ['required', 'string', 'max:255'],
            'campus' => ['required', 'string', Rule::in(config('campuses.list'))],
            'major'  => ['nullable', 'string', 'max:120'],
            'bio'    => ['nullable', 'string', 'max:1000'],
            'avatar' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:4096'],
        ]);

        $data = [
            'name'   => $validated['name'],
            'campus' => $validated['campus'],
            'major'  => $validated['major'] ?? null,
            'bio'    => $validated['bio'] ?? null,
        ];

        if ($request->hasFile('avatar')) {
            // Remove the previous file so we don't orphan uploads.
            if ($user->avatar) {
                Storage::disk('public')->delete($user->avatar);
            }
            $data['avatar'] = $request->file('avatar')->store('avatars', 'public');
        }

        $user->update($data);

        return back()->with('success', 'Profile updated.');
    }

    public function destroy(Request $request)
    {
        $request->validate([
            'password' => ['required'],
        ]);

        $user = Auth::user();

        if (! password_verify($request->password, $user->password)) {
            return back()->withErrors(['password' => 'Password is incorrect.']);
        }

        if ($user->avatar) {
            Storage::disk('public')->delete($user->avatar);
        }

        Auth::logout();
        $user->delete();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('login')->with('success', 'Your account has been deleted.');
    }
}
