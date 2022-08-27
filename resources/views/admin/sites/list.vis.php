@extends('layout.admin')

@section('admin-content')
	<table class="table table-striped table-bordered table-hover table-sm">
		<caption>Users</caption>
		<thead class="thead-dark">
			<tr>
				<th width="30%">Site</th>
				<th>Down<br>Time</th>
				<th>ADM<br>Only</th>
				<th width="20%">Created</th>
				<th width="20%">Updated</th>
				<th>Actions</th>
			</tr>
		</thead>
		<tbody>
	@foreach( $sites as $item )
			<tr>
				<td>
					<span>{{ $item->name }}</span>
					<br>
					<small>{{ $user->description }}</small>
				</td>
				<td><small>{{ iif($item->isDown, 'Yes', 'No') }}</small></td>
				<td><small>{{ iif($item->isAdminOnly, 'Yes', 'No') }}</small></td>
				<td><small>{{ $item->createdAt }}</small></td>
				<td><small>{{ $item->updatedAt }}</small></td>
				<td>
					<h4>
						<a href="{{ route('plat-adm-site-engine-view', $item->name) }}">
							<i class="fa fa-cogs" title="Manage Engine"></i>
						</a>
						<a href="{{ route('plat-adm-siteman-detail', $item->id) }}">
							<i class="fa fa-edit" title="Site Details"></i>
						</a>
					</h4>
				</td>
			</tr>
	@endforeach
		</tbody>
	</table>
@endsection
