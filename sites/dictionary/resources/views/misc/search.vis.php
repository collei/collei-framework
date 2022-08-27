@extends('layout.generic')

@section('site-content')
<style>
td.borderless, tr.borderless td {
	border-style: none !important;
}
</style>

<form action="{{ route('contact-search-do') }}" method="post">
	@csrf
<div class="form-row mt-4 mb-4">
	<div class="col-sm-4">
		<input type="text" class="form-control" name="search" value="{{ $search }}" placeholder="Type something...">
	</div>
	<div class="col-sm-2">
		<button type="submit" class="btn btn-sm btn-primary">Search</button>
	</div>
</div>
</form>

<div class="row">
	<div class="col-sm-12">
		<table class="table table-satriped wtable-bordered table-sm">
			<caption>Search results</caption>
			<thead>
				<tr>
					<th width="30%">Mean</th>
					<th width="20%">Type</th>
					<th width="15%">Detail</th>
					<th width="45%">Actions</th>
				</tr>
			</thead>
			<tbody>
		@if( !empty($people) && ($people->size() > 0) )
			@foreach( $people as $contact )
				<tr>
					<td colspan="8">
						<div class="row mb-2 mt-1 ml-1">
							<b>{{ $contact->name }}</b>
							({{ $contact->meanList()->size() }})
							<small><sup><i>{{ $contact->kind()->description }}</i></sup></small>
						</div>
						<div class="row">
							<table class="table table-bordered table-sm">
				@if( !empty($contact->meanList()) )
					@foreach( $contact->meanList() as $item )
								<tbody class="table table-hover">
									<tr>
										<td width="30%">{{ $item->mean }}</td>
										<td width="20%">{{ $item->type()->description }}</td>
										<td width="60%">{{ $item->detail }}</td>
									</tr>
								</tbody>
					@endforeach
				@endif
							</table>
						</div>
					</td>
				</tr>
			@endforeach
		@else
				<tr>
					<td colspan="8">
						<div class="row mb-2 mt-1 ml-1">
							<i>No items met the condition you said...</i>
						</div>
					</td>
				</tr>
		@endif
			</tbody>
		</table>
	</div>
</div>
@endsection
