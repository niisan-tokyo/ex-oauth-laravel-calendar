<?php
return [
    'scopes' => [
        'profile',
        'email',
        'https://www.googleapis.com/auth/calendar.events',
    ],
    'redirect' => env('GOOGLE_CALENDAR_OAUTH_REDIRECT_URL', ''),
    'client_id' => env('GOOGLE_CLIENT_ID', ''),
    'client_secret' => env('GOOGLE_CLIENT_SECRET', ''),
    'events' => [
        'token_refreshed' => ''
    ],
    'holiday_id' => 'japanese__ja@holiday.calendar.google.com'
];