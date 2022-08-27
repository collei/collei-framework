@extends('layout.app')

@section('content')
			<div class="row">	
				<div class="col-md-12 justify-content-center">
					<h1>ERROR!</h1>

					<fieldset>
						<legend>{{ $title }}</legend>
						<h5>
							<b>Cause:</b>
							{{ $longText }}
						</h5>
@if(!empty($trace))
<div>
<pre>							
{{ $trace }}
</pre>
</div>
@endif
					</fieldset>
				</div>
			</div>
@endsection
