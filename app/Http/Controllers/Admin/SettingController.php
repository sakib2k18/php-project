<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\OrganizationSettingsRequest;
use App\Models\OrganizationSetting;
use App\Services\ActivityLogger;
use App\Services\ImageUploadService;
use App\Services\SiteSettings;
use App\Services\WeatherService;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class SettingController extends Controller
{
    public function __construct(
        protected SiteSettings $settings,
        protected ImageUploadService $images,
        protected ActivityLogger $activity,
        protected WeatherService $weather,
    ) {}

    public function edit(): View
    {
        return view('admin.settings.index', [
            'groups' => OrganizationSetting::query()
                ->orderBy('id')
                ->get()
                ->groupBy('group'),
            'values' => $this->settings->all(),
            'apis' => [
                'weather' => config('apis.weather'),
                'map' => config('apis.map'),
            ],
        ]);
    }

    public function update(OrganizationSettingsRequest $request): RedirectResponse
    {
        $values = $request->safe()->except(['logo', 'favicon']);

        if ($request->hasFile('logo')) {
            $values['logo'] = $this->images->store($request->file('logo'), 'branding', $this->settings->get('logo'));
        }

        if ($request->hasFile('favicon')) {
            $values['favicon'] = $this->images->store($request->file('favicon'), 'branding', $this->settings->get('favicon'));
        }

        $this->settings->put($values);

        // Coordinates may have moved — drop the cached weather for the old spot.
        $this->weather->forget();

        $this->activity->log('settings.updated', 'Updated the organisation settings');

        return redirect()->route('admin.settings.edit')->with('success', 'Organisation settings saved.');
    }
}
