<?php

return [
    'name' => 'ExtraSecurity',
    
    'ips_enabled'           => env('EXTRASECURITY_IPS_ENABLED', false),
    //'ips_user_role' 		=> env('EXTRASECURITY_IPS_USER_ROLE', ''),
    'ips' 					=> env('EXTRASECURITY_IPS', ''),

    'recaptcha_main_enabled' => env('EXTRASECURITY_RECAPTCHA_MAIN_ENABLED', false),
    'recaptcha_main_type' => env('EXTRASECURITY_RECAPTCHA_MAIN_TYPE', 'invisible'),
    'recaptcha_main_site_key' => env('EXTRASECURITY_RECAPTCHA_MAIN_SITE_KEY', ''),
    'recaptcha_main_secret_key' => env('EXTRASECURITY_RECAPTCHA_MAIN_SECRET_KEY', ''),
    
    'recaptcha_eup_enabled' => env('EXTRASECURITY_RECAPTCHA_EUP_ENABLED', false),
    'recaptcha_eup_type' => env('EXTRASECURITY_RECAPTCHA_EUP_TYPE', 'invisible'),
    'recaptcha_eup_site_key' => env('EXTRASECURITY_RECAPTCHA_EUP_SITE_KEY', ''),
    'recaptcha_eup_secret_key' => env('EXTRASECURITY_RECAPTCHA_EUP_SECRET_KEY', ''),

    'recaptcha_eup_submit_enabled' => env('EXTRASECURITY_RECAPTCHA_EUP_SUBMIT_ENABLED', false),
    'recaptcha_eup_submit_type' => env('EXTRASECURITY_RECAPTCHA_EUP_SUBMIT_TYPE', 'invisible'),
    'recaptcha_eup_submit_site_key' => env('EXTRASECURITY_RECAPTCHA_EUP_SUBMIT_SITE_KEY', ''),
    'recaptcha_eup_submit_secret_key' => env('EXTRASECURITY_RECAPTCHA_EUP_SUBMIT_SECRET_KEY', ''),
];
