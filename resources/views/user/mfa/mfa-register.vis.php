@extends('layout.app')

@section('content')
				<div class="col-md-4 justify-content-center form-group">
					<form action="{{ route('plat-mfa-register-complete') }}" method="post">
						@csrf
						<div class="row">
							Escaneie o QR Code abaixo usando o APP Google Authenticator
							em seu smartphone ou celular para associar a conta do PLAT sistema
							à sua conta do Google. 
						</div>
						<div class="row mt-4 mb-4">
							<img src="{{ $qrcode }}"/>
						</div>
						<div class="row">
							<label for="confirmCode">Confirmation Code</label>
							<input type="text" class="form-control" name="confirmcode" id="confirmCode" size="8" maxlength="6" />
						</div>
						<div class="text-center">
							<button type="submit" class="btn btn-primary align-center">Continuar</button>
						</div>
					</form>
				</div>
@endsection

