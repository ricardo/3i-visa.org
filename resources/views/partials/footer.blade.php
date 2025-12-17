@php
	$current_currency = request()->attributes->get( 'current_currency' ) ?? request()->cookie( 'preferred_currency', 'USD' );
@endphp
<footer>
	<div class="container">
		<nav class="footer-links">
			<a href="{{ route( 'terms-of-service' ) }}">@lang( 'Terms of Service' )</a>
			<a href="{{ route( 'privacy-policy' ) }}">@lang( 'Privacy Policy' )</a>
			<a href="{{ route( 'refund-policy' ) }}">@lang( 'Refund Policy' )</a>
			<a href="javascript: void(0);" role="button" class="white footer-currency-button" onClick="window.openDialog( '#dialog-language-switcher', '#language-switcher' );">
				{{ $current_currency }} /
				@include( 'icons.globe' )
			</a>
		</nav>

		<div class="footer-disclaimer">
			@lang( '3i Visa is a private, independent service agency. We assist travelers by providing support for completing and submitting various travel forms. Our service includes form assistance, customer support, and error checking.' )
		</div>

		<div class="footer-copyright">
			&copy; {{ date( 'Y' ) }} 3i Visa. @lang( 'All rights reserved.' )
		</div>
	</div>
</footer>
