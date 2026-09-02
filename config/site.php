<?php

/*
|--------------------------------------------------------------------------
| KUET TRY — Site configuration
|--------------------------------------------------------------------------
|
| Every value here is a *fallback*. At runtime the values are merged with the
| `organization_settings` table (see App\Services\SiteSettings) so the admin
| can change the organisation identity without touching a single file.
|
*/

return [

    'organization' => [
        'name' => env('SITE_ORG_NAME', 'KUET TRY'),
        'short_name' => env('SITE_ORG_SHORT_NAME', 'KUET TRY'),
        'tagline' => 'Together, We Can Make a Difference.',
        'legal_name' => 'KUET TRY — Humanitarian Organization',
        'founded_year' => 2016,
    ],

    'contact' => [
        'email' => 'info@kuettry.org',
        'phone' => '+880 1700-000000',
        'emergency_contact' => '+880 1800-000000',
        'address' => 'Khulna University of Engineering & Technology, Fulbarigate, Khulna 9203, Bangladesh',
        'office_hours' => 'Sunday – Thursday, 9:00 AM – 5:00 PM',
    ],

    'social' => [
        'facebook' => 'https://facebook.com/',
        'instagram' => 'https://instagram.com/',
        'youtube' => 'https://youtube.com/',
        'linkedin' => null,
        'twitter' => null,
    ],

    'currency' => [
        'code' => env('SITE_CURRENCY_CODE', 'BDT'),
        'symbol' => env('SITE_CURRENCY_SYMBOL', 'Tk'),
        'decimals' => 0,
    ],

    'location' => [
        'latitude' => (float) env('SITE_LATITUDE', 22.899310),
        'longitude' => (float) env('SITE_LONGITUDE', 89.502289),
        'city' => 'Khulna',
        'country' => 'Bangladesh',
    ],

    /*
    |----------------------------------------------------------------------
    | Domain vocabularies — single source of truth for enums used by
    | migrations, validation rules, filters and Blade badges.
    |----------------------------------------------------------------------
    */

    'campaign_categories' => [
        'poverty_relief' => 'Poverty Relief',
        'natural_disaster' => 'Natural Disaster',
        'education' => 'Education',
        'medical' => 'Medical',
        'food_distribution' => 'Food Distribution',
        'emergency_relief' => 'Emergency Relief',
        'winter_support' => 'Winter Support',
        'orphan_support' => 'Orphan Support',
        'other' => 'Other',
    ],

    'campaign_statuses' => [
        'draft' => 'Draft',
        'active' => 'Active',
        'completed' => 'Completed',
        'cancelled' => 'Cancelled',
    ],

    'project_statuses' => [
        'planned' => 'Planned',
        'ongoing' => 'Ongoing',
        'completed' => 'Completed',
    ],

    'event_statuses' => [
        'draft' => 'Draft',
        'published' => 'Published',
        'cancelled' => 'Cancelled',
    ],

    'post_statuses' => [
        'draft' => 'Draft',
        'published' => 'Published',
    ],

    'donation_methods' => [
        'bank_transfer' => 'Bank Transfer',
        'mobile_banking' => 'Mobile Banking',
        'cash' => 'Cash',
        'other' => 'Other',
    ],

    'donation_statuses' => [
        'pending' => 'Pending',
        'approved' => 'Approved',
        'rejected' => 'Rejected',
    ],

    'volunteer_statuses' => [
        'pending' => 'Pending',
        'approved' => 'Approved',
        'rejected' => 'Rejected',
    ],

    'announcement_priorities' => [
        'low' => 'Low',
        'normal' => 'Normal',
        'high' => 'High',
        'urgent' => 'Urgent',
    ],

    'gallery_categories' => [
        'relief' => 'Relief Distribution',
        'education' => 'Education',
        'medical' => 'Medical Camp',
        'events' => 'Events',
        'community' => 'Community',
        'team' => 'Our Team',
    ],

    'post_categories' => [
        'news' => 'News',
        'field_report' => 'Field Report',
        'announcement' => 'Announcement',
        'story' => 'Story',
    ],

    'volunteer_availability' => [
        'weekdays' => 'Weekdays',
        'weekends' => 'Weekends',
        'evenings' => 'Evenings',
        'flexible' => 'Flexible',
    ],

    /*
    |----------------------------------------------------------------------
    | Uploads
    |----------------------------------------------------------------------
    */

    'uploads' => [
        'disk' => 'public',
        'max_kb' => 2048,
        'mimes' => ['jpg', 'jpeg', 'png', 'webp'],
        'mime_types' => ['image/jpeg', 'image/png', 'image/webp'],
        'max_dimension' => 4000,
    ],

    'pagination' => [
        'public' => 9,
        'admin' => 10,
        'news' => 10,
    ],
];
