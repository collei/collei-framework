@extends('layout.app')

@section('content')
	<div class="row">
		<div class="col-md-12 justify-content-center">
			<h1>General Admin Panel <small><i>of</i> PLAT system</small></h1> 

			<div>
				<a href="{{ route('plat-adm-panel') }}">Home</a>
				|
				<a href="{{ route('plat-adm-users') }}">Manage Users</a>
				|
				<a href="{{ route('plat-adm-roles') }}">Manage Roles</a>
				|
				<a href="{{ route('plat-adm-sites') }}">Manage Sites</a>
				|
				<a href="{{ route('plat-adm-res') }}">Manage Resources</a>
				|
				<a href="{{ route('aaa') }}">Tricia i Anatolie</a>
				|
				<a href="{{ route('aaa') }}">Nebaranahari Terapanarapanari</a>
			</div>
			<hr>
		</div>
	</div>
	<div class="row">
		<div class="col-md-12">
			@yield('admin-content')
		</div>
	</div>
@endsection


