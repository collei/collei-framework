@extends('layout.app')

@section('content')
			<h2>User Panel</h2>
			<br>
			<div class="col-md-6 justify-content-center">
				<div class="row mt-3">
					<div class="col-md-4">Username</div>
					<div class="col-md-8">{{ $user->name }}</div>
				</div>
				<div class="row mt-3">
					<div class="col-md-4">MFA Enabled</div>
					<div class="col-md-8">{{ $user->mfaEnabled ? 'Yes' : 'No' }}</div>
				</div>
				<div class="row mt-3">
					<div class="col-md-4">MFA Provider</div>
					<div class="col-md-8">{{ $user->mfaProvider ?? '—' }}</div>
				</div>
			</div>
			<div class="col-md-12 mt-4" sun="bg-dark text-white">
				<div class="row mt-3">
					<h4 class="mt-1 ml-3">Configure Options</h4>
				</div>
			</div>
			<div class="col-md-6 justify-content-center">
				<div class="row mt-3">
					<div class="col-md-8">MFA Settings</div>
				</div>
				<div class="row mt-3">
					<div class="col-md-4">Google Authenticator</div>
					<div class="col-md-8">
	@if( $user->mfaEnabled )
		@if( $user->mfaProvider == 'Google' )
						<a class="btn btn-sm btn-primary" href="{{ route('plat-mfa-unregister') }}">Remove Google MFA</a>
		@else
						<i>MFA already enabled under other provider</i>
		@endif
	@else
						<a class="btn btn-sm btn-primary" href="{{ route('plat-mfa-register') }}">Start Configuration Now</a>
	@endif
					</div>
				</div>
				<div class="row mt-3">
					<div class="col-md-4">Microsoft OAuth</div>
					<div class="col-md-8">
	@if( $user->mfaEnabled )
		@if( $user->mfaProvider == 'Microsoft' )
						<a class="btn btn-sm btn-primary" href="{{ route('plat-mfa-unregister') }}">Remove Microsoft MFA</a>
		@else
						<i>MFA already enabled under other provider</i>
		@endif
	@else
						<a class="btn btn-sm btn-primary" href="{{ route('plat-mfa-register') }}">Start Configuration Now</a>
	@endif
					</div>
				</div>
			</div>
@endsection

