<?php

namespace App\Http\Controllers;

use App\Http\Requests\ProfileUpdateRequest;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Redirect;
use Illuminate\View\View;

class ProfileController extends Controller
{
    /**
     * Display the user's profile form.
     */
    public function edit(Request $request): View
    {
        return view('profile.edit', [
            'user' => $request->user(),
        ]);
    }

    /**
     * Update the user's profile information.
     */
    public function update(ProfileUpdateRequest $request): RedirectResponse
    {
        $user = $request->user();
        $validated = $request->validated();

        // Handle avatar upload
        if ($request->hasFile('avatar')) {
            // Delete old avatar if exists
            if ($user->avatar && file_exists(public_path('legacy/images/avatars/' . $user->avatar))) {
                unlink(public_path('legacy/images/avatars/' . $user->avatar));
            }
            
            $avatar = $request->file('avatar');
            $avatarName = time() . '_' . $avatar->getClientOriginalName();
            $avatar->move(public_path('legacy/images/avatars'), $avatarName);
            $validated['avatar'] = $avatarName;
        } else {
            // Keep existing avatar if not uploading new one
            unset($validated['avatar']);
        }

        $user->fill($validated);

        $user->save();

        return Redirect::route('profile.edit')->with('status', 'profile-updated');
    }

}
