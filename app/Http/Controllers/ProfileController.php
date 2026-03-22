<?php

namespace App\Http\Controllers;

use App\Http\Requests\ProfileUpdateRequest;
use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Redirect;
use Illuminate\Support\Facades\Storage;
use Inertia\Inertia;
use Inertia\Response;

class ProfileController extends Controller
{
    /**
     * Display the user's profile form.
     */
    public function edit(Request $request): Response
    {
        $user = $request->user();
        $user->load([
            'student.program.department',
            'student.block',
            'instructor.department',
            'programAsHead.department',
            'departmentAsProgramHead',
            'departmentAsDean.programs',
        ]);

        $student = $user->role === 'student' ? $user->student : null;
        $instructor = $user->role === 'instructor' ? $user->instructor : null;
        $program = $user->role === 'program_head' ? $user->programAsHead : null;
        $department = $user->role === 'dean' ? $user->departmentAsDean : null;

        return Inertia::render('Profile/Edit', [
            'mustVerifyEmail' => $request->user() instanceof MustVerifyEmail,
            'status' => session('status'),
            'account' => $user,
            'student' => $student,
            'instructor' => $instructor,
            'program' => $program,
            'department' => $department,
        ]);
    }

    /**
     * Update the user's profile information.
     */
    public function update(ProfileUpdateRequest $request): RedirectResponse
    {
        Gate::authorize('manage-accounts');

        $request->user()->fill($request->validated());

        if ($request->user()->isDirty('email')) {
            $request->user()->email_verified_at = null;
        }

        $request->user()->save();

        return Redirect::route('profile.edit')
            ->with('success', 'You have successfully updated your account!');
    }

    /**
     * Upload or replace the authenticated user's avatar.
     */
    public function uploadAvatar(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'avatar' => ['required', 'image', 'mimes:jpg,png', 'max:2048'],
        ]);

        $user = $request->user();
        $oldAvatar = $user->avatar;

        $path = Storage::disk('public')->put('avatars', $validated['avatar']);
        $user->avatar = $path;
        $user->save();

        if ($oldAvatar && Storage::disk('public')->exists($oldAvatar)) {
            Storage::disk('public')->delete($oldAvatar);
        }

        return Redirect::route('profile.edit')
            ->with('success', 'Profile picture updated successfully.');
    }

    /**
     * Archive the user's account.
     */
    public function destroy(Request $request): RedirectResponse
    {
        Gate::authorize('manage-accounts');

        $request->validate([
            'password' => ['required', 'current_password'],
        ]);

        $user = $request->user();

        Auth::logout();

        $user->archive();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return Redirect::to('/');
    }
}
