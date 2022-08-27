@extends('layout.admin')

@section('admin-content')
	<div class="row">
		<div class="col-12-md">
			<p>Index of the Admin page</p>
		</div>
	</div>

	<div>
		<pre>{{ print_r($testings, true) }}</pre>
	</div>
@endsection


