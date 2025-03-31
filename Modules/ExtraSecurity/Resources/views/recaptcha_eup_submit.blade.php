@section('eup_javascripts')
    @parent
    <script src="https://recaptcha.net/recaptcha/api.js" async defer></script>
@endsection
@section('eup_javascript')
    @parent
    function esReacaptchaCallback()
    {
        //$('input.g-recaptcha:first').parents('form:first').submit();

        //$('input.g-recaptcha:first').parents('form:first').trigger('submit', [true]);
        $('#eup-ticket-form').trigger('submit', [true]);
    }
    @if ($type != 'checkbox')
        $(document).ready(function(){
            $('#eup-ticket-form').submit(function (e, force_submit) {

                if (force_submit) {
                    return true;
                }

                if (!grecaptcha.getResponse()) {
                    e.preventDefault();

                    /*grecaptcha.reset();

                    var form = $(e.target);
                    if (!form[0].checkValidity()) {
                        form[0].reportValidity();
                        return false;
                    }*/

                    $(this).children().find('.eup-btn-ticket-submit:first').button('reset');

                    // Check recaptcha
                    grecaptcha.execute();
                }
            });
        });
    @endif
@endsection
@php
    if (!isset($errors)) {
        $errors = null;
    }
@endphp
@if ($type == 'checkbox')
    <div class="form-group{{ ($errors && $errors->has('extrasecurity.recaptha')) ? ' has-error' : '' }}">
        <div class="g-recaptcha" data-sitekey="{{ $site_key }}"></div>
        @if ($errors && $errors->has('extrasecurity.recaptha'))
            <span class="help-block form-help">
                <strong>{{ $errors ? $errors->first('extrasecurity.recaptha') : '' }}</strong>
            </span>
        @endif
    </div>
@else
    <div id="recaptcha" class="g-recaptcha" data-sitekey="{{ $site_key }}" data-callback="esReacaptchaCallback" data-size="invisible"></div>
    @if ($errors && $errors->has('extrasecurity.recaptha'))
        <div class="form-group{{ ($errors && $errors->has('extrasecurity.recaptha')) ? ' has-error' : '' }}">
            <span class="help-block form-help">
                <strong>{{ $errors ? $errors->first('extrasecurity.recaptha') : '' }}</strong>
            </span>
        </div>
    @endif
@endif