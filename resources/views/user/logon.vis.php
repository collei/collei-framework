@extends('layout.app')

@section('content')
				<div class="col-md-4 justify-content-center">
					<form action="{{ route('plat-logon') }}" method="post">
						@csrf
						<div class="form-group">
							<label for="username">Usuário</label>
							<input class="form-control" type="text" name="username" id="username"> 
						</div>
						<div class="form-group">
							<label for="password">Senha</label>
							<input class="form-control" type="password" name="password" id="password"> 
						</div>
						<div class="text-center">
							<button type="submit" class="btn btn-primary align-center">Logon</button>
						</div>
					</form>
				</div>
@endsection


