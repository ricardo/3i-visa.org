@extends( 'layouts/main' )
@section( 'title', __( 'Home' ) . ' • 3i Visa' )
@section( 'content' )
	<main class="container landing">
		<section class="hero medium">
			<div class="particle-container" id="particle-container"></div>
			<!-- <div class="hero-badge">
				@lang( '' )
			</div> -->
			<h1 id="main-heading" class="color-highlight">
				A maneira <mark>@lang( 'mais rápida' )</mark> de conseguir seu visto de viagem
			</h1>
			<p>
				Lorem ipsum dolor sit amet consectetur
			</p>
			<div class="grid column" accesskey="1">
				<a href="#" role="button" class="contrast">
					@lang( 'Get Started' )
					@include('icons.arrow-right')
				</a>
				<small class="color-muted">
					@lang( 'You are not alone in this journey.' )
				</small>
			</div>
		</section>
	</main>
@endsection
