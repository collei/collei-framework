@extends('layout.app')

@section('content')
				<div class="col-md-4 justify-content-center">
					<form action="{{ route('plat-logon-confirm') }}" method="post">
						@csrf
						<div class="form-group">
							<label for="confirmcode">Código de Confirmação</label>
							<input class="form-control" type="text" name="confirmcode" id="confirmcode" size="8" maxlength="6"> 
						</div>
						<div class="text-center">
							<button type="submit" class="btn btn-primary align-center">Confirm Logon</button>
						</div>
					</form>
				</div>
@endsection


