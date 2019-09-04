<?php
return [
    'id' => env('BILLMATE_ID'),
    'key' => env('BILLMATE_KEY'),
    'test' => env('BILLMATE_TEST', true),
    'url' => env('BILLMATE_URL', 'https://api.billmate.se'),
    'version' => env('BILLMATE_VERSION', '2.1.6'),
    'client' => env('BILLMATE_CLIENT', 'ttrig/laravel-billmate'),

    'route_prefix' => 'billmate',
    'accept_action' => 'BillmateController@accept',
    'cancel_action' => 'BillmateController@cancel',
    'callback_action' => \Ttrig\Billmate\Controllers\CallbackController::class,
];
