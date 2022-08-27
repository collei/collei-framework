@extends('layout.admin')

@section('admin-content')
	<table class="table table-striped table-bordered table-hover table-sm">
		<caption>Users</caption>
		<thead class="thead-dark">
			<tr>
				<th width="30%">User</th>
				<th width="20%">Created</th>
				<th width="20%">Updated</th>
				<th>Actions</th>
			</tr>
		</thead>
		<tbody>
	@foreach( $users as $user )
			<tr>
				<td>
					<span>{{ $user->name }}</span>
					<br>
					<small>{{ $user->email }}</small>
				</td>
				<td><small>{{ $user->createdAt }}</small></td>
				<td><small>{{ $user->updatedAt }}</small></td>
				<td>
					<a href="./users/{{ $user->id }}">[detail]</a> [resetPass] [delete] [block]
				</td>
			</tr>
	@endforeach
		</tbody>
	</table>
@endsection


