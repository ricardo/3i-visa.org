@extends( 'layouts.main' )
@section( 'title', __( 'Privacy Policy' ) )
@section( 'content' )
	<section class="legal-page">
		<div class="container">
			@include( 'pages.privacy-policy.' . app()->getLocale() )
		</div>
	</section>
@endsection
