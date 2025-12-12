@php
	$user             = auth()->getUser();
	$current_currency = request()->attributes->get( 'current_currency' ) ?? request()->cookie( 'preferred_currency', 'USD' );
@endphp
<header>
	<div class="container">
		<a href="{{ route( 'home' ) }}" class="logo">
			<img src="{{ asset( 'images/logo.svg' ) }}" alt="3i Visa">
		</a>

		@guest
			<div class="header-actions">
				<a href="javascript: void(0);" role="button" class="white header-button translate-button display-desktop-only" onClick="window.openDialog( '#dialog-language-switcher', '#language-switcher' );">
					<!-- {{ $current_currency }} / -->
					@include( 'icons.globe' )
				</a>

				<a href="{{ route( 'login' ) }}" role="button" class="white header-button">@lang( 'Log in' )</a>

				<button id="menu-toggle" class="nav-toggle display-mobile-only" aria-label="Open menu">
					<span></span>
					<span></span>
					<span></span>
				</button>
			</div>
		@endguest

		@auth
			<div class="header-actions">
				<a href="javascript: void(0);" role="button" class="white header-button translate-button display-desktop-only" onClick="window.openDialog( '#dialog-language-switcher', '#language-switcher' );">
					<!-- {{ $current_currency }} / -->
					@include( 'icons.globe' )
				</a>

				<!-- <a href="{{ route( 'account' ) }}" role="button" class="white header-button user-button">
					@include( 'icons.user' )
					<span>{{ Str::limit(auth()->user()->first_name, 15, '') }}</span>
				</a> -->

				<details class="dropdown button-dropdown">
					<summary role="button" class="white header-button user-button">
						@include( 'icons.user' )
						<span>{{ Str::limit(auth()->user()->first_name, 15, '') }}</span>
					</summary>
					<ul class="top right">
						<li>
							<a href="{{ route( 'account' ) }}" class="contrast">
								@lang( 'My Orders' )
							</a>
						</li>
						<li>
							<a href="{{ route( 'logout' ) }}" class="contrast">
								@lang( 'Logout' )
							</a>
						</li>
					</ul>
				</details>

				<button id="menu-toggle" class="nav-toggle display-mobile-only" aria-label="Open menu">
					<span></span>
					<span></span>
					<span></span>
				</button>
			</div>
		@endauth
	</div>

	<!-- Mobile Menu -->
	@guest
		<div class="mobile-menu">
			<div class="mobile-menu-backdrop"></div>
			<div class="mobile-menu-content">
				<button class="menu-close" aria-label="Close menu">
					<svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
						<line x1="18" y1="6" x2="6" y2="18"></line>
						<line x1="6" y1="6" x2="18" y2="18"></line>
					</svg>
				</button>

				<nav class="mobile-menu-nav">
					<a href="javascript: void(0);" role="button" class="mobile-menu-item" onClick="window.openDialog( '#dialog-language-switcher', '#language-switcher' );">
						{{ $current_currency }} /
						@include( 'icons.globe' )
					</a>
				</nav>
			</div>
		</div>
	@endguest

	@auth
		<div class="mobile-menu">
			<div class="mobile-menu-backdrop"></div>
			<div class="mobile-menu-content">
				<button class="menu-close" aria-label="Close menu">
					<svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
						<line x1="18" y1="6" x2="6" y2="18"></line>
						<line x1="6" y1="6" x2="18" y2="18"></line>
					</svg>
				</button>

				<nav class="mobile-menu-nav">
					<a href="javascript: void(0);" role="button" class="mobile-menu-item" onClick="window.openDialog( '#dialog-language-switcher', '#language-switcher' );">
						{{ $current_currency }} /
						@include( 'icons.globe' )
					</a>
					<a href="{{ route( 'logout' ) }}" class="mobile-menu-item">
						@lang( 'Logout' )
					</a>
				</nav>
			</div>
		</div>
	@endauth
</header>
@include( 'dialogs.language-switcher' )
