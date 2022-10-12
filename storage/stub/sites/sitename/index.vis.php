@extends('layout.generic')

@section('site-content')
	@inject('baga','App\Services\RelogioService')
	@inject('dately','Dately\Dately')
				<div class="col-md-12 justify-content-center">
					<h1>Contact List</h1>
					<p>Designed for managing your contact list and related data, such as phone numbers, mail addresses, and so on.</p>
					<p>You can...</p>
					<ul>
						<li>...<a href="">view</a>, add, modify, remove people.
						<li>...<a href="">include</a> and maintain related contact data.
						<li>...<a href="">tag</a> people as and how you want.
						<li>...<a href="">Now's</a> {{ $dately::now()->format('d/m/Y H:i:s |u|') }}.
						<li>...<a href="">Holy Friday's</a> {{ $dately::parse('2022-04-15')->format('d/m/Y') }}.
						<li>...<a href="">É Hoje?</a> {{ ($dately::now()->between('2022-04-15','2022-04-15 23:59:59') ? 'sim' : 'não') }}.
						<li>...<a href="">E...?</a> {{ ($dately::now()->lessThan('2022-04-15') ? 'tá vindo' : 'é hoje ou já passou') }}.
					</ul>
				</div>

				<div>
					<pre>
{{ print_r($GLOBALS['__app.plugins'], true) }}
					</pre>
				</div>
@endsection
