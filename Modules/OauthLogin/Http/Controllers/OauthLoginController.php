<?php

namespace Modules\OauthLogin\Http\Controllers;

use App\User;
use App\Email;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Routing\Controller;

class OauthLoginController extends Controller
{
    public function callback(Request $request)
    {
        \OauthLogin::debug('OAuth callback triggered: '.json_encode($request->all()));

        $provider = \OauthLogin::getProviderById($request->provider_id);

        if (empty($provider)) {
            $msg = 'Invalid OAuth Provider ID: '.$request->provider_id;
            \OauthLogin::log($msg);
            \Helper::addFloatingFlash(__('Authentication error: :error', ['error' => $msg]));
            return redirect()->route('login');
        }

        $provider = \OauthLogin::addConfigToProvider($provider);

        if (empty($provider['active'])) {
            $msg = 'OAuth Provider is not active: '.$provider['provider'];
            \OauthLogin::log($msg);
            \Helper::addFloatingFlash(__('Authentication error: :error', ['error' => $msg]));
            return redirect()->route('login');
        }

        $ch = curl_init($provider['token_url']);

        curl_setopt_array($ch, [
            CURLOPT_RETURNTRANSFER => 1,
            //CURLOPT_TIMEOUT => 40,
            CURLOPT_POST => 1,
            CURLOPT_POSTFIELDS => http_build_query([
                'client_id'     => $provider['client_id'],
                'client_secret' => $provider['client_secret'],
                'grant_type'    => 'authorization_code',
                'code'          => $request->get('code'),
                'redirect_uri'  => route('oauthlogin.callback', ['provider_id' => $request->provider_id]),
            ]),
            CURLINFO_HEADER_OUT => true,
            CURLOPT_HTTPHEADER => [
                'Content-Type: application/x-www-form-urlencoded; charset=utf-8',
                'Accept: application/json',
                'User-Agent: '.\OauthLogin::USER_AGENT,
            ],
        ]);
        \Helper::setCurlDefaultOptions($ch);
        if (!empty($provider['proxy'])) {
            curl_setopt($ch, CURLOPT_PROXY, $provider['proxy']);
        }

        $response = curl_exec($ch);

        \OauthLogin::debug('OAuth token URL response: '.$response);

        $data = json_decode($response, true);

        $access_token = $data['access_token'] ?? '';

        if (empty($access_token)) {
            $msg = __('Could not obtain Access Token from OAuth Provider');
            \OauthLogin::log($msg);
            \Helper::addFloatingFlash(__('Authentication error: :error', ['error' => $msg]));
            return redirect()->route('login');
        }

        //$provider_config = \OauthLogin::getProviderConfig($provider['provider']);
        $fields_mapping = \OauthLogin::parseFieldsMapping($provider['mapping'] ?? '');
        \OauthLogin::debug('Fields mapping: '.json_encode($fields_mapping));
        
        if (!empty($provider['user_url'])) {
            \OauthLogin::debug('Requesting data from User Info URL: '.$provider['user_url']);

            $user_curl_opt = [
                CURLOPT_URL => $provider['user_url'],
                //CURLOPT_TIMEOUT => 40,
                CURLOPT_POST => (!empty($provider['user_method']) && $provider['user_method'] == 'GET') ? 0 : 1,
                CURLOPT_HTTPHEADER => [
                    'Accept: application/json',
                    'Authorization: Bearer ' . $access_token,
                    'User-Agent: '.\OauthLogin::USER_AGENT,
                ],
            ];

            curl_setopt_array($ch, $user_curl_opt);

            \Helper::setCurlDefaultOptions($ch);
            if (!empty($provider['proxy'])) {
                curl_setopt($ch, CURLOPT_PROXY, $provider['proxy']);
            }
            
            $user_response = curl_exec($ch);
            \OauthLogin::debug('User Info response: '.$user_response);
            $user_data = json_decode($user_response, true);

            $user_data = \Eventy::filter('oauthlogin.get_user_data', $user_data, $provider, $ch, $user_curl_opt);
        } else {
            // https://github.com/freescout-helpdesk/freescout/issues/3782
            if (!empty($data['id_token']) && !self::getUserDataEmail($data, $fields_mapping)) {
                $parts = explode(".", $data['id_token']);
                $id_token = json_decode($parts[1], true);
                $jwt_payload = base64_decode($parts[1]);

                $user_data = json_decode($jwt_payload, true);

                if (!self::getUserDataEmail($user_data, $fields_mapping)) {
                    $user_data = $data;
                }
            } else {
                $user_data = $data;
            }
        }

        if (!is_array($user_data) || empty($user_data)) {
            $msg = __('Error occurred obtaining user data');
            \OauthLogin::log($msg);
            \Helper::addFloatingFlash($msg);
            return redirect()->route('login');
        }

        $user_email = self::getUserDataEmail($user_data, $fields_mapping);

        // https://github.com/freescout-helpdesk/freescout/issues/3504
        // https://github.com/freescout-help-desk/freescout/issues/4301
        $user_email = Email::sanitizeEmail($user_email).'';

        $user = User::where('email', $user_email)->first();

        if ($user && !$user->isDeleted()) {
            \OauthLogin::debug('FreeScout user found by email: '.$user_email);
            \Auth::login($user);
            return redirect($request->session()->get('url.intended', '/'));
        } else {
            \OauthLogin::debug('FreeScout user not found by email: '.$user_email);
            if (!config('oauthlogin.auto_create_users')) {
                $msg = __('User not found: :email', ['email' => $user_email]);
                \Helper::addFloatingFlash(__('Authentication error: :error', ['error' => $msg]));
                return redirect()->route('login');
            } else {
                // Create a new user.
                \OauthLogin::log("Creating a new user", ['email' => $user_email]);
                if (!empty($fields_mapping['name'])) {
                    $name = $user_data[$fields_mapping['name']] 
                        ?? $user_data['name'] 
                        ?? $user_data['nickname'] 
                        ?? ucfirst($provider['provider']).' User';
                } else {
                    $name = $user_data['name'] ?? ucfirst($provider['provider']).' User';
                }
                $name_parts = explode(' ', $name, 2);
                $first_name = trim($name_parts[0]);
                $last_name = 'User';
                if (!empty($name_parts[1]) && trim($name_parts[1])) {
                    $last_name = trim($name_parts[1]);
                }

                $new_user_data = [
                    'email' => $user_email,
                    // Set special dummy password in order to be able to determine
                    // that the user has not set the password by himself.
                    //'password' => User::generateRandomPassword(),
                    'password' => User::getDummyPassword(),
                    'no_password_hashing' => true,
                    'first_name' => $first_name,
                    'last_name' => $last_name,
                ];

                // Map fields.
                if (!empty($fields_mapping)) {
                    foreach ($user_data as $user_field_name => $user_field_value) {
                        foreach ($fields_mapping as $mapping_from => $mapping_to) {
                            if (strtolower($user_field_name) == $mapping_to) {
                                if (!empty($user_field_value) && is_string($user_field_value)) {
                                    $new_user_data[$mapping_from] = $user_field_value;
                                }
                                break;
                            }
                        }
                    }
                }

                $new_user_data = \Eventy::filter('oauthlogin.user_data', $new_user_data, $user_data);
                \OauthLogin::debug("User data after mapping:", array('new_user_data' => $new_user_data));

                $user = User::create($new_user_data);

                if ($user) {
                    // Save photo.
                    if (!empty($new_user_data['photo'])) {
                        $photo_path = \Helper::downloadRemoteFileAsTmp($new_user_data['photo']);

                        if ($photo_path) {
                            $photo_file = $user->savePhoto($photo_path, \File::mimeType($photo_path));
                            $user->photo_url = $photo_file;
                            $user->save();
                        } else {
                            \OauthLogin::debug("Could not download remote user photo: ".$new_user_data['photo']);
                        }
                    }
                    
                    \Auth::login($user);
                    return redirect($request->session()->get('url.intended', '/'));
                } else {
                    \OauthLogin::log("Error occurred creating a user.", ['new_user_data' => $new_user_data]);
                    \Helper::addFloatingFlash(__('Error occurred creating a user.'));
                    return redirect()->route('login');
                }
            }
        }
    }

    public static function getUserDataEmail($user_data, $fields_mapping)
    {
        return $user_data[$fields_mapping['email'] ?? 'dummy']
            ?? $user_data['email'] 
            ?? $user_data['mail']
            ?? '';
    }

    public function logout(Request $request, $provider_id, $logout_secret)
    {
        $provider = \OauthLogin::getProviderById($provider_id);

        if (empty($provider)) {
            \Helper::denyAccess();
        }

        if (empty($logout_secret) || $logout_secret != \OauthLogin::getLogoutSecret()) {
            \Helper::denyAccess();
        }

        if (auth()->user()) {
            // Logout.
            \Auth::logout();
        }
        return \Redirect::route('login');
    }
}
