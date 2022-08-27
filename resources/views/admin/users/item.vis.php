@extends('layout.admin')

@section('admin-content')
	<div class="row bg-dark text-white mt-2 mb-1">
		<div class="col-md-12"><b>General Profile</b></div>
	</div>
	<div class="row">
		<div class="col-md-2"><b>Name</b></div>
		<div class="col-md-4">{{ $usr->name }}</div>
		<div class="col-md-2"><b>Email</b></div>
		<div class="col-md-4">{{ $usr->email }}</div>
	</div>
	<div class="row">
		<div class="col-md-2"><b>Created</b></div>
		<div class="col-md-4">{{ coalesce($usr->createdAt, 'Never') }}</div>
		<div class="col-md-2"><b>Updated</b></div>
		<div class="col-md-4">{{ coalesce($usr->updatedAt, 'Never') }}</div>
	</div>
	<div class="row">
		<div class="col-md-2"><b>Permissions</b></div>
		<div class="col-md-8">
			<div class="form-row">
	@foreach( $usr->permissions() as $permission )
				<div class="pill" data-id="{{ $permission->id }}">
					<i>{{ $permission->role()->name }}</i>
					at
					<i>{{ $permission->site()->name }}</i>
				</div>
	@endforeach
			</div>
		</div>
	</div>
	<div class="row bg-dark text-white mt-2 mb-1">
		<div class="col-md-12"><b>Permissions Management</b></div>
	</div>
	<div class="row">
		<div class="col-md-2"><b>Change</b></div>
		<div class="col-md-6">
	@foreach( $sitelist as $sitei )
			<div class="form-row" data-id="{{ $permission->id }}">
				<div class="col-md-3">
					<label for="permsite{{ $sitei->id }}">{{ $sitei->name }}</label>
				</div>
				<div class="col-md-8">
					<select class="rolechanger" id="permsite{{ $sitei->id }}" data-site="{{ $sitei->id }}">
		@foreach( $rolelist as $rolei )
			@if( $usr->hasRole($rolei->name, $sitei->name) )
						<option value="{{ $rolei->id }}" selected>{{ $rolei->name }}</option>
			@else
						<option value="{{ $rolei->id }}">{{ $rolei->name }}</option>
			@endif
		@endforeach
					</select>
				</div>
			</div>
	@endforeach
		</div>
	</div>
	<div class="row bg-dark text-white mt-2 mb-1">
		<div class="col-md-12"><b>Password Management</b></div>
	</div>
	<div class="row">
		<div class="col-md-2"><b>Change to</b></div>
		<div class="col-md-6">
			<input type="password" id="newPassword">
		</div>
	</div>
	<div class="row">
		<div class="col-md-2"><b>Options</b></div>
		<div class="col-md-6">
			<input type="checkbox" id="passwordMustChange" value="yes"> <label for="passwordMustChange">Enforce change at next logon</label>
		</div>
	</div>
	<div class="row">
		<div class="col-md-2"></div>
		<div class="col-md-6">
			<button type="button" id="passwordChanger">Change Password</button>
		</div>
	</div>
	<div class="row bg-dark text-white mt-2 mb-1">
		<div class="col-md-12"><b>MFA Management</b></div>
	</div>
	@if($usr->mfaEnabled)
	<div class="row">
		<div class="col-md-2"></div>
		<div class="col-md-6">
			<button type="button" id="mfaRemover" data-provider="{{ $usr->mfaProvider }}">Remove {{ $usr->mfaProvider }} MFA</button>
		</div>
	</div>
	@else
	<div class="row">
		<div class="col-md-2"></div>
		<div class="col-md-6">
			<i>This user has no MFA option set.</i>
		</div>
	</div>
	@endif


<script>
$(document).ready(function(){
	$('select.rolechanger').on('change', function(){
		let site = $(this).data('site');
		let role = $(this).find('option:selected').val();
		let user = ({{ $usr->id }});
		let url = '/sites/admin/users/' + user + '/sites/' + site + '/roles/' + role;

		$.ajax(url, {
			method: 'POST',
			data: {
				'_token': '@csrf_token'
			},
			success: (function(d,t,m){
				alert('Successfully updated.');
			}),
			error: (function(m,t,o){
				alert('Furou...');
			})
		});
	});

	$('#passwordChanger').on('click', function(){
		let newpass = $('#newPassword').val();
		let mustchange = $('#passwordMustChange').is(':checked');
		let user = ({{ $usr->id }});
		let url = '/sites/admin/users/' + user + '/changepassword';

		$.ajax(url, {
			method: 'POST',
			data: {
				'new_password': newpass,
				'must_change': (mustchange ? 1 : 0),
				'_token': '@csrf_token'
			},
			success: (function(d,t,m){
				alert('Successfully updated.');
			}),
			error: (function(m,t,o){
				alert('Error: password not changed or something else');
			})
		});
	});

	$('#mfaRemover').on('click', function(){
		let user = ({{ $usr->id }});
		let url = '/sites/admin/users/' + user + '/removemfa';
		//
		$.ajax(url, {
			method: 'POST',
			data: {
				'_token': '@csrf_token'
			},
			success: (function(d,t,m){
				let parentNode = $('#mfaRemover')[0].parentNode;
				let provider = $('#mfaRemover').attr('data-provider');
				$('#mfaRemover').remove();
				$(parentNode).html('<i>' + provider + ' MFA successfully removed.</i>');
			}),
			error: (function(m,t,o){
				alert('Error: MFA not removed or something else');
			})
		});
	});
});
</script>
@endsection


