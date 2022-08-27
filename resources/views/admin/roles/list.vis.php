@extends('layout.admin')

@section('admin-content')
	<table>
		<caption>Roles</caption>
		<thead>
			<tr>
				<th width="14%">Name</th>
				<th width="13%">Color</th>
				<th width="26%">Icon</th>
				<th width="17%">Created</th>
				<th width="17%">Updated</th>
				<th width="14%">Actions</th>
			</tr>
		</thead>
		<tbody>
	@foreach( $roles as $role )
			<tr>
				<td>{{ $role->name }}</td>
				<td><b style="color: {{ $role->color }} !important;">{{ $role->color }}</b></td>
				<td><i style="color: {{ $role->color }} !important;" class="{{ $role->icon }}"></i> {{ $role->icon }}</td>
				<td><small>{{ $role->createdAt }}</small></td>
				<td><small>{{ $role->updatedAt }}</small></td>
				<td>
					<nobr><a href="./roles/{{ $role->id }}">[detail]</a> [delete]</nobr>
				</td>
			</tr>
	@endforeach
		</tbody>
	</table>
@endsection


