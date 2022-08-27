@extends('layout.generic')

@section('site-content')
	<div class="modal fade" id="contactForm" tabindex="-1" role="dialog" aria-labelledby="contactFormLabel">
		<div class="modal-dialog" role="document">
			<form id="contactFormForm" action="" method="post">
				@csrf
			<div class="modal-content">
				<div class="modal-header">
					<h4 class="modal-title" id="contactFormLabel">Contact Management</h4>
					<button type="button" class="close" data-dismiss="modal" aria-label="Close">
						<span aria-hidden="true">&times;</span>
					</button>
				</div>
				<div class="modal-body">
					<div class="form-row">
						<div class="col-sm-7">
							<label>Name:</label>
							<input class="form-control" type="text" name="name">
						</div>
					</div>
					<div class="form-row">
						<div class="col-sm-7">
						@if( !empty($types) )
							<label>Kind:</label>
							<select name="contact_type" class="form-control">
							@foreach( $types as $typ )
								<option value="{{ $typ->id }}">{{ $typ->description }}</option>
							@endforeach
							</select>
						@else
							<input class="form-control" type="text" value="{{ iif(empty($contact_type_description), '', $contact_type_description) }}">
							<input type="hidden" name="contact_type" value="{{ iif(empty($contact_type), 0, $contact_type) }}">
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

	<div class="row mt-4 mb-1">
		<div class="col-sm-4">
			<input class="form-control" id="searche" placeholder="Start typing something">
		</div>
	</div>

	<table class="table table-striped table-bordered table-hover table-search table-sm" id="searchetarget">
		<caption>Phone Book</caption>
		<thead>
			<tr>
				<th width="30%">Name</th>
				<th width="20%">Kind</th>
				<th width="15%">Means</th>
				<th width="45%">Actions</th>
			</tr>
		</thead>
		<tbody>
		@if( !empty($people) )
			@foreach( $people as $contact )
			<tr>
				<td>{{ $contact->name }}</td>
				<td>{{ $contact->kind()->description }}</td>
				<td>{{ $contact->meanList()->size() }}</td>
				<td>
					<a class="btn btn-sm btn-secondary mr-2" href="{{ route('contact-means', ['id' => $contact->id]) }}">
						Means
					</a>
					<button type="button" class="btn btn-sm btn-secondary mr-2" data-toggle="modal" data-mode="edit" data-target="#contactForm" data-name="{{ $contact->name }}" data-kind="{{ $contact->contactTypeId }}" data-route="{{ route('contact-modify', ['id' => $contact->id]) }}">
						Edit
					</button>
					<button type="button" class="btn btn-sm btn-danger" data-toggle="modal" data-mode="delete" data-target="#contactForm" data-name="{{ $contact->name }}" data-kind="{{ $contact->contactTypeId }}" data-route="{{ route('contact-remove', ['id' => $contact->id]) }}">
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
	<button type="button" class="btn btn-primary mb-4" data-toggle="modal" data-mode="create" data-target="#contactForm" data-route="{{ route('contact-add') }}">
		Add a new contact
	</button>

	<script>
$('#contactForm').on('show.bs.modal', function (event) {
	var button = $(event.relatedTarget);
	var route = button.data('route');
	var mode = button.data('mode');
	var modal = $(this);

	modal.find('#contactFormForm').attr('action', route);

	if (mode == 'edit')
	{
		modal.find("#btn-cancel").html('Discard changes');
		modal.find("#btn-confirm").html('Save changes');
		modal.find("#contactFormForm input[name='name']").val(button.data('name'));
		modal.find("#contactFormForm select[name='contact_type']").val(button.data('kind'));
	}
	else if (mode == 'delete')
	{
		modal.find("#btn-cancel").html('Do not remove');
		modal.find("#btn-confirm").html('Remove, please');
		modal.find("#contactFormForm input[name='name']").val(button.data('name'));
		modal.find("#contactFormForm input[name='name']").attr('readonly', true);
		modal.find("#contactFormForm select[name='contact_type']").val(button.data('kind'));
		modal.find("#contactFormForm select[name='contact_type']").attr('readonly', true);
	}
	else
	{
		modal.find("#btn-cancel").html('Do not add');
		modal.find("#btn-confirm").html('Yes, add it');
	}
});

$(document).ready(function() {
	$("#searche").on("keyup", function() {
		var value = $(this).val().toLowerCase();
			$("#searchetarget tbody tr").filter(function() {
			$(this).toggle($(this).text().toLowerCase().indexOf(value) > -1)
		});
	});
});
	</script>
	@endauth

@endsection
