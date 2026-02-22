<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Multi Location Mode
    |--------------------------------------------------------------------------
    |
    | If false, stock movement form uses one default location automatically
    | and hides location management from navigation.
    |
    */
    'multi_location' => env('STOCK_MULTI_LOCATION', false),

    /*
    |--------------------------------------------------------------------------
    | Default Stock Location Code
    |--------------------------------------------------------------------------
    |
    | Used when multi_location is disabled. If this code does not exist,
    | system falls back to first active stock location.
    |
    */
    'default_location_code' => env('STOCK_DEFAULT_LOCATION_CODE', 'GUDANG'),

    /*
    |--------------------------------------------------------------------------
    | Reminder Popup Settings
    |--------------------------------------------------------------------------
    |
    | Cooldown avoids repeated popup spam when user refreshes page.
    | Preview limit controls how many low/out items are listed in popup body.
    |
    */
    'reminder_popup_cooldown_minutes' => (int) env('STOCK_REMINDER_POPUP_COOLDOWN_MINUTES', 10),
    'reminder_popup_preview_limit' => (int) env('STOCK_REMINDER_POPUP_PREVIEW_LIMIT', 5),
];
