<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Pagination Settings
    |--------------------------------------------------------------------------
    */
    
    'tracks_per_page' => env('TRACKS_PER_PAGE', 12),
    'purchases_per_page' => env('PURCHASES_PER_PAGE', 20),
    'dashboard_tracks_per_page' => env('DASHBOARD_TRACKS_PER_PAGE', 15),
    
    'allowed_per_page' => [12, 24, 48],
];
