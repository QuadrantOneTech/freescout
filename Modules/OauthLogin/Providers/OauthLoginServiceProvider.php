<?php

namespace Modules\OauthLogin\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Database\Eloquent\Factory;

define('OL_MODULE', 'oauthlogin');

class OauthLoginServiceProvider extends ServiceProvider
{
    const USER_AGENT = 'Mozilla/5.0 (Windows NT 6.2; WOW64; rv:17.0) Gecko/20100101 Firefox/17.0';

    const CUSTOM_PROVIDER = 'oauth';

    const DEFAULT_SCOPES = 'openid email profile';

    // https://github.com/laravel/socialite
    // https://auth0.com/docs/authenticate/protocols/oauth
    public static $providers_config = [
        'google' => [
            'name'      => 'Google',
            'auth_url'  => 'https://accounts.google.com/o/oauth2/auth',
            'token_url' => 'https://www.googleapis.com/oauth2/v4/token',
            'user_url'  => 'https://www.googleapis.com/oauth2/v3/userinfo', // requested via POST by default
            'mapping'   => 'email>>email,name>>name,avatar>>photo',
        ],
        'azure' => [
            'name'      => 'Microsoft Azure',
            // https://learn.microsoft.com/en-us/entra/identity-platform/v2-oauth2-auth-code-flow
            'auth_url'  => 'https://login.microsoftonline.com/{tenant-id}/oauth2/v2.0/authorize',
            'token_url' => 'https://login.microsoftonline.com/{tenant-id}/oauth2/v2.0/token',
            'user_url'  => 'https://graph.microsoft.com/oidc/userinfo',
            'mapping'   => 'userPrincipalName>>email,displayName>>name',
        ],
        'github' => [
            'name'      => 'GitHub',
            'auth_url'  => 'https://github.com/login/oauth/authorize',
            'token_url' => 'https://github.com/login/oauth/access_token',
            'user_url'  => 'https://api.github.com/user',
            'mapping'   => 'email>>email,login>>name,avatar_url>>photo',
            'scopes'    => 'openid email profile user:email',
            'user_method' => 'GET',
        ],
        'auth0' => [
            'name'      => 'Auth0.com',
            'auth_url'  => 'https://{subdomain}.auth0.com/authorize',
            'token_url' => 'https://{subdomain}.auth0.com/oauth/token',
            'user_url'  => 'https://{subdomain}.auth0.com/userinfo',
            'mapping'   => 'email>>email,name>>name,picture>>photo',
        ],
        'nextcloud' => [
            'name'      => 'Nextcloud',
            'auth_url'  => 'https://{domain}/apps/oauth2/authorize',
            'token_url' => 'https://{domain}/apps/oauth2/api/v1/token',
            'user_url'  => 'https://{domain}/ocs/v2.php/cloud/user?format=json',
            'user_method' => 'GET',
            'mapping'   => 'email>>email,displayname>>name',
        ],
    ];

    public static $mappable_fields = [
        'name',
        'email',
        'photo',
        'job_title',
        'phone',
    ];

    /**
     * Indicates if loading of the provider is deferred.
     *
     * @var bool
     */
    protected $defer = false;

    /**
     * Boot the application events.
     *
     * @return void
     */
    public function boot()
    {
        $this->registerConfig();
        $this->registerViews();
        $this->registerFactories();
        $this->loadMigrationsFrom(__DIR__ . '/../Database/Migrations');
        $this->hooks();
    }

    /**
     * Module hooks.
     */
    public function hooks()
    {
        // Add module's JS file to the application layout.
        \Eventy::addFilter('javascripts', function($javascripts) {
            $javascripts[] = \Module::getPublicPath(OL_MODULE).'/js/laroute.js';
            $javascripts[] = \Module::getPublicPath(OL_MODULE).'/js/module.js';

            return $javascripts;
        });

        \Eventy::addFilter('settings.sections', function ($sections) {
            $sections['oauth'] = ['title' => __('OAuth'), 'icon' => 'user', 'order' => 250];

            return $sections;
        }, 15);

        // Section settings
        \Eventy::addFilter('settings.section_settings', function ($settings, $section) {

            if ($section != 'oauth') {
                return $settings;
            }

            $settings = self::getSettings();

            return $settings;
        }, 20, 2);

        // Section parameters.
        \Eventy::addFilter('settings.section_params', function ($params, $section) {
            if ($section != 'oauth') {
                return $params;
            }

            $params = [
                'template_vars' => [
                    
                ],
                'settings' => [
                    'oauthlogin.auto_create_users' => [
                        'env' => 'OAUTHLOGIN_AUTO_CREATE_USERS',
                    ],
                    'oauthlogin.force_oauth_login' => [
                        'env' => 'OAUTHLOGIN_FORCE_OAUTH_LOGIN',
                    ],
                    'oauthlogin.debug' => [
                        'env' => 'OAUTHLOGIN_DEBUG',
                    ],
                    'oauthlogin.providers' => [
                        
                    ],
                ]
            ];

            return $params;
        }, 20, 2);

        // Show OAuth login buttons.
        \Eventy::addAction('login_form.after',  function () {
            $settings = self::getSettings();
            foreach ($settings['oauthlogin.providers'] ?? [] as $i => $provider) {
                if (empty($provider['active'])) {
                    continue;
                }
                $provider_name = $provider['name']
                    ?? self::getProvidersConfig()[$provider['provider']]['name']
                    ?? 'OAuth';
                echo \View::make('oauthlogin::login_button', [
                    'provider_index' => $i,
                    'provider_name' => $provider_name,
                ])->render();
            }
        });

        // Settings view name
        \Eventy::addFilter('settings.view', function ($view, $section) {
            if ($section != 'oauth') {
                return $view;
            } else {
                return 'oauthlogin::settings';
            }
        }, 20, 2);

        \Eventy::addFilter('middleware.web.custom_handle.response', function ($prev, $request, $next) {
            
            $settings = self::getSettings();
            $providers = $settings['oauthlogin.providers'] ?? [];
            $main_provider = self::getMainProvider($providers);
            $chosen_provider = null;
            $chosen_provider_index = -1;

            if (!empty($request->oauth_provider) && !empty($providers[(int)$request->oauth_provider])) {
                $chosen_provider = $providers[(int)$request->oauth_provider];
                $chosen_provider_index = (int)$request->oauth_provider;
            }

            $route_name = $request->route()->getName();

            if ($route_name == 'login'
                && (($main_provider['provider'] && !empty($main_provider['provider']['active'])) || $chosen_provider)
                && !\Auth::check()
                && (($settings['oauthlogin.force_oauth_login'] && (int)$request->get('oauth', -1) != 0) || (int)$request->get('oauth'))
            ) {
                // Merge provider data with default settings.
                $provider = null;
                $provider_index = -1;
                if ($chosen_provider) {
                    $provider = $chosen_provider;
                    $provider_index = $chosen_provider_index;
                } else {
                    $provider = $main_provider['provider'];
                    $provider_index = $main_provider['index'];
                }

                $provider = self::addConfigToProvider($provider);

                $auth_url = self::oauthGetAuthUrl($provider, $provider_index);
                
                self::debug('Redirecting to Authorization URL: '.$auth_url);
                return redirect($auth_url);
            }

            // if ($main_provider && !empty($main_provider['active']) && $route_name == 'logout') {
            //     return redirect($settings['oauthlogin.logout_url']);
            // }

            return $prev;
        }, 10, 3);

        // \Eventy::addFilter('settings.before_save', function ($request, $section, $settings) {
        //     if ($section != 'oauth') {
        //         return $request;
        //     } 
        //     if ($request->has('settings') && !empty($request->settings['oauthlogin.providers'])) {
        //         $new_settings = $request->settings;

        //         $new_settings['oauthlogin.providers'] = json_encode($new_settings['oauthlogin.providers']);

        //         $request->merge(['settings' => array_merge($request->settings ?? [], $new_settings)]);
        //     }
        //     return $request;
        // }, 20, 3);
        
        \Eventy::addFilter('oauthlogin.get_user_data', function ($user_data, $provider, $ch, $curl_opt) {

            switch ($provider['provider']) {
                case 'github':
                    // Get user email.
                    $curl_opt[CURLOPT_URL] = 'https://api.github.com/user/emails';

                    curl_setopt_array($ch, $curl_opt);

                    $email_response = curl_exec($ch);
                    \OauthLogin::debug('User email response: '.$email_response);
                    $user_emails = json_decode($email_response, true);

                    if (!is_array($user_emails) || empty($user_emails)) {
                        return $user_data;
                    }
                    foreach ($user_emails as $email) {
                        if (!is_array($email)) {
                            continue;
                        }
                        if ($email['primary'] && $email['verified']) {
                            $user_data['email'] = $email['email'];
                            return $user_data;
                        }
                    }
                    break;
                case 'nextcloud':
                	if (is_array($user_data) && array_key_exists('ocs', $user_data)) {
                    	if (is_array($user_data['ocs']) && array_key_exists('data', $user_data['ocs'])) {
                        	\OauthLogin::debug('Nextcloud data: ' . json_encode($user_data['ocs']['data']));
                			return $user_data['ocs']['data'];
                        }
                    }
                	
                    break;
            }

            return $user_data;
        }, 20, 4);
    }

    public static function addConfigToProvider($provider)
    {
        $config = self::getProvidersConfig();
        if (!empty($config[$provider['provider']])) {
            $provider = array_merge($config[$provider['provider']], $provider);
        }
        return $provider;
    }

    public static function getSettings()
    {
        $settings = [];

        $settings['oauthlogin.auto_create_users'] = config('oauthlogin.auto_create_users');
        $settings['oauthlogin.force_oauth_login'] = config('oauthlogin.force_oauth_login');
        $settings['oauthlogin.debug'] = config('oauthlogin.debug');
        $settings['oauthlogin.providers'] = \Option::get('oauthlogin.providers') ?: [];

        return $settings;
    }


    public static function getProviders()
    {
        return \Option::get('oauthlogin.providers') ?: [];
    }

    public static function getProviderById($id)
    {
        $providers = self::getProviders();
        foreach ($providers as $provider) {
            if ($provider['id'] == $id) {
                return $provider;
            }
        }

        return null;
    }

    public static function getMainProvider($providers = [])
    {
        $result = [
            'provider' => null,
            'index' => null,
        ];

        if (!is_array($providers)) {
            return $result;
        }

        foreach ($providers as $i => $provider) {
            if (!empty($provider['default'])) {
                $result['provider'] = $provider;
                $result['index'] = $i;
            }
        }

        return $result;
    }

    public static function getProvidersConfig()
    {
        return \Eventy::filter('oauthlogin.providers_config', self::$providers_config);
    }

    public static function getProviderConfig($provider)
    {
        $providers_config = self::getProvidersConfig();

        return $providers_config[$provider] ?? [];
    }

    public static function parseFieldsMapping($mapping)
    {
        $mapping_array = [];

        $mapping_parts = preg_split('/,/', $mapping);
        foreach ($mapping_parts as $mapping_line) {
            $mapping = explode('>>', $mapping_line);
            if (count($mapping) != 2) {
                continue;
            }
            $oauth_field = trim($mapping[0]);
            $fs_field = trim($mapping[1]);

            if (!in_array($fs_field, self::getMappableFields())) {
                continue;
            }
            $mapping_array[$oauth_field] = $fs_field;
        }

        return array_flip($mapping_array);
    }

    public static function getMappableFields()
    {
        return self::$mappable_fields;
    }

    public static function sanitizeOauthFieldName($field_name)
    {
        return preg_replace("#.*/([^/]+)$#", '$1', $field_name);
    }

    public static function debug($text, $params = [])
    {
        if (!config('oauthlogin.debug')) {
            return;
        }
        self::log('DEBUG - '. $text, $params);
    }

    public static function log($text, $params = [])
    {
        if ($params) {
            $text .= ' '.json_encode($params);
        }
        \Helper::log('oauth', stripslashes(str_replace("\n", '\n', $text)));
    }

    public static function oauthGetAuthUrl($provider, $index)
    {
        $connector = '?';
        if (strstr($provider['auth_url'], '?')) {
            $connector = '&';
        }

        $scope = $provider['scopes'] ?? '';
        if (!$scope) {
            $scope = self::DEFAULT_SCOPES;
        }

        return $provider['auth_url'].$connector
            .'client_id='.$provider['client_id']
            .'&response_type=code'
            .'&scope='.urlencode($scope)
            .'&state='.md5(config('app.key').$provider['client_id'])
            .'&redirect_uri='.urlencode(route('oauthlogin.callback', ['provider' => $provider['id']]));
    }
    
    public static function getLogoutSecret()
    {
        return substr(encrypt('logout_url'), 0, 8);
    }

    // public static function oauthGetLogoutUrl(string $redirectUri)
    // {
    //     return $this->getBaseUrl()
    //         .'/oauth2/logout?'
    //         .http_build_query(['post_logout_redirect_uri' => $redirectUri], '', '&', $this->encodingType);
    // }

    /**
     * Register the service provider.
     *
     * @return void
     */
    public function register()
    {
        $this->registerTranslations();
    }

    /**
     * Register config.
     *
     * @return void
     */
    protected function registerConfig()
    {
        $this->publishes([
            __DIR__.'/../Config/config.php' => config_path('oauthlogin.php'),
        ], 'config');
        $this->mergeConfigFrom(
            __DIR__.'/../Config/config.php', 'oauthlogin'
        );
    }

    /**
     * Register views.
     *
     * @return void
     */
    public function registerViews()
    {
        $viewPath = resource_path('views/modules/oauthlogin');

        $sourcePath = __DIR__.'/../Resources/views';

        $this->publishes([
            $sourcePath => $viewPath
        ],'views');

        $this->loadViewsFrom(array_merge(array_map(function ($path) {
            return $path . '/modules/oauthlogin';
        }, \Config::get('view.paths')), [$sourcePath]), 'oauthlogin');
    }

    /**
     * Register translations.
     *
     * @return void
     */
    public function registerTranslations()
    {
        $this->loadJsonTranslationsFrom(__DIR__ .'/../Resources/lang');
    }

    /**
     * Register an additional directory of factories.
     * @source https://github.com/sebastiaanluca/laravel-resource-flow/blob/develop/src/Modules/ModuleServiceProvider.php#L66
     */
    public function registerFactories()
    {
        if (! app()->environment('production')) {
            app(Factory::class)->load(__DIR__ . '/../Database/factories');
        }
    }

    /**
     * Get the services provided by the provider.
     *
     * @return array
     */
    public function provides()
    {
        return [];
    }
}
