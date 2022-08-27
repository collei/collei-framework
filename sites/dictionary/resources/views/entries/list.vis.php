@extends('layout.generic')

@section('site-content')
	<div class="modal fade" id="contactForm" data-origem="" tabindex="-1" role="dialog" aria-labelledby="contactFormLabel">
		<div class="modal-dialog" role="document">
			<form id="contactFormForm" action="" method="post">
	@csrf
			<div class="modal-content">
				<div class="modal-header">
					<h4 class="modal-title" id="contactFormLabel">Dictionary Entries</h4>
					<button type="button" class="close" data-dismiss="modal" aria-label="Close">
						<span aria-hidden="true">&times;</span>
					</button>
				</div>
				<div class="modal-body">
					<div class="form-row">
						<div class="col-sm-7">
							<label>Entry:</label>
							<input class="form-control" type="text" name="entry" id="gEntry">
						</div>
						<div class="col-sm-5">
							<label>Speech part:</label>
							<select name="speech" id="gSpeech" class="form-control">
	@if( !empty($speechparts) )
		@foreach( $speechparts as $sp )
								<option value="{{ $sp->id }}">{{ $sp->description }}</option>
		@endforeach
	@endif
							</select>
						</div>
					</div>
					<div class="form-row">
						<div class="col-sm-5">
							<label>Origin Term:</label>
							<input class="form-control" type="text" name="origin" id="gOrigin">
						</div>
						<div class="col-sm-7">
							<label>Origin From:</label>
							<input class="form-control" type="text" name="origin_from" id="gOriginFrom">
						</div>
					</div>
					<div class="form-row">
						<div class="col-sm-7">
							<label>Meaning:</label>
							<textarea class="form-control" name="meanings" id="gMeanings" rows="6"></textarea>
						</div>
					</div>
				</div>
				<div class="modal-footer">
					<button type="button" id="btn-cancel" class="btn btn-default" data-dismiss="modal">Discard changes</button>
					<button type="submit" id="btn-confirm" class="btn btn-primary">Save changes</button>
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

	<div class="mt-2 mb-3 dic-content" id="searchetarget">
	@if( isEmpty($entries) )
		<div>
			<i>No entries yet...</i>
		</div>
	@else
		@foreach( $entries as $ent )
			@if( dictionary_divisor($entries, $ent) )
			<a name="{{ dictionary_divisor_letter() }}">&nbsp;</a>
			<div class="mt-4 mb-2">
				<b style="font-size: 200%;">{{ dictionary_divisor_letter() }}</b>
			</div>
			@endif

		<div data-entry="{{ $ent->id }}" data-detail="{{ route('dic-entry-json', ['id' => $ent->id]) }}"
			 data-save="{{ route('dic-update-entry', ['id' => $ent->id]) }}">
			<b>{{ $ent->entry }}</b> &nbsp;
			<i>{{ $ent->partof()->description }}</i> &nbsp;
			<span>{{ $ent->meaningsAsString() }}</span> &nbsp;
			<sup>
				<span class="btn-pill entryDetail" data-toggle="modal" data-target="#contactForm">
					<i class="fas fa-info-circle"></i>
				</span>
			</sup>
		</div>
		@endforeach
	@endif
	</div>

	@auth
	<button type="button" class="btn btn-primary mb-4" data-toggle="modal" data-mode="create" data-target="#contactForm" data-route="{{ route('contact-add') }}">
		Add a new entry
	</button>

	<script>

$('#contactForm').on('show.bs.modal', function (event) {
	let dataUrl = $('#contactForm').data('origem');
	console.log('dataUrl -> ', dataUrl);
	$.ajax({
		url: (dataUrl),
		type: 'GET',
		success: (function(data, ts, jqx){
			$('#gEntry').val(data.entry);
			$('#gOrigin').val(data.origin);
			$('#gOriginFrom').val(data.originFrom);
			$('#gSpeech').val(data.speechpartId);
			$('#gMeanings').val(data.meanings.join('\r\n'));
		})
	});
});


$(document).ready(function() {
	$("#searche").on("keyup", function() {
		let value = $(this).val().trim().toLowerCase();

		if (value.length > 2) {
			$("#searchetarget div, #searchetarget a").filter(function() {
				$(this).toggle($(this).text().toLowerCase().indexOf(value) > -1);
			});
		} else if (value.length == 0) {
			$("#searchetarget div, #searchetarget a").filter(function() {
				$(this).toggle(true);
			});
		}
	});

	$(".entryDetail").on("click", function() {
		let uri = $(this.parentNode.parentNode).data('detail');
		let act = $(this.parentNode.parentNode).data('save');
		$('#contactForm').data('origem', uri);
		$('#contactForm #contactFormForm').attr('action', act);
	});
});
	</script>
	@endauth

@endsection
