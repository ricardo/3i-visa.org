@php
	$current_currency = request()->cookie( 'preferred_currency', 'USD' );
@endphp
<footer>
	<div class="container">
		<nav class="footer-links">
			<a href="{{ route( 'terms' ) }}">@lang( 'Terms of Service' )</a>
			<a href="{{ route( 'privacy' ) }}">@lang( 'Privacy Policy' )</a>
			<a href="javascript: void(0);" role="button" class="white footer-currency-button" onClick="window.openDialog( '#dialog-language-switcher', '#language-switcher' );">
				{{ $current_currency }} /
				@include( 'icons.globe' )
			</a>
		</nav>

		<div class="footer-copyright">
			&copy; {{ date( 'Y' ) }} 3i Visa. @lang( 'All rights reserved.' )
		</div>
	</div>
</footer>
