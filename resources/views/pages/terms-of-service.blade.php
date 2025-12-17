@extends( 'layouts.main' )
@section( 'title', __( 'Terms of Service' ) )
@section( 'content' )
	<section class="legal-page">
		<div class="container">
			@include( 'pages.terms-of-service.' . app()->getLocale() )
		</div>
	</section>
@endsection
