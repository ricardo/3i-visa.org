@php
	$user   = auth()->getUser();
@endphp
<header>
	<div class="container">
		<a href="{{ route( 'home' ) }}" class="logo">
			<img src="{{ asset( 'images/logo.webp' ) }}" alt="3i Visa">
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
						<a href="javascript: void(0);" role="button" class="contrast outline header-button translate-button" onClick="window.openDialog( '#dialog-auth', '#login' );">
							USD /
							@include( 'icons.globe' )
						</a>
					</li>
					<li class="display-desktop-only">
						<a href="javascript: void(0);" role="button" class="contrast outline header-button" onClick="window.openDialog( '#dialog-auth', '#login' );">@lang( 'Log in' )</a>
					</li>
				</ul>
			</nav>
		@endguest
	</div>
</header>
@guest
	@include( 'dialogs.auth', [ 'open' => Request::has( 'auth' ) ] )
@endguest
