<?php

return [
    'device_lock_enabled' => (bool) env('ATTENDANCE_DEVICE_LOCK_ENABLED', true),

    'geofence' => [
        'enabled' => (bool) env('ATTENDANCE_GEOFENCE_ENABLED', true),
        'latitude' => env('ATTENDANCE_OFFICE_LAT'),
        'longitude' => env('ATTENDANCE_OFFICE_LNG'),
        'radius_meters' => (int) env('ATTENDANCE_RADIUS_METERS', 200),
    ],
];

