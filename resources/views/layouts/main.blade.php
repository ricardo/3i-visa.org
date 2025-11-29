<!doctype html>
<html lang="{{ app()->getLocale() }}" data-theme="light">
	<head>
		@include( 'partials.head' )

		@livewireStyles
	</head>

	<body class="@if ( ! empty( $grid_body ) ) grid @endif @if ( ! empty( $active_chat ) ) active-chat @endif">
		@include( 'partials.header' )

		<div class="layout">

			@yield( 'content' )

			@if ( ! isset( $footer ) || $footer )
				{{-- @include( 'partials.footer' )  --}}
			@endif
		</div>

		@include( 'partials.scripts' )
		@livewireScripts
	</body>
</html>
