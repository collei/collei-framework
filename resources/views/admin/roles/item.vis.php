@extends('layout.admin')

@section('admin-content')
	<div class="row bg-dark text-white mt-2 mb-1">
		<div class="col-md-12"><b>General Profile</b></div>
	</div>
	<div class="row">
		<div class="col-md-1"><b>Name</b></div>
		<div class="col-md-3">{{ $role->name }}</div>
		<div class="col-md-1"><b>Color</b></div>
		<div class="col-md-3">#{{ $role->color }}</div>
		<div class="col-md-1"><b>Icon</b></div>
		<div class="col-md-3">{{ $role->icon }}</div>
	</div>
	<div class="row">
		<div class="col-md-1"><b>Created</b></div>
		<div class="col-md-3">{{ $role->createdAt }}</div>
		<div class="col-md-1"><b>Updated</b></div>
		<div class="col-md-3">{{ $role->updatedAt }}</div>
	</div>
	<div class="row bg-dark text-white mt-2 mb-1">
		<div class="col-md-12"><b>Role Features</b></div>
	</div>
	<form action="/sites/admin/roles/{{ $role->id }}" method="post">
		@csrf
	<div class="row">
		<div class="col-md-2"><b>Rename to</b></div>
		<div class="col-md-6">
			<input type="text" name="name" value="{{ $role->name }}">
		</div>
	</div>
	<div class="row">
		<div class="col-md-2"><b>New color</b></div>
		<div class="col-md-6">
			<input type="text" name="color" value="{{ $role->color }}">
		</div>
	</div>
	<div class="row">
		<div class="col-md-2"><b>New Icon</b></div>
		<div class="col-md-6">
			<input type="text" name="icon" value="{{ $role->icon }}">
		</div>
	</div>
	<div class="row">
		<div class="col-md-2"></div>
		<div class="col-md-6">
			<button type="submit">Submit changes</button>
		</div>
	</div>
	</form>
@endsection


