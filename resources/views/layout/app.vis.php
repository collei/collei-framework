<!doctype html>
<html>
	<head>
		<title>{{ site() }} - {{ $sitepart ?? 'home' }}</title>
		<!-- platform - jquery -->
		<script src="{{ asset('js/jquery-3.6.0.min.js') }}" type="text/javascript"></script>
		<!-- platfrom - bootstrap -->
		<!--link rel="stylesheet" href="/plat/sites/resources/css/bootstrap.min.css" type="text/css" /-->
		<link rel="stylesheet" href="{{ asset('css/bootstrap.min.css') }}" type="text/css" />
		<!--script src="/plat/sites/resources/js/bootstrap.bundle.min.js" type="text/javascript"></script-->
		<script src="{{ asset('js/bootstrap.bundle.min.js') }}" type="text/javascript"></script>
		<!--link rel="stylesheet" href="/plat/sites/resources/css/plat.css" type="text/css" /-->
		<link rel="stylesheet" href="{{ asset('css/plat.css') }}" type="text/css" />
		<!--link rel="stylesheet" href="/sites/resources/fontawesome-free-5.15.4-web/css/all.css" type="text/css" /-->
		<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.3/css/all.min.css"
			integrity="sha512-iBBXm8fW90+nuLcSKlbmrPcLa0OT92xO1BIsZ+ywDWZCvqsWgccV3gFoRBv0z+8dLJgyAHIhR35VZc2oM/gI1w=="
			crossorigin="anonymous" referrerpolicy="no-referrer" type="text/css" />

		<link rel="datasource" href="{{ asset('views/user/logon.vis.php') }}" type="text/html" />
	</head>
	<body>
		<div id="header" class="row">
			<div class="col-md-2"></div>
			<div class="col-md-8">
				Cabeçalho em comum

				@auth
					|
					<a href="{{ route('plat-logout') }}">Logout ({{ $user->name }})</a>
					|
					<a href="{{ route('plat-userpanel') }}">My Panel</a>

					@if( $user->hasRole('admin','plat') )
						|
						<span><i class="fas fa-user-tie admin-color"></i> plat</span>
						&bull;
						<a href="{{ route('plat-adm-panel') }}" target="_blank">Admin Panel</a>
					@else
						|
						<span><i class="fas fa-user user-color"></i> plat</span>
					@endif

					@if(site() != 'plat')
						@if( $user->hasRole('admin',site()) )
							|
							<span><i class="fas fa-user-tie admin-color"></i> {{ site() }}</span>
						@else
							|
							<span><i class="fas fa-user user-color"></i> {{ site() }}</span>
						@endif
					@endif
				@endauth

				@guest
					<a href="{{ route('plat-logon') }}">Logon</a>
					|
					<a href="{{ route('plat-register') }}">Register</a>
				@endguest
			</div>
			<div class="col-md-2"></div>
		</div>

		<div id="content" class="content">
			<div class="row">
				<div class="col-md-2"></div>
				<div class="col-md-8">
				@if( !empty($message) )
					<div class="alert alert-success alert-dismissible fade show">
						{{ $message }}
						<button type="button" class="close" data-dismiss="alert" aria-label="Close">
							<span aria-hidden="true">&times;</span>
						</button>
					</div>
				@endif

				@if( !empty($error) )
					<div class="alert alert-danger alert-dismissible fade show">
						{{ $error }}
						<button type="button" class="close" data-dismiss="alert" aria-label="Close">
							<span aria-hidden="true">&times;</span>
						</button>
					</div>
				@endif

					@yield('content')
				</div>
				<div class="col-md-2"></div>
			</div>
		</div>

		<div id="footer">
			<div class="row">
				<div class="col-md-2"></div>
				<div class="col-md-8">
					Rodapé em comum
				</div>
				<div class="col-md-2"></div>
			</div>
		</div>

		<script>
$(document).ready(function(){
	$("#escolhas").on("change", function(e){
		$("#escolhido").text(this.value);
	});
});
		</script>
	</body>
</html>
