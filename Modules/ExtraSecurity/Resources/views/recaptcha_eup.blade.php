@section('eup_javascripts')
    @parent
    <script src="https://recaptcha.net/recaptcha/api.js" async defer></script>
@endsection
@section('eup_javascript')
    @parent
    function esReacaptchaCallback()
    {
        //$('button.g-recaptcha:first').parents('form:first').submit();
        $('button.es-submit-btn:first').parents('form:first').trigger('submit', [true]);
    }
    @if ($type != 'checkbox')
        $(document).ready(function(){
            $('button.es-submit-btn:first').parents('form:first').submit(function (e, force_submit) {

                if (force_submit) {
                    return true;
                }

                if (!grecaptcha.getResponse()) {
                    e.preventDefault();

                    // Check recaptcha
                    grecaptcha.execute();
                }
            });
        });
    @endif
@endsection
@if ($type == 'checkbox')
    <div class="form-group{{ $errors->has('extrasecurity.recaptha') ? ' has-error' : '' }}">
        <div class="g-recaptcha" data-sitekey="{{ $site_key }}"></div>
        @if ($errors->has('extrasecurity.recaptha'))
            <span class="help-block form-help">
                <strong>{{ $errors->first('extrasecurity.recaptha') }}</strong>
            </span>
        @endif
    </div>
@else
    <div id="recaptcha" class="g-recaptcha" data-sitekey="{{ $site_key }}" data-callback="esReacaptchaCallback" data-size="invisible"></div>
    @if ($errors->has('extrasecurity.recaptha'))
        <div class="form-group{{ $errors->has('extrasecurity.recaptha') ? ' has-error' : '' }}">
            <span class="help-block form-help">
                <strong>{{ $errors->first('extrasecurity.recaptha') }}</strong>
            </span>
        </div>
    @endif
@endif