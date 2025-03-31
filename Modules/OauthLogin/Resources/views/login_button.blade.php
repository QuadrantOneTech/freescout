<div class="form-horizontal">
	<div class="form-group margin-top-30">
		<div class="col-md-8 col-md-offset-4">
			<a href="?oauth=1&amp;oauth_provider={{ $provider_index }}" class="btn btn-primary"><em class="glyphicon glyphicon-log-in"></em>&nbsp;
				{{ __('Sign in with :provider_name', ['provider_name' => $provider_name]) }}
			</a>
		</div>
	</div>
</div>
