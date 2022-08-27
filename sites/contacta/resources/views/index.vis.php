@extends('layout.generic')

@section('site-content')
	@inject('dately','Dately\Dately')
	@inject('storag','Collei\App\Agents\Storage')
				<div class="col-md-12 justify-content-center">
					<h1>Contact List</h1>
					<p>Designed for managing your contact list and related data, such as phone numbers, mail addresses, and so on.</p>
					<p>You can...</p>
					<ul>
						<li>...<a href="">view</a>, add, modify, remove people.
						<li>...<a href="">include</a> and maintain related contact data.
						<li>...<a href="">tag</a> people as and how you want.
					</ul>
					<p>For example:</p>
					<ul>
						<li>Now's			{{ $dately::now()->format('d/m/Y H:i:s |u|'), }}.
						<li>Holy Friday's	{{ ($holyFriday = $dately::parse($mydat = '2022-05-02'))->format('d/m/Y') }}.
						<li>Tá vindo?		{{ ($dately::now()->lt($holyFriday) ? 'sim' : 'não') }}.
						<li>É Hoje?			{{ ($dately::now()->eq($mydat) ? 'sim' : 'não') }}.
						<li>Já passou?		{{ ($dately::now()->gt($holyFriday) ? 'sim' : 'não') }}.
					</ul>
				</div>

				<div>
					<pre>
{{ print_r([$storag, $congelado ?? 'NO!'], true) }}
					</pre>
					<img src="{!! $qrcodeuri !!}" />
				</div>
@endsection

<!--
yanfei	c6
	indolentes (atk=510, crit=55.1) circlet (%crit=31.1)
		atk		2044
		%crit	75
		crit	199,1
	lpsw (atk=608, %crit=33.1) circlet (crit=62.2)
		atk		2044 // may be lower or higher depending on the new circlet substats
		%crit	77
		crit	206,2
-->
