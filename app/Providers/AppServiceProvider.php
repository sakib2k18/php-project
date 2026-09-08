<?php

namespace App\Providers;

use App\Models\Announcement;
use App\Models\Campaign;
use App\Models\Donation;
use App\Models\Event;
use App\Models\GalleryItem;
use App\Models\Project;
use App\Models\SuccessStory;
use App\Models\TeamMember;
use App\Models\User;
use App\Models\Volunteer;
use App\Policies\ContentPolicy;
use App\Policies\DonationPolicy;
use App\Policies\UserPolicy;
use App\Policies\VolunteerPolicy;
use App\Services\SiteSettings;
use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Http\Request;
use Illuminate\Pagination\Paginator;
use Illuminate\Support\Facades\Blade;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\Facades\View;
use Illuminate\Support\ServiceProvider;
use Illuminate\Validation\Rules\Password;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Editorial models all share the same "admin manages, public reads" rule.
     *
     * @var array<int, class-string>
     */
    protected array $contentModels = [
        Campaign::class,
        Project::class,
        Event::class,
        SuccessStory::class,
        Announcement::class,
        GalleryItem::class,
        TeamMember::class,
    ];

    public function register(): void
    {
        // One settings instance per request keeps the settings query to a single
        // cached lookup no matter how many Blade partials read from it.
        $this->app->singleton(SiteSettings::class);
    }

    public function boot(): void
    {
        $this->configureAuthorization();
        $this->configureValidation();
        $this->configureRateLimiting();
        $this->configureViews();
        $this->configureUrls();
    }

    protected function configureAuthorization(): void
    {
        Gate::policy(Donation::class, DonationPolicy::class);
        Gate::policy(Volunteer::class, VolunteerPolicy::class);
        Gate::policy(User::class, UserPolicy::class);

        foreach ($this->contentModels as $model) {
            Gate::policy($model, ContentPolicy::class);
        }

        // Convenience gate used by Blade (`@can('access-admin')`) and routes.
        Gate::define('access-admin', fn (User $user) => $user->isAdmin());
    }

    protected function configureValidation(): void
    {
        Password::defaults(function () {
            return Password::min(8)->letters()->numbers();
        });
    }

    protected function configureRateLimiting(): void
    {
        /*
         * A loose per-IP flood guard only. The precise "five failed attempts
         * per email address" lockout lives in Auth\LoginRequest, which counts
         * failures rather than requests and clears the counter on success —
         * so a visitor who mistypes twice and then signs in is not punished.
         */
        RateLimiter::for('login', fn (Request $request) => Limit::perMinute(20)->by($request->ip()));

        RateLimiter::for('contact', fn (Request $request) => Limit::perHour(5)->by($request->ip()));

        RateLimiter::for('donations', fn (Request $request) => Limit::perHour(15)->by($request->user()?->id ?: $request->ip()));

        RateLimiter::for('weather', fn (Request $request) => Limit::perMinute(12)->by($request->ip()));
    }

    protected function configureViews(): void
    {
        Paginator::defaultView('components.pagination.default');
        Paginator::defaultSimpleView('components.pagination.simple');

        // `$site` is available in every template without a controller passing it.
        View::composer('*', function ($view) {
            $view->with('site', $this->app->make(SiteSettings::class));
        });

        // @money(1250) → "Tk 1,250"
        Blade::directive('money', fn ($expression) => "<?php echo e(money({$expression})); ?>");
    }

    protected function configureUrls(): void
    {
        // Behind Apache/XAMPP the app may live under a sub-directory served over
        // http; force the scheme only when the deployment is genuinely https.
        if (str_starts_with((string) config('app.url'), 'https://')) {
            URL::forceScheme('https');
        }
    }
}
