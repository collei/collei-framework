@extends('layout.app')

@section('content')
	<style>
		.dic-content {
			height: 60vh !important;
			overflow-y: scroll !important;
		}
		.child-change > a {
			display: inline-block !important;
			vertical-align: middle !important;
		}
	</style>
	<fieldset>
		<div class="child-change">
			<span style="font-size:200%;"><b>Dictionary</b> | </span>

			<a class="align-middle" href="{{ route('dic-home') }}">Home</a>
			|
			<a class="align-middle" href="{{ route('dic-list') }}">List</a>
			|
			<a class="align-middle" href="{{ route('dic-export') }}" target="_blank">Download Data</a>
		</div>
	</fieldset>

	<fieldset>
		@yield('site-content')
	</fieldset>
@endsection
