<?php

Route::group(['middleware' => 'web', 'prefix' => \Helper::getSubdirectory(), 'namespace' => 'Modules\ExtraSecurity\Http\Controllers'], function()
{
    Route::get('/app-settings/extrasecurity/get-ip-address', ['uses' => 'ExtraSecurityController@getIp', 'middleware' => ['auth', 'roles'], 'roles' => ['user', 'admin']])->name('extrasecurity.get_ip');
});
