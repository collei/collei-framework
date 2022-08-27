@extends('layout.admin')

@section('admin-content')
	<div class="row bg-dark text-white mt-2 mb-1">
		<div class="col-md-12"><b>General Profile</b></div>
	</div>
	<div class="row">
		<div class="col-md-1"><b>Name</b></div>
		<div class="col-md-3">{{ $site->name }}</div>
		<div class="col-md-1"><b>Description</b></div>
		<div class="col-md-7">{{ $site->description }}</div>
	</div>
	<div class="row">
		<div class="col-md-1"><b>Created</b></div>
		<div class="col-md-3">{{ $site->createdAt }}</div>
		<div class="col-md-1"><b>Updated</b></div>
		<div class="col-md-3">{{ $site->updatedAt }}</div>
	</div>
	<div class="row bg-dark text-white mt-2 mb-1">
		<div class="col-md-12"><b>Role Features</b></div>
	</div>
	<form action="/sites/admin/sites/{{ $site->id }}" method="post">
		@csrf
	<div class="row">
		<div class="col-md-2"><b>Description</b></div>
		<div class="col-md-8">
			<textarea name="description" rows="2" cols="60">{{ $site->description }}</textarea>
		</div>
	</div>
	<div class="row">
		<div class="col-md-2"><b>Options</b></div>
		<div class="col-md-8">
		@if( $site->isDown )
			<input type="checkbox" id="cbIsDown" name="is_down" value="1" checked>
		@else
			<input type="checkbox" id="cbIsDown" name="is_down" value="1">
		@endif
			<label for="cbIsDown">Site is down</label>
		</div>
	</div>
	<div class="row">
		<div class="col-md-2">&nbsp;</div>
		<div class="col-md-8">
		@if( $site->isAdminOnly )
			<input type="checkbox" id="cbIsAdminOnly" name="is_admin_only" value="1" checked>
		@else
			<input type="checkbox" id="cbIsAdminOnly" name="is_admin_only" value="1">
		@endif
			<label for="cbIsAdminOnly">Make accessible only for admins</label>
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


