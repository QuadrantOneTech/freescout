<form class="form-horizontal margin-top margin-bottom" method="POST" action="">
    {{ csrf_field() }}

	<div class="form-group{{ $errors->has('settings[oauthlogin.auto_create_users]') ? ' has-error' : '' }} margin-bottom-10">
        <label for="oauthlogin_auto_create_users" class="col-sm-2 control-label">{{ __('Auto-Create Users') }}</label>

        <div class="col-sm-6">
            <div class="controls">
                <div class="onoffswitch-wrap">
                    <div class="onoffswitch">
                        <input type="checkbox" name="settings[oauthlogin.auto_create_users]" value="1" id="oauthlogin_auto_create_users" class="onoffswitch-checkbox" @if (old('settings[oauthlogin.auto_create_users]', $settings['oauthlogin.auto_create_users'])) checked="checked"@endif >
                        <label class="onoffswitch-label" for="oauthlogin_auto_create_users"></label>
                    </div>
                </div>
            </div>
            <p class="form-help">
                {{ __('Automatically create non-existing users in FreeScout upon successful login to OAuth provider.') }}
            </p>
        </div>
    </div>

	<div class="form-group{{ $errors->has('settings[oauthlogin.force_oauth_login]') ? ' has-error' : '' }} margin-bottom-10">
        <label for="oauthlogin_force_oauthlogin_login" class="col-sm-2 control-label">{{ __('Force OAuth Login') }}</label>

        <div class="col-sm-6">
            <div class="controls">
                <div class="onoffswitch-wrap">
                    <div class="onoffswitch">
                        <input type="checkbox" name="settings[oauthlogin.force_oauth_login]" value="1" id="oauthlogin_force_oauthlogin_login" class="onoffswitch-checkbox" @if (old('settings[oauthlogin.force_oauth_login]', $settings['oauthlogin.force_oauth_login'])) checked="checked"@endif >
                        <label class="onoffswitch-label" for="oauthlogin_force_oauthlogin_login"></label>
                    </div>
                </div>
            </div>
            <p class="help-block">
                {{ __('Disable standard login form and require to login using default OAuth Provider.') }}
            </p>
        </div>
    </div>

    <div class="form-group{{ $errors->has('settings[oauthlogin.debug]') ? ' has-error' : '' }} margin-bottom-10">
        <label for="oauthlogin_debug" class="col-sm-2 control-label">{{ __('Debug Mode') }}</label>

        <div class="col-sm-6">
            <div class="controls">
                <div class="onoffswitch-wrap">
                    <div class="onoffswitch">
                        <input type="checkbox" name="settings[oauthlogin.debug]" value="1" id="oauthlogin_debug" class="onoffswitch-checkbox" @if (old('settings[oauthlogin.debug]', $settings['oauthlogin.debug'])) checked="checked"@endif >
                        <label class="onoffswitch-label" for="oauthlogin_debug"></label>
                    </div>
                </div>
            </div>
            <p class="help-block">
                {!! __('Enable debug mode to see more info in :%a_begin%Manage » Logs » OAuth:%a_end%.', ['%a_begin%' => '<a href="'.route('logs', ['name' => 'oauth']).'" target="_blank">', '%a_end%' => '</a>']) !!}
            </p>
        </div>
    </div>

    <h3 class="subheader">{{ __('OAuth Providers') }}</h3>

    <div id="ol-provider-pattern" class="hidden">
        <div class="form-group">
            <label class="col-sm-2 control-label">{{ __('Active') }}</label>

            <div class="col-sm-6">
                <label class="checkbox inline">
                    <input type="checkbox" name="settings[oauthlogin.providers][provider_index][active]" value="1" data-ol-no-clear="1" class="ol-provider-field-active">
                </label>
            </div>
        </div>

        <div class="form-group">
            <label class="col-sm-2 control-label">{{ __('Default') }}</label>

            <div class="col-sm-6">
                <label class="radio inline">
                    <input type="radio" name="settings[oauthlogin.providers][provider_index][default]" value="1" data-ol-no-clear="1" class="ol-provider-field-default">
                </label>
            </div>
        </div>

        <div class="form-group">
            <label class="col-sm-2 control-label">{{ __('Provider') }}</label>

            <div class="col-sm-6">
                <select name="settings[oauthlogin.providers][provider_index][provider]" class="form-control input-sized-lg ol-provider-field-provider" data-ol-no-clear="1" disabled="disabled">
                    <option value="{{ \OauthLogin::CUSTOM_PROVIDER }}">{{ __('Custom OAuth Provider') }}</option>
                    @foreach (\OauthLogin::$providers_config as $provider_code => $provider)
                        <option value="{{ $provider_code }}">{{ $provider['name'] }}</option>
                    @endforeach
                </select>
            </div>
        </div>

        <div class="form-group">
            <label class="col-sm-2 control-label">{{ __('Name') }}</label>

            <div class="col-sm-6">
                <input type="text" name="settings[oauthlogin.providers][provider_index][name]" class="form-control input-sized-lg ol-provider-field-name" value="" placeholder="{{ __('Used in Login button title') }} {{ __('(optional)') }}" disabled="disabled" />
            </div>
        </div>

        <div class="form-group">
            <label class="col-sm-2 control-label">{{ __('Redirect URI') }}</label>

            <div class="col-sm-6">
                <input type="hidden" name="settings[oauthlogin.providers][provider_index][id]" class="form-control input-sized-lg disabled ol-provider-field-id" value="" data-ol-no-clear="1" disabled="disabled" />
                <input type="text" class="form-control input-sized-lg disabled ol-provider-redirect-uri" value="" readonly="readonly" data-ol-no-clear="1" />
            </div>
        </div>

        <div class="form-group">
            <label class="col-sm-2 control-label">{{ __('Logout URI') }}</label>

            <div class="col-sm-6">
                <input type="text" class="form-control input-sized-lg disabled ol-provider-logout-uri" value="" readonly="readonly" data-ol-no-clear="1" data-ol-logout-secret="{{ \OauthLogin::getLogoutSecret() }}" />
            </div>
        </div>

        <div class="form-group">
            <label class="col-sm-2 control-label">{{ __('Client ID') }}</label>

            <div class="col-sm-6">
                <input type="text" name="settings[oauthlogin.providers][provider_index][client_id]" class="form-control input-sized-lg ol-provider-field-client_id" value="" required="required" disabled="disabled" />
            </div>
        </div>

        <div class="form-group">
            <label class="col-sm-2 control-label">{{ __('Client Secret') }}</label>

            <div class="col-sm-6">
                <input type="text" name="settings[oauthlogin.providers][provider_index][client_secret]" class="form-control input-sized-lg ol-provider-field-client_secret" value="" required="required" disabled="disabled"/>
            </div>
        </div>

        <div class="form-group">
            <label class="col-sm-2 control-label">{{ __('Authorization URL') }}</label>

            <div class="col-sm-6">
                <input type="url" name="settings[oauthlogin.providers][provider_index][auth_url]" class="form-control input-sized-lg ol-provider-field-auth_url" value="" required="required" pattern="^[^\{\}]+$" disabled="disabled"/>
            </div>
        </div>

        <div class="form-group">
            <label class="col-sm-2 control-label">{{ __('Token URL') }}</label>

            <div class="col-sm-6">
                <input type="url" name="settings[oauthlogin.providers][provider_index][token_url]" class="form-control input-sized-lg ol-provider-field-token_url" value="" required="required" pattern="^[^\{\}]+$" disabled="disabled"/>
            </div>
        </div>

        <div class="form-group">
            <label class="col-sm-2 control-label">{{ __('User Info URL') }}</label>

            <div class="col-sm-6">
                <input type="url" name="settings[oauthlogin.providers][provider_index][user_url]" class="form-control input-sized-lg ol-provider-field-user_url" value="" placeholder="{{ __('(optional)') }}" pattern="^[^\{\}]*$" disabled="disabled"/>
            </div>
        </div>

        <div class="form-group">
            <label class="col-sm-2 control-label">{{ __('User Info Method') }}</label>

            <div class="col-sm-6">
                <select name="settings[oauthlogin.providers][provider_index][user_method]" class="form-control input-sized-lg ol-provider-field-user_method" disabled="disabled">
                    <option value="POST" selected="selected" />POST</option>
                    <option value="GET" />GET</option>
                </select>
            </div>
        </div>

        <div class="form-group">
            <label class="col-sm-2 control-label">{{ __('Proxy URL') }}</label>

            <div class="col-sm-6">
                <input type="url" name="settings[oauthlogin.providers][provider_index][proxy]" class="form-control input-sized-lg ol-provider-field-proxy" value="" placeholder="{{ __('(optional)') }}" disabled="disabled" />
            </div>
        </div>

        <div class="form-group">
            <label class="col-sm-2 control-label">{{ __('Fields Mapping') }}</label>

            <div class="col-sm-6">
                <div class="flexy">
                    <input type="text" name="settings[oauthlogin.providers][provider_index][mapping]" class="form-control input-sized-lg ol-provider-field-mapping" value="" placeholder="{{ __('(optional)') }}" disabled="disabled" />
                    <i class="glyphicon glyphicon-info-sign icon-info" data-toggle="popover" data-trigger="hover" data-html="true" data-placement="left"  data-content="{{ __('Comma separated list of field mappings in the following format: oauth_user_field>>freescout_user_field. Available FreeScout user fields: name, email, photo, job_title, phone') }}"></i>
                </div>
            </div>
        </div>

        <div class="form-group">
            <label class="col-sm-2 control-label">{{ __('Scopes') }}</label>

            <div class="col-sm-6">
                <div class="flexy">
                    <input type="text" name="settings[oauthlogin.providers][provider_index][scopes]" class="form-control input-sized-lg ol-provider-field-scopes" value="" placeholder="{{ __('(optional)') }}" disabled="disabled" />
                </div>
            </div>
        </div>

        <div class="form-group margin-bottom-10">
            <div class="col-sm-6 col-sm-offset-2">
                <a href="#" class="btn btn-link text-danger ol-delete-provider padding-left-0" data-ol-provider-index="provider_index">{{ __('Delete') }}</a> 
                <a href="#" class="btn btn-link ol-add-provider">{{ __('Add Provider') }}</a>
            </div>
        </div>

        <hr class="margin-top-0"/>
    </div>

    <div class="form-group margin-top margin-bottom">
        <div class="col-sm-6 col-sm-offset-2">
            <button type="submit" class="btn btn-primary">
                {{ __('Save') }}
            </button>
        </div>
    </div>
</form>

@section('javascript')
    @parent
    var ol_providers = {!! json_encode($settings['oauthlogin.providers'] ?: []) !!};
    var ol_providers_config = {!! json_encode(\OauthLogin::$providers_config) !!};
    olInit();
@endsection