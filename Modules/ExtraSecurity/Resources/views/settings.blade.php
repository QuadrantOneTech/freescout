<form class="form-horizontal margin-top margin-bottom" method="POST" action="">
    {{ csrf_field() }}

    <div class="form-group">
        <label class="col-sm-2 control-label"></label>

        <div class="col-sm-6">
            <p class="form-help">
                [<a href="{{ route('logs', ['name' => \ExtraSecurity::LOG_NAME]) }}" target="_blank">{{ __('Security Logs') }}</a>]
            </p>
        </div>
    </div>

    <h3 class="subheader">{{ __('Restrict Access By IP') }}</h3>

    <div class="form-group{{ $errors->has('settings[extrasecurity.ips_enabled]') ? ' has-error' : '' }} margin-bottom-10">
        <label for="extrasecurity_ips_enabled" class="col-sm-2 control-label">{{ __('Enabled') }}</label>

        <div class="col-sm-6">
            <div class="controls">
                <div class="onoffswitch-wrap">
                    <div class="onoffswitch">
                        <input type="checkbox" name="settings[extrasecurity.ips_enabled]" value="on" id="extrasecurity_ips_enabled" class="onoffswitch-checkbox" @if (old('settings[extrasecurity.recaptcha_main_enabled]', $settings['extrasecurity.ips_enabled'])) checked="checked"@endif >
                        <label class="onoffswitch-label" for="extrasecurity_ips_enabled"></label>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{--<div class="form-group{{ $errors->has('settings.extrasecurity.ips_user_role') ? ' has-error' : '' }} margin-bottom-10">
        <label class="col-sm-2 control-label">{{ __('Users To Check') }}</label>

        <div class="col-sm-6">
            <select class="form-control input-sized-lg" name="settings[extrasecurity.ips_user_role]">
                <option value="" @if (!$settings['extrasecurity.ips_user_role']) selected @endif>{{ __('All') }}</option>
                <option value="{{ App\User::ROLE_USER }}" @if ($settings['extrasecurity.ips_user_role'] == App\User::ROLE_USER) selected @endif>{{ __('User') }}</option>
                <option value="{{ App\User::ROLE_ADMIN }}"  @if ($settings['extrasecurity.ips_user_role'] == App\User::ROLE_ADMIN) selected @endif>{{ __('Administrator') }}</option>
            </select>
        </div>
    </div>--}}

    <div class="form-group{{ $errors->has('settings.extrasecurity.ips') ? ' has-error' : '' }} margin-bottom-10">
        <label class="col-sm-2 control-label">{{ __('Allowed IPs') }}</label>

        <div class="col-sm-6">
            <textarea class="form-control input-sized-lg" name="settings[extrasecurity.ips]" rows="3" placeholder="{{ __('One per line') }}">{{ $settings['extrasecurity.ips'] }}</textarea>

            <p class="form-help">
                <a href="{{ route('extrasecurity.get_ip') }}" target="_blank">{{ __('Determine IP address') }}</a> – {{ __('you can send this link to other users as well to determine their IP addresses.') }}
            </p>
        </div>
    </div>

    <h3 class="subheader">(reCAPTCHA) {{ __('Main Login Form') }}</h3>

	<div class="form-group{{ $errors->has('settings[extrasecurity.recaptcha_main_enabled]') ? ' has-error' : '' }} margin-bottom-10">
        <label for="extrasecurity_recaptcha_main_enabled" class="col-sm-2 control-label">{{ __('Enabled') }}</label>

        <div class="col-sm-6">
            <div class="controls">
                <div class="onoffswitch-wrap">
                    <div class="onoffswitch">
                        <input type="checkbox" name="settings[extrasecurity.recaptcha_main_enabled]" value="on" id="extrasecurity_recaptcha_main_enabled" class="onoffswitch-checkbox" @if (old('settings[extrasecurity.recaptcha_main_enabled]', $settings['extrasecurity.recaptcha_main_enabled'])) checked="checked"@endif >
                        <label class="onoffswitch-label" for="extrasecurity_recaptcha_main_enabled"></label>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="form-group{{ $errors->has('settings.extrasecurity.recaptcha_main_type') ? ' has-error' : '' }} margin-bottom-10">
        <label for="extrasecurity_recaptcha_main_type" class="col-sm-2 control-label">{{ __('Type') }}</label>

        <div class="col-sm-6">
            <select id="extrasecurity_recaptcha_main_type" class="form-control input-sized-lg" name="settings[extrasecurity.recaptcha_main_type]">
                <option value="invisible" @if ($settings['extrasecurity.recaptcha_main_type'] == 'invisible') selected @endif>{{ __('Invisible') }}</option>
                <option value="checkbox"  @if ($settings['extrasecurity.recaptcha_main_type'] == 'checkbox') selected @endif>{{ __('"I\'m not a robot" checkbox') }}</option>
            </select>
        </div>
    </div>
    <div class="form-group{{ $errors->has('settings.extrasecurity.recaptcha_main_site_key') ? ' has-error' : '' }} margin-bottom-10">
        <label for="extrasecurity_recaptcha_main_site_key" class="col-sm-2 control-label">{{ __('Site Key') }}</label>

        <div class="col-sm-6">
            <input id="extrasecurity_recaptcha_main_site_key" type="text" class="form-control input-sized-lg"
                   name="settings[extrasecurity.recaptcha_main_site_key]" value="{{ old('settings.extrasecurity.recaptcha_main_site_key', $settings['extrasecurity.recaptcha_main_site_key']) }}">
        </div>
    </div>
    <div class="form-group{{ $errors->has('settings.extrasecurity.recaptcha_main_secret_key') ? ' has-error' : '' }}">
        <label for="extrasecurity_recaptcha_main_secret_key" class="col-sm-2 control-label">{{ __('Secret Key') }}</label>

        <div class="col-sm-6">
            <input id="extrasecurity_recaptcha_main_secret_key" type="text" class="form-control input-sized-lg"
                   name="settings[extrasecurity.recaptcha_main_secret_key]" value="{{ old('settings.extrasecurity.recaptcha_main_secret_key', $settings['extrasecurity.recaptcha_main_secret_key']) }}">

            <p class="form-help">
                <a href="https://www.google.com/recaptcha/admin" target="_blank">{{ __('Get keys') }}</a> – reCAPTCHA (v2)
            </p>
        </div>
    </div>

    @if (\Module::isActive('enduserportal'))
        <h3 class="subheader">(reCAPTCHA) {{ __('End-User Portal Login Form') }}</h3>

        <div class="form-group{{ $errors->has('settings[extrasecurity.recaptcha_eup_enabled]') ? ' has-error' : '' }} margin-bottom-10">
            <label for="extrasecurity_recaptcha_eup_enabled" class="col-sm-2 control-label">{{ __('Enabled') }}</label>

            <div class="col-sm-6">
                <div class="controls">
                    <div class="onoffswitch-wrap">
                        <div class="onoffswitch">
                            <input type="checkbox" name="settings[extrasecurity.recaptcha_eup_enabled]" value="on" id="extrasecurity_recaptcha_eup_enabled" class="onoffswitch-checkbox" @if (old('settings[extrasecurity.recaptcha_eup_enabled]', $settings['extrasecurity.recaptcha_eup_enabled'])) checked="checked"@endif >
                            <label class="onoffswitch-label" for="extrasecurity_recaptcha_eup_enabled"></label>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="form-group{{ $errors->has('settings.extrasecurity.recaptcha_eup_type') ? ' has-error' : '' }} margin-bottom-10">
            <label for="extrasecurity_recaptcha_eup_type" class="col-sm-2 control-label">{{ __('Type') }}</label>

            <div class="col-sm-6">
                <select id="extrasecurity_recaptcha_eup_type" class="form-control input-sized-lg" name="settings[extrasecurity.recaptcha_eup_type]">
                    <option value="invisible" @if ($settings['extrasecurity.recaptcha_eup_type'] == 'invisible') selected @endif>{{ __('Invisible') }}</option>
                    <option value="checkbox"  @if ($settings['extrasecurity.recaptcha_eup_type'] == 'checkbox') selected @endif>{{ __('"I\'m not a robot" checkbox') }}</option>
                </select>
            </div>
        </div>
        <div class="form-group{{ $errors->has('settings.extrasecurity.recaptcha_eup_site_key') ? ' has-error' : '' }} margin-bottom-10">
            <label for="extrasecurity_recaptcha_eup_site_key" class="col-sm-2 control-label">{{ __('Site Key') }}</label>

            <div class="col-sm-6">
                <input id="extrasecurity_recaptcha_eup_site_key" type="text" class="form-control input-sized-lg"
                       name="settings[extrasecurity.recaptcha_eup_site_key]" value="{{ old('settings.extrasecurity.recaptcha_eup_site_key', $settings['extrasecurity.recaptcha_eup_site_key']) }}">
            </div>
        </div>
        <div class="form-group{{ $errors->has('settings.extrasecurity.recaptcha_eup_secret_key') ? ' has-error' : '' }}">
            <label for="extrasecurity_recaptcha_eup_secret_key" class="col-sm-2 control-label">{{ __('Secret Key') }}</label>

            <div class="col-sm-6">
                <input id="extrasecurity_recaptcha_eup_secret_key" type="text" class="form-control input-sized-lg"
                       name="settings[extrasecurity.recaptcha_eup_secret_key]" value="{{ old('settings.extrasecurity.recaptcha_eup_secret_key', $settings['extrasecurity.recaptcha_eup_secret_key']) }}">

                <p class="form-help">
                    <a href="https://www.google.com/recaptcha/admin" target="_blank">{{ __('Get keys') }}</a> – reCAPTCHA (v2)
                </p>
            </div>
        </div>

        <h3 class="subheader">(reCAPTCHA) {{ __('End-User Portal "Submit a Ticket" Form') }}</h3>

        <div class="form-group{{ $errors->has('settings[extrasecurity.recaptcha_eup_submit_enabled]') ? ' has-error' : '' }} margin-bottom-10">
            <label for="extrasecurity_recaptcha_eup_submit_enabled" class="col-sm-2 control-label">{{ __('Enabled') }}</label>

            <div class="col-sm-6">
                <div class="controls">
                    <div class="onoffswitch-wrap">
                        <div class="onoffswitch">
                            <input type="checkbox" name="settings[extrasecurity.recaptcha_eup_submit_enabled]" value="on" id="extrasecurity_recaptcha_eup_submit_enabled" class="onoffswitch-checkbox" @if (old('settings[extrasecurity.recaptcha_eup_submit_enabled]', $settings['extrasecurity.recaptcha_eup_submit_enabled'])) checked="checked"@endif >
                            <label class="onoffswitch-label" for="extrasecurity_recaptcha_eup_submit_enabled"></label>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="form-group{{ $errors->has('settings.extrasecurity.recaptcha_eup_submit_type') ? ' has-error' : '' }} margin-bottom-10">
            <label for="extrasecurity_recaptcha_eup_submit_type" class="col-sm-2 control-label">{{ __('Type') }}</label>

            <div class="col-sm-6">
                <select id="extrasecurity_recaptcha_eup_submit_type" class="form-control input-sized-lg" name="settings[extrasecurity.recaptcha_eup_submit_type]">
                    <option value="invisible" @if ($settings['extrasecurity.recaptcha_eup_submit_type'] == 'invisible') selected @endif>{{ __('Invisible') }}</option>
                    <option value="checkbox"  @if ($settings['extrasecurity.recaptcha_eup_submit_type'] == 'checkbox') selected @endif>{{ __('"I\'m not a robot" checkbox') }}</option>
                </select>
            </div>
        </div>
        <div class="form-group{{ $errors->has('settings.extrasecurity.recaptcha_eup_submit_site_key') ? ' has-error' : '' }} margin-bottom-10">
            <label for="extrasecurity_recaptcha_eup_submit_site_key" class="col-sm-2 control-label">{{ __('Site Key') }}</label>

            <div class="col-sm-6">
                <input id="extrasecurity_recaptcha_eup_submit_site_key" type="text" class="form-control input-sized-lg"
                       name="settings[extrasecurity.recaptcha_eup_submit_site_key]" value="{{ old('settings.extrasecurity.recaptcha_eup_submit_site_key', $settings['extrasecurity.recaptcha_eup_submit_site_key']) }}">
            </div>
        </div>
        <div class="form-group{{ $errors->has('settings.extrasecurity.recaptcha_eup_submit_secret_key') ? ' has-error' : '' }}">
            <label for="extrasecurity_recaptcha_eup_submit_secret_key" class="col-sm-2 control-label">{{ __('Secret Key') }}</label>

            <div class="col-sm-6">
                <input id="extrasecurity_recaptcha_eup_submit_secret_key" type="text" class="form-control input-sized-lg"
                       name="settings[extrasecurity.recaptcha_eup_submit_secret_key]" value="{{ old('settings.extrasecurity.recaptcha_eup_submit_secret_key', $settings['extrasecurity.recaptcha_eup_submit_secret_key']) }}">

                <p class="form-help">
                    <a href="https://www.google.com/recaptcha/admin" target="_blank">{{ __('Get keys') }}</a> – reCAPTCHA (v2)
                </p>
            </div>
        </div>
    @endif

    <div class="form-group margin-top margin-bottom">
        <div class="col-sm-6 col-sm-offset-2">
            <button type="submit" class="btn btn-primary">
                {{ __('Save') }}
            </button>
        </div>
    </div>
</form>
