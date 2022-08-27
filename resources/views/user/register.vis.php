@extends('layout.app')

@section('content')
				<div class="col-md-6 justify-content-center">
					<form action="/sites/register" method="post">
						@csrf
						<div class="form-group">
							<label for="username">Usuário</label>
							<input class="form-control" type="text" name="username" id="username"> 
						</div>
						<div class="form-group">
							<label for="email">E-mail</label>
							<input class="form-control" type="email" name="email" id="email"> 
						</div>
						<div class="form-group">
							<label for="password">Senha</label>
							<input class="form-control" type="password" name="password" id="password"> 
						</div>
						<div class="form-group">
							<label for="password_confirm">Confirmar Senha</label>
							<input class="form-control" type="password" name="password_confirm" id="password_confirm"> 
						</div>
						<div class="text-center">
							<button type="submit" class="btn btn-primary align-center">Register</button>
						</div>
					</form>
				</div>
@endsection


