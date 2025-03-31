<?php

return [
    'name' => 'OauthLogin',
    'auto_create_users' => env('OAUTHLOGIN_AUTO_CREATE_USERS', true),
    'force_oauth_login' => env('OAUTHLOGIN_FORCE_OAUTH_LOGIN', false),
    'debug' => env('OAUTHLOGIN_DEBUG', false),
];
