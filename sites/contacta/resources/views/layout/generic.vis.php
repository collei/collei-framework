@extends('layout.app')

@section('content')
	<link rel="stylesheet" href="{{ asset('css/contatos.css') }}" type="text/css" />
	<fieldset>
		<h2><b>Contact Book</b></h2>
		<div>
			<a href="{{ route('contact-home') }}">Home</a>
			|
			<a href="{{ route('contact-people') }}">People</a>

			@auth
			|
			<a href="{{ route('tag-list') }}">Tags</a>
			@endauth

			|
			<a href="{{ route('contact-search') }}">Search</a>

			@auth('admin')
			|
			<a href="{{ route('contacta-admin') }}">Book Admin</a>
			@endauth
		</div>
	</fieldset>

	<fieldset>
		@yield('site-content')
	</fieldset>

	<fieldset>
		<legend>Visibility</legend>

		<i>everyone</i>
		@guest
			|
			<i>guests</i>
		@endguest

		@auth
			|
			<i>formal users</i>
		@endauth

		@auth('admin')
			|
			<i>admins</i>
		@endauth
	</fieldset>
@endsection
