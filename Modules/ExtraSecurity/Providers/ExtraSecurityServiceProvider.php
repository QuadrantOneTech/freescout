<?php

namespace Modules\ExtraSecurity\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Database\Eloquent\Factory;

class ExtraSecurityServiceProvider extends ServiceProvider
{
    const LOG_NAME = 'security';

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
        \Eventy::addFilter('settings.sections', function ($sections) {
            $sections['extrasecurity'] = ['title' => __('Security'), 'icon' => 'lock', 'order' => 250];

            return $sections;
        }, 12);

        // Section settings
        \Eventy::addFilter('settings.section_settings', function ($settings, $section) {

            if ($section != 'extrasecurity') {
                return $settings;
            }

            $settings = self::getSettings();

            return $settings;
        }, 20, 2);

        // Section parameters.
        \Eventy::addFilter('settings.section_params', function ($params, $section) {
            if ($section != 'extrasecurity') {
                return $params;
            }

            $params = [
                'template_vars' => [
                    
                ],
                'settings' => [
                    'extrasecurity.ips_enabled' => [
                        'env' => 'EXTRASECURITY_IPS_ENABLED',
                    ],
                    // 'extrasecurity.ips_user_role' => [
                    //     'env' => 'EXTRASECURITY_IPS_USER_ROLE',
                    // ],
                    'extrasecurity.ips' => [
                        'env' => 'EXTRASECURITY_IPS',
                        'env_encode' => true,
                    ],

                    'extrasecurity.recaptcha_main_enabled' => [
                        'env' => 'EXTRASECURITY_RECAPTCHA_MAIN_ENABLED',
                    ],
                    'extrasecurity.recaptcha_main_type' => [
                        'env' => 'EXTRASECURITY_RECAPTCHA_MAIN_TYPE',
                    ],
                    'extrasecurity.recaptcha_main_site_key' => [
                        'env' => 'EXTRASECURITY_RECAPTCHA_MAIN_SITE_KEY',
                    ],
                    'extrasecurity.recaptcha_main_secret_key' => [
                        'env' => 'EXTRASECURITY_RECAPTCHA_MAIN_SECRET_KEY',
                    ],

                    'extrasecurity.recaptcha_eup_enabled' => [
                        'env' => 'EXTRASECURITY_RECAPTCHA_EUP_ENABLED',
                    ],
                    'extrasecurity.recaptcha_eup_type' => [
                        'env' => 'EXTRASECURITY_RECAPTCHA_EUP_TYPE',
                    ],
                    'extrasecurity.recaptcha_eup_site_key' => [
                        'env' => 'EXTRASECURITY_RECAPTCHA_EUP_SITE_KEY',
                    ],
                    'extrasecurity.recaptcha_eup_secret_key' => [
                        'env' => 'EXTRASECURITY_RECAPTCHA_EUP_SECRET_KEY',
                    ],

                    'extrasecurity.recaptcha_eup_submit_enabled' => [
                        'env' => 'EXTRASECURITY_RECAPTCHA_EUP_SUBMIT_ENABLED',
                    ],
                    'extrasecurity.recaptcha_eup_submit_type' => [
                        'env' => 'EXTRASECURITY_RECAPTCHA_EUP_SUBMIT_TYPE',
                    ],
                    'extrasecurity.recaptcha_eup_submit_site_key' => [
                        'env' => 'EXTRASECURITY_RECAPTCHA_EUP_SUBMIT_SITE_KEY',
                    ],
                    'extrasecurity.recaptcha_eup_submit_secret_key' => [
                        'env' => 'EXTRASECURITY_RECAPTCHA_EUP_SUBMIT_SECRET_KEY',
                    ],
                ]
            ];

            return $params;
        }, 20, 2);

        // Settings view name
        \Eventy::addFilter('settings.view', function ($view, $section) {
            if ($section != 'extrasecurity') {
                return $view;
            } else {
                return 'extrasecurity::settings';
            }
        }, 20, 2);

        // Before saving settings.
        \Eventy::addFilter('settings.before_save', function($request, $section, $settings) {
            if ($section != 'extrasecurity') {
                return $request;
            }

            $new_settings = [];

            // IP restriction.
            if (!empty($request->settings['extrasecurity.ips_enabled']) && 
                (empty($request->settings['extrasecurity.ips']) || !preg_replace("#[^\w]#", '', $request->settings['extrasecurity.ips']))
            ) {
                $new_settings['extrasecurity.ips_enabled'] = false;
            } else if (!empty($request->settings['extrasecurity.ips_enabled'])) {
                $ips = self::parseIpList($request->settings['extrasecurity.ips']);
                $current_ip = \Helper::getClientIP();
                if (!in_array($current_ip, $ips)) {
                    $new_settings['extrasecurity.ips'] = $current_ip."\r\n".$request->settings['extrasecurity.ips'];
                }
            }

            if (!empty($request->settings['extrasecurity.recaptcha_main_enabled']) &&
                (empty($request->settings['extrasecurity.recaptcha_main_site_key'])
                || empty($request->settings['extrasecurity.recaptcha_main_secret_key']))
            ) {
                $new_settings['extrasecurity.recaptcha_main_enabled'] = false;
            }

            if (!empty($request->settings['extrasecurity.recaptcha_eup_enabled']) &&
                (empty($request->settings['extrasecurity.recaptcha_eup_site_key'])
                || empty($request->settings['extrasecurity.recaptcha_eup_secret_key']))
            ) {
                $new_settings['extrasecurity.recaptcha_eup_enabled'] = false;
            }

            if (!empty($request->settings['extrasecurity.recaptcha_eup_submit_enabled']) &&
                (empty($request->settings['extrasecurity.recaptcha_eup_submit_site_key'])
                || empty($request->settings['extrasecurity.recaptcha_eup_submit_secret_key']))
            ) {
                $new_settings['extrasecurity.recaptcha_eup_submit_enabled'] = false;
            }

            $request->merge(['settings' => array_merge($request->settings ?? [], $new_settings)]);

            return $request;
        }, 20, 3);

        \Eventy::addAction('login_form.before_submit',  function () {
            if (config('extrasecurity.recaptcha_main_enabled')) {
                echo \View::make('extrasecurity::recaptcha_main', [
                    'site_key' => config('extrasecurity.recaptcha_main_site_key'),
                    'type' => config('extrasecurity.recaptcha_main_type'),
                ])->render();
            }
        });

        \Eventy::addAction('login_form.submit_class',  function() {
            if (config('extrasecurity.recaptcha_main_enabled')
                && config('extrasecurity.recaptcha_main_type') == 'invisible'
            ) {
                echo ' es-submit-btn';
            }
        });

        // \Eventy::addAction('login_form.submit_attrs',  function() {
        //     if (config('extrasecurity.recaptcha_main_enabled')
        //         && config('extrasecurity.recaptcha_main_type') == 'invisible'
        //     ) {
        //         echo ' data-sitekey="'.htmlspecialchars(config('extrasecurity.recaptcha_main_site_key')).'" data-callback="esReacaptchaCallback"';
        //     }
        // });

        // EUP Login Form.
        \Eventy::addAction('enduserportal.login.submit_class',  function() {
            if (config('extrasecurity.recaptcha_eup_enabled')
                && config('extrasecurity.recaptcha_eup_type') == 'invisible'
            ) {
                //echo ' g-recaptcha';
                echo ' es-submit-btn';
            }
        });

        // \Eventy::addAction('enduserportal.login.submit_attrs',  function() {
        //     if (config('extrasecurity.recaptcha_eup_enabled')
        //         && config('extrasecurity.recaptcha_eup_type') == 'invisible'
        //     ) {
        //         echo ' data-sitekey="'.htmlspecialchars(config('extrasecurity.recaptcha_eup_site_key')).'" data-callback="esReacaptchaCallback"';
        //     }
        // });

        \Eventy::addAction('enduserportal.login.before_submit',  function () {
            if (config('extrasecurity.recaptcha_eup_enabled')) {
                echo \View::make('extrasecurity::recaptcha_eup', [
                    'site_key' => config('extrasecurity.recaptcha_eup_site_key'),
                    'type' => config('extrasecurity.recaptcha_eup_type'),
                ])->render();
            }
        });

        // EUP "Submit a Ticket" Form.
        // https://developers.google.com/recaptcha/docs/invisible
        // \Eventy::addAction('enduserportal.submit_form.submit_class',  function() {
        //     if (config('extrasecurity.recaptcha_eup_submit_enabled')
        //         && config('extrasecurity.recaptcha_eup_submit_type') == 'invisible'
        //         && !\Helper::isCurrentRoute('enduserportal.widget_form')
        //     ) {
        //         echo ' g-recaptcha';
        //     }
        // });

        // \Eventy::addAction('enduserportal.submit_form.submit_attrs',  function() {
        //     if (config('extrasecurity.recaptcha_eup_submit_enabled')
        //         && config('extrasecurity.recaptcha_eup_submit_type') == 'invisible'
        //         && !\Helper::isCurrentRoute('enduserportal.widget_form')
        //     ) {
        //         echo ' data-sitekey="'.htmlspecialchars(config('extrasecurity.recaptcha_eup_submit_site_key')).'" data-callback="esReacaptchaCallback"';
        //     }
        // });

        \Eventy::addAction('enduserportal.submit_form.before_submit',  function () {
            if (config('extrasecurity.recaptcha_eup_submit_enabled')
                && !\Helper::isCurrentRoute('enduserportal.widget_form')
            ) {
                echo \View::make('extrasecurity::recaptcha_eup_submit', [
                    'site_key' => config('extrasecurity.recaptcha_eup_submit_site_key'),
                    'type' => config('extrasecurity.recaptcha_eup_submit_type'),
                ])->render();
            }
        });

        // IP restriction.
        \Eventy::addAction('middleware.web.custom_handle', function($request) {
            $actions = $request->route()->getAction();

            if (!empty($actions['middleware']) 
                && is_array($actions['middleware'])
                && (in_array('auth', $actions['middleware']) || in_array('roles', $actions['middleware']))
            ) {
                self::checkIp();
            }
            if (\Helper::isCurrentRoute('login')) {
                self::checkIp();
            }
        });

        \Eventy::addAction('auth_middleware.handle', function($request) {
            self::checkIp();
        });

        // Validate capatcha in main login form.
        \Eventy::addFilter('login.custom_check',  function($errors, $request) {
            if (config('extrasecurity.recaptcha_main_enabled') && empty($request->input('2fa_code'))) {
                $result = self::validateRecaptcha($request, config('extrasecurity.recaptcha_main_secret_key'));
                if ($result === 0) {
                    return [
                        'extrasecurity.recaptha' => __('Invalid captcha')
                    ];
                } elseif ($result === -1) {
                    return [
                        'extrasecurity.recaptha' => __('Captcha validation error – contact Administrator')
                    ];
                }
            }

            return $errors;
        }, 20, 2);

        // Validate captcha in End-User Portal login form.
        \Eventy::addFilter('enduserportal.login.custom_check',  function($errors, $request) {
            if (config('extrasecurity.recaptcha_eup_enabled')) {
                $result = self::validateRecaptcha($request, config('extrasecurity.recaptcha_eup_secret_key'));

                if ($result === 0) {
                    return [
                        'extrasecurity.recaptha' => __('Invalid captcha')
                    ];
                } elseif ($result === -1) {
                    return [
                        'extrasecurity.recaptha' => __('Captcha validation error – contact Administrator')
                    ];
                }
            }

            return $errors;
        }, 20, 2);

        // Validate captcha in End-User Portal "Submit a Ticket" form.
        \Eventy::addFilter('enduserportal.submit_form.custom_check',  function($errors, $request) {
            if (config('extrasecurity.recaptcha_eup_submit_enabled')) {
                $result = self::validateRecaptcha($request, config('extrasecurity.recaptcha_eup_submit_secret_key'));

                if ($result === 0) {
                    return [
                        'extrasecurity.recaptha' => __('Invalid captcha')
                    ];
                } elseif ($result === -1) {
                    return [
                        'extrasecurity.recaptha' => __('Captcha validation error – contact Administrator')
                    ];
                }
            }

            return $errors;
        }, 20, 2);

        // Validate captcha in End-User Portal when replying to a ticket.
        \Eventy::addFilter('enduserportal.submit_reply.custom_check',  function($errors, $request) {
            if (config('extrasecurity.recaptcha_eup_submit_enabled')) {
                $result = self::validateRecaptcha($request, config('extrasecurity.recaptcha_eup_submit_secret_key'));

                if ($result === 0) {
                    return [
                        'extrasecurity.recaptha' => __('Invalid captcha')
                    ];
                } elseif ($result === -1) {
                    return [
                        'extrasecurity.recaptha' => __('Captcha validation error – contact Administrator')
                    ];
                }
            }

            return $errors;
        }, 20, 2);

        \Eventy::addFilter('csp.script_src', function ($value) {
            return $value.' recaptcha.net/recaptcha/api.js';
        });
    }

    public static function parseIpList($str)
    {
        return array_unique(preg_split("/\r\n|\n|\r/", $str));
    }

    public static function isIpInRange($ip, $range) {
        if (strpos( $range, '/') === false ) {
            $range .= '/32';
        }
        // $range is in IP/CIDR format eg 127.0.0.1/24
        list( $range, $netmask ) = explode( '/', $range, 2 );
        $range_decimal = ip2long( $range );
        $ip_decimal = ip2long( $ip );
        $wildcard_decimal = pow( 2, ( 32 - $netmask ) ) - 1;
        $netmask_decimal = ~ $wildcard_decimal;

        return ( ( $ip_decimal & $netmask_decimal ) == ( $range_decimal & $netmask_decimal ) );
    }

    public static function checkIp()
    {
        if (!config('extrasecurity.ips_enabled')) {
            return;
        }

        $ip = \Helper::getClientIP();
        $allowed_ips = self::parseIpList(base64_decode(config('extrasecurity.ips') ?? ''));

        // Check IP ranges.
        foreach ($allowed_ips as $allowed_ip) {
            if (strstr($allowed_ip, '/') && self::isIpInRange($ip, $allowed_ip)) {
                return;
            }
        }

        if (!in_array($ip, $allowed_ips)) {
            \Helper::log(self::LOG_NAME, 'Access denied for IP: '.$ip);
            abort(403, __('Your IP address is not allowed: :ip', ['ip' => $ip]).'[display]');
        }
    }

    public static function validateRecaptcha($request, $secret)
    {
        $url = 'https://www.google.com/recaptcha/api/siteverify?'. http_build_query([
            'secret' => $secret,
            'response' => $request->get('g-recaptcha-response'),
            'remoteip' => \Helper::getClientIp(),
        ]);
        $status_code = 0;
        try {
            $ch = curl_init();
            curl_setopt($ch, CURLOPT_URL, $url);
            curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            \Helper::setCurlDefaultOptions($ch);

            $json = curl_exec($ch);
            $status_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);

            curl_close($ch);

            $json_decoded = json_decode($json, true);

            if (!empty($json_decoded['success'])) {
                return 1;
            } else {
                if ($status_code == 200 && isset($json_decoded['success'])) {
                    return 0;
                } else {
                    // Captcha validation error.
                    \Helper::log(self::LOG_NAME, 'reCAPTCHA validation error (HTTP Status Code: '.$status_code.'): '.json_encode($json_decoded));
                    return -1;
                }
            }

        } catch (\Exception $e) {
            \Helper::log(self::LOG_NAME, 'reCAPTCHA validation error (HTTP Status Code: '.$status_code.'): '.$e->getMessage());
            // Captcha validation error.
            return -1;
        }
    }

    public static function getSettings()
    {
        $settings = [];

        $settings['extrasecurity.ips_enabled'] = config('extrasecurity.ips_enabled');
        //$settings['extrasecurity.ips_user_role'] = config('extrasecurity.ips_user_role');
        $settings['extrasecurity.ips'] = base64_decode(config('extrasecurity.ips') ?? '');

        $settings['extrasecurity.recaptcha_main_enabled'] = config('extrasecurity.recaptcha_main_enabled');
        $settings['extrasecurity.recaptcha_main_type'] = config('extrasecurity.recaptcha_main_type');
        $settings['extrasecurity.recaptcha_main_site_key'] = config('extrasecurity.recaptcha_main_site_key');
        $settings['extrasecurity.recaptcha_main_secret_key'] = config('extrasecurity.recaptcha_main_secret_key');

        $settings['extrasecurity.recaptcha_eup_enabled'] = config('extrasecurity.recaptcha_eup_enabled');
        $settings['extrasecurity.recaptcha_eup_type'] = config('extrasecurity.recaptcha_eup_type');
        $settings['extrasecurity.recaptcha_eup_site_key'] = config('extrasecurity.recaptcha_eup_site_key');
        $settings['extrasecurity.recaptcha_eup_secret_key'] = config('extrasecurity.recaptcha_eup_secret_key');

        $settings['extrasecurity.recaptcha_eup_submit_enabled'] = config('extrasecurity.recaptcha_eup_submit_enabled');
        $settings['extrasecurity.recaptcha_eup_submit_type'] = config('extrasecurity.recaptcha_eup_submit_type');
        $settings['extrasecurity.recaptcha_eup_submit_site_key'] = config('extrasecurity.recaptcha_eup_submit_site_key');
        $settings['extrasecurity.recaptcha_eup_submit_secret_key'] = config('extrasecurity.recaptcha_eup_submit_secret_key');

        return $settings;
    }

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
            __DIR__.'/../Config/config.php' => config_path('extrasecurity.php'),
        ], 'config');
        $this->mergeConfigFrom(
            __DIR__.'/../Config/config.php', 'extrasecurity'
        );
    }

    /**
     * Register views.
     *
     * @return void
     */
    public function registerViews()
    {
        $viewPath = resource_path('views/modules/extrasecurity');

        $sourcePath = __DIR__.'/../Resources/views';

        $this->publishes([
            $sourcePath => $viewPath
        ],'views');

        $this->loadViewsFrom(array_merge(array_map(function ($path) {
            return $path . '/modules/extrasecurity';
        }, \Config::get('view.paths')), [$sourcePath]), 'extrasecurity');
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
