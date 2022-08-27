@extends('layout.generic')

@section('site-content')
	<div class="modal fade" id="contactForm" tabindex="-1" role="dialog" aria-labelledby="contactFormLabel">
		<div class="modal-dialog" role="document">
			<form id="contactFormForm" action="" method="post" enctype="multipart/form-data">
				@csrf
				<input type="hidden" name="_method" value="POST" id="formethod">
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
							<label>Photo:</label>
							<input class="form-control" type="file" name="avatar">
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

	<h3 class="headlined">People</h3>

	<div class="row mt-2 mb-1" id="pagemaster">
		<div class="col-sm-4">
			<input class="form-control" id="searche" placeholder="Start typing something">
		</div>
		<div class="col-sm-4">
		</div>
		<div class="col-sm-4">
			<div class="row">
				<div class="col-sm-1 mr-1">
					<button type="button" class="btn btn-sm" onclick="window.location='?page={{ $pagination->first }}';" data-page="{{ $pagination->first }}"><i class="fa fa-backward"></i></button>
				</div>
				<div class="col-sm-1 mr-1">
					<button type="button" class="btn btn-sm" onclick="window.location='?page={{ $pagination->prior }}';" data-page="{{ $pagination->prior }}">◄</button>
				</div>
				<div class="col-sm-4 text-center ml-3" style="border: 1px solid #ddd; border-radius: 4px;">
					<div class="cas-form-control">{{ $pagination->current }}</div>
				</div>
				<div class="col-sm-1 mr-2">
					<button type="button" class="btn btn-sm" onclick="window.location='?page={{ $pagination->next }}';" data-page="{{ $pagination->next }}"><b>►</b></button>
				</div>
				<div class="col-sm-1">
					<button type="button" class="btn btn-sm" onclick="window.location='?page={{ $pagination->last }}';" data-page="{{ $pagination->last }}"><i class="fa fa-forward"></i></button>
				</div>
			</div>
		</div>
	</div>

	<table class="table table-striped table-bordered table-hover table-search table-sm" id="searchetarget">
		<caption>Phone Book</caption>
		<thead>
			<tr>
				<th width="3%">#</th>
				<th width="20%">Name</th>
				<th width="20%">Photo</th>
				<th width="15%">Kind</th>
				<th width="7%">Means</th>
				<th width="35%">Actions</th>
			</tr>
		</thead>
		<tbody id="pagefield">
		@if( !empty($people) )
			@foreach( $people as $contact )
			<tr>
				<td>{{ $contact->id }}</td>
				<td class="{{ strtolower(str_replace(' ', '-', $contact->name)) }}"><b>{{ $contact->name }}</b></td>
				<td><img height="32px" src="{{ $contact->photoOr() }}"/></td>
				<td>{{ $contact->kind()->description }}</td>
				<td>{{ $contact->meanList()->size() }}</td>
				<td>
					<span class="text-info mr-2 contactDetail pointer" data-item="{{ $contact->id }}">
						<i class="fa fa-2x fa-file-invoice" data-item="{{ $contact->id }}"></i>
					</span>
					<span class="text-success mr-2f pointer" data-toggle="modal" data-mode="edit" data-target="#contactForm" data-name="{{ $contact->name }}" data-kind="{{ $contact->contactTypeId }}" data-item="{{ $contact->id }}">
						<i class="fa fa-2x fa-edit"></i>
					</span>
					<span class="text-danger pointer" data-toggle="modal" data-mode="delete" data-target="#contactForm" data-name="{{ $contact->name }}" data-kind="{{ $contact->contactTypeId }}" data-item="{{ $contact->id }}">
						<i class="fa fa-2x fa-trash-alt"></i>
					</span>
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

var contactData = ({
	list:	(function(id) { return "{{ route('contact-means') }}".replace("{id}",id); }),
	modify:	(function(id) { return "{{ route('contact-modify') }}".replace("{id}",id); }),
	remove:	(function(id) { return "{{ route('contact-remove') }}".replace("{id}",id); })
});

$('#contactForm').on('show.bs.modal', function (event) {
	let button = $(event.relatedTarget);
	let mode = button.data('mode');
	let modal = $(this);

	if (mode == 'edit')
	{
		let route = contactData.modify(button.data('item'))
		modal.find('#contactFormForm').attr('action', route);
		modal.find('#contactFormForm').attr('enctype', 'multipart/form-data');
		modal.find('#formethod').val('POST');
		modal.find("#btn-cancel").html('Discard changes');
		modal.find("#btn-confirm").html('Save changes');
		modal.find("#contactFormForm input[name='name']").val(button.data('name'));
		modal.find("#contactFormForm select[name='contact_type']").val(button.data('kind'));
	}
	else if (mode == 'delete')
	{
		let route = contactData.remove(button.data('item'))
		modal.find('#contactFormForm').attr('action', route);
		modal.find('#contactFormForm').attr('enctype', 'application/x-www-form-urlencoded');
		modal.find('#formethod').val('DELETE');
		modal.find("#btn-cancel").html('Do not remove');
		modal.find("#btn-confirm").html('Remove, please');
		modal.find("#contactFormForm input[name='name']").val(button.data('name'));
		modal.find("#contactFormForm input[name='name']").attr('readonly', true);
		modal.find("#contactFormForm select[name='contact_type']").val(button.data('kind'));
		modal.find("#contactFormForm select[name='contact_type']").attr('readonly', true);
	}
	else
	{
		let route = button.data('route');
		modal.find('#contactFormForm').attr('action', route);
		modal.find('#contactFormForm').attr('enctype', 'multipart/form-data');
		modal.find('#formethod').val('POST');
		modal.find("#btn-cancel").html('Do not add');
		modal.find("#btn-confirm").html('Yes, add it');
	}
});


$(document).ready(function() {
	$("#searche").on("keyup", function(){
		var value = $(this).val().toLowerCase();
			$("#searchetarget tbody tr").filter(function() {
			$(this).toggle($(this).text().toLowerCase().indexOf(value) > -1)
		});
	});

	$(".contactDetail").on("click", function(event){
		let button = $(event.target);
		let dest = contactData.list(button.data('item'));
		window.location = dest;
	});

/*
	$("#pagemaster button").on("click", function(){
		let page = $(this).data("page");
		let name = $(this).html();
		let field = $("#pagefield");

		$.ajax({
			type: "GET",
			url: ("./people/page/" + page),
			success: (function(d,t,x) {
				alert('pressed button: ' + name);
				console.log('data: ', d);
			})
		});

	});
*/
});
	</script>
	@endauth

@endsection
