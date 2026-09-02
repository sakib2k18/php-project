<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Donation;
use App\Models\User;
use App\Services\ActivityLogger;
use App\Services\ImageUploadService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class UserController extends Controller
{
    public function __construct(
        protected ActivityLogger $activity,
        protected ImageUploadService $images,
    ) {}

    public function index(Request $request): View
    {
        $this->authorize('viewAny', User::class);

        $filters = $request->validate([
            'q' => ['nullable', 'string', 'max:120'],
            'role' => ['nullable', Rule::in([User::ROLE_ADMIN, User::ROLE_USER])],
            'state' => ['nullable', Rule::in(['active', 'inactive'])],
        ]);

        $users = User::query()
            ->search($filters['q'] ?? null)
            ->when($filters['role'] ?? null, fn ($q, $r) => $q->where('role', $r))
            ->when(($filters['state'] ?? null) === 'active', fn ($q) => $q->where('is_active', true))
            ->when(($filters['state'] ?? null) === 'inactive', fn ($q) => $q->where('is_active', false))
            ->withCount([
                'donations',
                'donations as approved_donations_count' => fn ($q) => $q->where('status', Donation::STATUS_APPROVED),
            ])
            ->withSum(['donations as approved_amount' => fn ($q) => $q->where('status', Donation::STATUS_APPROVED)], 'amount')
            ->latest()
            ->paginate(config('site.pagination.admin'))
            ->withQueryString();

        return view('admin.users.index', [
            'users' => $users,
            'filters' => $filters,
            'counts' => [
                'total' => User::query()->count(),
                'active' => User::query()->where('is_active', true)->count(),
                'inactive' => User::query()->where('is_active', false)->count(),
                'admins' => User::query()->admins()->count(),
            ],
        ]);
    }

    public function show(User $user): View
    {
        $this->authorize('view', $user);

        $summary = Donation::query()
            ->where('user_id', $user->id)
            ->selectRaw('status, COUNT(*) as c, COALESCE(SUM(amount),0) as total')
            ->groupBy('status')
            ->get()
            ->keyBy('status');

        return view('admin.users.show', [
            'user' => $user,
            'volunteer' => $user->volunteer,
            'donations' => $user->donations()->with('campaign:id,title,slug')->latest('donated_on')->limit(10)->get(),
            'stats' => [
                'count' => (int) $summary->sum('c'),
                'approved_amount' => (float) ($summary[Donation::STATUS_APPROVED]->total ?? 0),
                'pending_count' => (int) ($summary[Donation::STATUS_PENDING]->c ?? 0),
            ],
        ]);
    }

    /**
     * Activate / deactivate a supporter account.
     *
     * UserPolicy::toggleActive refuses when the target is the current user or
     * another administrator, so the sole administrator can never lock itself out.
     */
    public function toggleActive(User $user): RedirectResponse
    {
        $this->authorize('toggleActive', $user);

        $user->forceFill(['is_active' => ! $user->is_active])->save();

        $state = $user->is_active ? 'activated' : 'deactivated';
        $this->activity->log("user.{$state}", "Account for {$user->name} {$state}", $user);

        return back()->with('success', "Account {$state}.");
    }

    public function destroy(User $user): RedirectResponse
    {
        $this->authorize('delete', $user);

        // Belt and braces on top of the policy: never leave the site adminless.
        if ($user->isAdmin() && User::query()->admins()->count() <= 1) {
            return back()->with('error', 'The only administrator account cannot be deleted.');
        }

        $name = $user->name;

        // Donation records survive the account (user_id is set to null) so the
        // financial history stays complete and auditable.
        $this->images->delete($user->avatar);
        $user->delete();

        $this->activity->log('user.deleted', "Deleted the account of {$name}");

        return redirect()->route('admin.users.index')->with('success', "Account for {$name} deleted. Donation records were kept.");
    }
}
