@extends('layout.generic')

@section('site-content')
	<h3 class="headlined">Tags</h3>

	<div class="row mt-2 mb-1" id="pagemaster">
		<div class="col-sm-4">
			<input class="form-control" id="searche" placeholder="Start typing something">
		</div>
		<div class="col-sm-8">
		</div>
	</div>

	<div class="tagpills-edit" id="searchetarget">
		@if( !empty($tags) )
			@foreach( $tags as $tag )
		<div style="background-color: {{ $tag->color }};">
			<div type="button" class="edit readable" style="color: {{ $tag->color }};" data-toggle="modal" data-mode="edit" data-target="#tagDialog" data-name="{{ $tag->name }}" data-color="{{ $tag->color }}" data-item="{{ $tag->id }}" data-route="{{ route('tag-edit', $tag->id) }}">{{ $tag->name }}</div>
			<button type="button" class="btn btn-sm btn-danger remove" data-toggle="modal" data-mode="delete" data-target="#tagDialog" data-name="{{ $tag->name }}" data-color="{{ $tag->color }}" data-item="{{ $tag->id }}" data-route="{{ route('tag-delete', $tag->id) }}">X</button>
		</div>
			@endforeach
		@else
		<i>No tags yet...</i>
		@endif
		<div class="tagpill-new">
			<input type="text" id="tagNameToAdd" placeholder="Name here a brand new tag..." />
		</div>
	</div>

	<div class="modal fade" id="tagDialog" tabindex="-1" role="dialog" aria-labelledby="tagDialogLabel">
		<div class="modal-dialog" role="document">
			<form id="tagDialogForm" action="" method="post" enctype="multipart/form-data">
				@csrf
				<input type="hidden" name="_method" value="POST" id="formethod">
			<div class="modal-content">
				<div class="modal-header">
					<h4 class="modal-title" id="tagDialogLabel">Tag Management</h4>
					<button type="button" class="close" data-dismiss="modal" aria-label="Close">
						<span aria-hidden="true">&times;</span>
					</button>
				</div>
				<div class="modal-body">
					<div class="form-row">
						<div class="col-sm-7">
							<label>Name:</label>
							<input class="form-control" type="text" name="name" size="20" maxlength="32" placeholder="your new tag">
						</div>
					</div>
					<div class="form-row">
						<div class="col-sm-7">
							<label>Color:</label>
							<input class="form-control" type="text" name="color" size="10" maxlength="7" placeholder="#rrggbb">
						</div>
					</div>
				</div>
				<div class="modal-footer">
					<button type="button" id="btn-cancel" class="btn btn-default" data-dismiss="modal">No, don't create it</button>
					<button type="submit" id="btn-confirm" class="btn btn-primary">Yes, create it</button>
				</div>
			</div>
			</form>
		</div>
	</div>

	@auth
	<button type="button" class="btn btn-primary mb-4" data-toggle="modal" data-mode="create" data-target="#tagDialog" data-route="{{ route('tag-create') }}">
		Create a brand new tag
	</button>

	<script>

var contactData = ({
	list:	(function(id) { return "{{ route('tag-list') }}"; }),
	modify:	(function(id) { return "{{ route('tag-edit') }}".replace("{tag}",id); }),
	remove:	(function(id) { return "{{ route('tag-delete') }}".replace("{tag}",id); })
});

$('#tagDialog').on('show.bs.modal', function (event) {
	let button = $(event.relatedTarget);
	let mode = button.data('mode');
	let modal = $(this);

	if (mode == 'edit')
	{
		let route = contactData.modify(button.data('item'))
		modal.find('#tagDialogForm').attr('action', route);
		modal.find('#formethod').val('POST');
		modal.find("#btn-cancel").html('Discard changes');
		modal.find("#btn-confirm").html('Save changes');
		modal.find("#tagDialogForm input[name='name']").val(button.data('name'));
		modal.find("#tagDialogForm input[name='color']").val(button.data('color'));
	}
	else if (mode == 'delete')
	{
		let route = contactData.remove(button.data('item'))
		modal.find('#tagDialogForm').attr('action', route);
		modal.find('#formethod').val('DELETE');
		modal.find("#btn-cancel").html('Do not remove');
		modal.find("#btn-confirm").html('Remove, please');
		modal.find("#tagDialogForm input[name='name']").val(button.data('name'));
		modal.find("#tagDialogForm input[name='name']").attr('readonly', true);
		modal.find("#tagDialogForm input[name='color']").val(button.data('color'));
		modal.find("#tagDialogForm input[name='color']").attr('readonly', true);
	}
	else
	{
		let route = button.data('route');
		modal.find('#tagDialogForm').attr('action', route);
		modal.find('#formethod').val('POST');
		modal.find("#btn-cancel").html("No, don't create it");
		modal.find("#btn-confirm").html('Yes, create it');
	}
});

function createPill(name, id)
{
	let dv = document.createElement('div'),
		color = "#000000";
	dv.style.backgroundColor = '#000';
	//
	let dve = document.createElement('div');
	dve.className = 'edit readable';
	dve.innerText = name;
	dve.style.backgroundColor = '#000';
	dve.setAttribute("type", "button");
	dve.setAttribute("data-toggle", "modal");
	dve.setAttribute("data-mode", "edit");
	dve.setAttribute("data-target", "#tagDialog");
	dve.setAttribute("data-name", name);
	dve.setAttribute("data-color", color);
	dve.setAttribute("data-item", id);
	dve.setAttribute("data-route", contactData.modify(id));
	//
	let btn = document.createElement('button');
	btn.className = 'btn btn-sm btn-danger remove';
	btn.innerText = 'X';
	btn.setAttribute("type", "button");
	btn.setAttribute("data-toggle", "modal");
	btn.setAttribute("data-mode", "delete");
	btn.setAttribute("data-target", "#tagDialog");
	btn.setAttribute("data-name", name);
	btn.setAttribute("data-color", color);
	btn.setAttribute("data-item", id);
	btn.setAttribute("data-route", contactData.remove(id));
	//
	dv.appendChild(dve);
	dv.appendChild(btn);
	//
	return dv;
}

$(document).ready(function() {
	$("#searche").on("keyup", function(){
		var value = $(this).val().toLowerCase();
			$("#searchetarget div").filter(function() {
			$(this).toggle($(this).text().toLowerCase().indexOf(value) > -1)
		});
	});

	$("#tagNameToAdd").on("blur", function(event){
		let fname = $(event.target).val().trim(),
			input = event.target;
		//
		if (fname!="")
		{
			let formData = {
				name: (fname),
				color: ('#000000'),
				_token: '@csrf_token'
			};
			//
			$.ajax({
				data: formData,
				type: "PUT",
				url: ("{{ route('tag-create-ajax') }}"),
				success: (function(d,t,x) {
					if (d.tag) if (d.tag.id)
					{
						let dv = createPill(d.tag.name, d.tag.id);
						input.parentNode.parentNode.insertBefore(dv, input.parentNode);
						input.value = '';
					}
				})
			});
		}
	});
});

	</script>
	@endauth

@endsection