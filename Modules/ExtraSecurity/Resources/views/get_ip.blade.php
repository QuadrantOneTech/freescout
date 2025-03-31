@extends('layouts.app')

@section('title', __('Determine IP address'))

@section('content')
	<div class="container">
		@include('partials/empty', ['icon' => 'comment', 'empty_header' => __('Your IP address is:'), 'empty_text' => $ip])
	</div>
@endsection