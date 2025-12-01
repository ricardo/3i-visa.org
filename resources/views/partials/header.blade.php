@php
	$user             = auth()->getUser();
	$current_currency = request()->cookie( 'preferred_currency', 'USD' );
@endphp
<header>
	<div class="container">
		<a href="{{ route( 'home' ) }}" class="logo">
			<img src="{{ asset( 'images/logo.svg' ) }}" alt="3i Visa">
		</a>

		@guest
			<!-- <a href="javascript: void(0);" role="button" class="contrast header-button display-mobile-only" onClick="window.openDialog( '#dialog-auth', '#login' );">Login</a> -->

			<button id="menu-toggle" class="nav-toggle" aria-label="Open menu">
				<span></span>
				<span></span>
				<span></span>
			</button>
		@endguest

		@guest
			<nav>
				<ul>
					<li class="display-desktop-only">
						<a href="javascript: void(0);" role="button" class="white header-button translate-button" onClick="window.openDialog( '#dialog-language-switcher', '#language-switcher' );">
							{{ $current_currency }} /
							@include( 'icons.globe' )
						</a>
					</li>
					<li class="display-desktop-only">
						<a href="javascript: void(0);" role="button" class="white header-button" onClick="window.openDialog( '#dialog-auth', '#login' );">@lang( 'Log in' )</a>
					</li>
				</ul>
			</nav>
		@endguest
	</div>
</header>
@guest
	@include( 'dialogs.auth', [ 'open' => Request::has( 'auth' ) ] )
@endguest
@include( 'dialogs.language-switcher' )
