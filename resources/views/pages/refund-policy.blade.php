@extends( 'layouts.main' )
@section( 'title', __( 'Refund Policy' ) )
@section( 'content' )
	<section class="legal-page">
		<div class="container">
			@include( 'pages.refund-policy.' . app()->getLocale() )
		</div>
	</section>
@endsection
