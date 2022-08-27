@extends('layout.app')

@section('content')
	<fieldset>
		<h2><b>Example Site</b> <i><small>Some fancy description</small></i></h2>
		<div>
			<a href="{{ route('site-home') }}">Home</a>

			@auth
			|
			<a href="{{ route('site-subject') }}">Subject</a>
			@endauth

			@auth('admin')
			|
			<a href="{{ route('site-adm-panel') }}">Área Administrativa</a>
			@endauth
		</div>
	</fieldset>

	<fieldset>
		@yield('site-content')
	</fieldset>

	<fieldset>
		<legend>Visibility</legend>

		<i>todos</i>
		@guest
			|
			<i>Guests</i>
		@endguest

		@auth
			|
			<i>Registered Users</i>
		@endauth

		@auth('admin')
			|
			<i>Administrators</i>
		@endauth
	</fieldset>
@endsection
