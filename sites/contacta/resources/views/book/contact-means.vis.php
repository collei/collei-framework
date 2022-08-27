@extends('layout.generic')

@section('site-content')
	<div class="modal fade" id="contactMean" tabindex="-1" role="dialog" aria-labelledby="contactMeanLabel">
		<div class="modal-dialog" role="document">
			<form id="contactMeanForm" action="" method="post">
				@csrf
			<div class="modal-content">
				<div class="modal-header">
					<h4 class="modal-title" id="contactMeanLabel">Contact Management</h4>
					<button type="button" class="close" data-dismiss="modal" aria-label="Close">
						<span aria-hidden="true">&times;</span>
					</button>
				</div>
				<div class="modal-body">
					<div class="form-row">
						<div class="col-sm-6">
							<label>Mean:</label>
							<input class="form-control" type="text" name="mean">
						</div>
					</div>
					<div class="form-row">
						<div class="col-sm-10">
							<label>Detail:</label>
							<input class="form-control" type="text" name="detail">
						</div>
					</div>
					<div class="form-row">
						<div class="col-sm-6">
						@if( !empty($types) )
							<label>Kind:</label>
							<select name="mean_type" class="form-control">
							@foreach( $types as $typ )
								<option value="{{ $typ->id }}">{{ $typ->description }}</option>
							@endforeach
							</select>
						@else
							<input class="form-control" type="text" value="{{ iif(empty($mean_type_description), '', $mean_type_description) }}">
							<input type="hidden" name="mean_type" value="{{ iif(empty($mean_type), 0, $mean_type) }}">
						@endif
						</div>
					</div>
				</div>
				<div class="modal-footer">
					<button type="button" id="btn-cancel" class="btn btn-default" data-dismiss="modal">Do not add</button>
					<button type="submit" id="btn-confirm" class="btn btn-primary">Yes, add it</button>
				</div>
			</div>
			</form>
		</div>
	</div>

	<div class="form-row mt-4 mb-4">
		<div class="col-md-2">
			<img height="128px" src="{{ $contact->photoOr() }}"/>
		</div>
		<div class="col-md-4">
			<h2>{{ $contact->name }}</h2>
			<div>This is a <i>{{ $contact->kind()->description }}</i> contact.</div>
			<div>There are <b>{{ $means->size() }}</b> contact means.</div>
		</div>
	</div>

	<table class="table table-striped table-bordered table-hover table-sm">
		<caption>{{ $contact->name }}'s Contact means</caption>
		<thead>
			<tr>
				<th width="20%">Mean</th>
				<th width="15%">Type</th>
				<th width="35%">Detail</th>
				<th width="40%">Actions</th>
			</tr>
		</thead>
		<tbody>
		@if( !empty($means) )
			@foreach( $means as $item )
			<tr>
				<td>{{ $item->mean }}</td>
				<td>{{ $item->type()->description }}</td>
				<td>{{ $item->detail }}</td>
				<td>
					<button type="button" class="btn btn-sm btn-secondary mr-2" data-toggle="modal" data-mode="edit" data-target="#contactMean" data-mean="{{ $item->mean }}" data-detail="{{ $item->detail }}" data-kind="{{ $item->meanTypeId }}" data-route="{{ route('contact-mean-modify', ['contact_id' => $contact->id, 'mean_id' => $item->id]) }}">
						Edit
					</button>
					<button type="button" class="btn btn-sm btn-danger" data-toggle="modal" data-mode="delete" data-target="#contactMean" data-mean="{{ $item->mean }}" data-detail="{{ $item->detail }}" data-kind="{{ $item->meanTypeId }}" data-route="{{ route('contact-mean-remove', ['contact_id' => $contact->id, 'mean_id' => $item->id]) }}">
						Delete
					</button>
				</td>
			</tr>
			@endforeach
		@else
			<tr>
				<th>
					<i>No contacts yet...</i>
				</th>
			</tr>
		@endif
		</tbody>
	</table>

	@auth
	<button type="button" class="btn btn-primary mb-4" data-toggle="modal" data-mode="create" data-target="#contactMean" data-route="{{ route('contact-mean-add', ['contact_id' => $contact->id]) }}">
		Add a new mean
	</button>

	<script>
$('#contactMean').on('show.bs.modal', function (event) {
	var button = $(event.relatedTarget);
	var route = button.data('route');
	var mode = button.data('mode');
	var modal = $(this);

	modal.find('#contactMeanForm').attr('action', route);

	if (mode == 'edit')
	{
		modal.find("#btn-cancel").html('Discard changes');
		modal.find("#btn-confirm").html('Save changes');
		modal.find("#contactMeanForm input[name='mean']").val(button.data('mean'));
		modal.find("#contactMeanForm input[name='detail']").val(button.data('detail'));
		modal.find("#contactMeanForm select[name='mean_type']").val(button.data('kind'));
	}
	else if (mode == 'delete')
	{
		modal.find("#btn-cancel").html('Do not remove');
		modal.find("#btn-confirm").html('Remove, please');
		modal.find("#contactMeanForm input[name='mean']").val(button.data('mean')).attr('readonly', true);
		modal.find("#contactMeanForm input[name='detail']").val(button.data('detail')).attr('readonly', true);
		modal.find("#contactMeanForm select[name='mean_type']").val(button.data('kind')).attr('readonly', true);
	}
	else
	{
		modal.find("#btn-cancel").html('Do not add');
		modal.find("#btn-confirm").html('Yes, add it');
	}
});
	</script>
	@endauth

@endsection
