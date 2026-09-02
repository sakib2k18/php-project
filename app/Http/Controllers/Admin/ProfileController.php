<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\ProfileUpdateRequest;
use App\Models\ActivityLog;
use App\Services\ActivityLogger;
use App\Services\ImageUploadService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ProfileController extends Controller
{
    public function __construct(
        protected ImageUploadService $images,
        protected ActivityLogger $activity,
    ) {}

    public function edit(Request $request): View
    {
        return view('admin.profile.edit', [
            'user' => $request->user(),
            'recentActivity' => ActivityLog::query()
                ->where('user_id', $request->user()->id)
                ->latest()
                ->limit(10)
                ->get(),
        ]);
    }

    public function update(ProfileUpdateRequest $request): RedirectResponse
    {
        $user = $request->user();

        $user->fill($request->safe()->only(['name', 'email', 'phone', 'student_id', 'address', 'bio']));

        if ($request->hasFile('avatar')) {
            $user->avatar = $this->images->store($request->file('avatar'), 'avatars', $user->avatar);
        }

        $user->save();

        $this->activity->log('admin.profile_updated', 'Updated the administrator profile');

        return redirect()->route('admin.profile.edit')->with('success', 'Your profile has been updated.');
    }

    public function editPassword(): View
    {
        return view('admin.profile.password');
    }
}
