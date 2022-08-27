@extends('layout.admin')

@section('admin-content')
	<style>
		div.file-item { margin: 3.5px !important; }
	</style>
	<table class="table table-striped table-bordered table-hover table-sm">
		<caption>Users</caption>
		<thead class="thead-dark">
			<tr>
				<th width="20%">Category</th>
				<th>Files</th>
			</tr>
		</thead>
		<tbody>
	@foreach( $elements as $element => $items )
			<tr>
				<th valign="top">{{ $element }}</th>
				<td>
		@foreach($items as $n => $item)
					<div class="file-item">
						<span>{{ $item }}</span>
					</div>
		@endforeach
					<div class="file-item">
						<form action="{{ route('plat-adm-site-engine-addfile', ['engine' => $engine ]) }}" method="post">
							@csrf
							@method('PUT')
							<input type="hidden" name="element" value="{{ $element }}" />
							<input type="text" name="classname" size="25">
							<span>{{ PLAT_CLASSES_SUFFIX }}</span>
							<button type="submit" class="btn btn-primary">Create</button>
						</form>
					</div>
				</td>
			</tr>
	@endforeach
		</tbody>
	</table>
@endsection
