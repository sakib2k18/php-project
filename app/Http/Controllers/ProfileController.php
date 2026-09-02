<?php

namespace App\Http\Controllers;

use App\Http\Requests\ProfileUpdateRequest;
use App\Models\Donation;
use App\Services\ImageUploadService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class ProfileController extends Controller
{
    public function __construct(protected ImageUploadService $images) {}

    public function edit(Request $request): View
    {
        $user = $request->user();

        $summary = Donation::query()
            ->where('user_id', $user->id)
            ->selectRaw('status, COUNT(*) as c, COALESCE(SUM(amount),0) as total')
            ->groupBy('status')
            ->get()
            ->keyBy('status');

        return view('dashboard.profile', [
            'user' => $user,
            'volunteer' => $user->volunteer,
            'stats' => [
                'donations' => (int) $summary->sum('c'),
                'approved_amount' => (float) ($summary[Donation::STATUS_APPROVED]->total ?? 0),
                'pending_count' => (int) ($summary[Donation::STATUS_PENDING]->c ?? 0),
            ],
        ]);
    }

    public function update(ProfileUpdateRequest $request): RedirectResponse
    {
        $user = $request->user();

        $user->fill($request->safe()->only(['name', 'email', 'phone', 'student_id', 'address', 'bio']));

        // Changing the email address invalidates a previous verification.
        if ($user->isDirty('email')) {
            $user->email_verified_at = null;
        }

        if ($request->hasFile('avatar')) {
            $user->avatar = $this->images->store($request->file('avatar'), 'avatars', $user->avatar);
        }

        $user->save();

        return redirect()->route('profile.edit')->with('success', 'Your profile has been updated.');
    }

    public function removeAvatar(Request $request): RedirectResponse
    {
        $user = $request->user();

        $this->images->delete($user->avatar);
        $user->forceFill(['avatar' => null])->save();

        return redirect()->route('profile.edit')->with('success', 'Profile photo removed.');
    }

    /**
     * Let a supporter close their own account. The administrator account is
     * excluded so the organisation can never be left without an administrator.
     */
    public function destroy(Request $request): RedirectResponse
    {
        $user = $request->user();

        if ($user->isAdmin()) {
            return back()->with('error', 'The administrator account cannot be deleted.');
        }

        $request->validate([
            'password' => ['required', 'current_password'],
        ]);

        Auth::logout();

        $this->images->delete($user->avatar);
        $user->delete();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('home')->with('success', 'Your account has been closed. Thank you for your support.');
    }
}
