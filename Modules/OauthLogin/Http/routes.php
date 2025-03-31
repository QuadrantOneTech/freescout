<?php

Route::group(['middleware' => 'web', 'prefix' => \Helper::getSubdirectory(), 'namespace' => 'Modules\OauthLogin\Http\Controllers'], function()
{
    Route::get('/oauth-login/callback/{provider_id}', ['uses' => 'OauthLoginController@callback', 'laroute' => true])->name('oauthlogin.callback');
    Route::get('/oauth-login/logout/{provider_id}/{logout_secret}', ['uses' => 'OauthLoginController@logout', 'laroute' => true])->name('oauthlogin.logout');
});

Route::group(['middleware' => [\App\Http\Middleware\EncryptCookies::class, \Illuminate\Cookie\Middleware\AddQueuedCookiesToResponse::class, \Illuminate\Session\Middleware\StartSession::class, \App\Http\Middleware\TokenAuth::class, \Illuminate\View\Middleware\ShareErrorsFromSession::class, /*\App\Http\Middleware\VerifyCsrfToken::class,*/ \Illuminate\Routing\Middleware\SubstituteBindings::class, \App\Http\Middleware\HttpsRedirect::class, \App\Http\Middleware\Localize::class, \App\Http\Middleware\LogoutIfDeleted::class, \App\Http\Middleware\FrameGuard::class, \App\Http\Middleware\CustomHandle::class,], 'prefix' => \Helper::getSubdirectory(), 'namespace' => 'Modules\OauthLogin\Http\Controllers'], function()
{
    Route::post('/oauth-login/logout/{provider_id}/{logout_secret}', ['uses' => 'OauthLoginController@logout']);
});
