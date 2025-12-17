@php
	$default_title       = '3i Visa';
	$default_description = '3i Visa description';
	$default_keywords	 = '3i Visa, 3i-visa.org';
@endphp
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<meta name="color-scheme" content="light dark">

<meta name="csrf-token" content="{{ csrf_token() }}">

{{-- SEO Meta tags --}}
<meta name="description" content="@yield( 'description', $default_description )">
<meta name="keywords" content="@yield( 'keywords', $default_keywords )">

{{-- Open Graph tags --}}
<meta property="og:title" content="@yield( 'title', $default_title )">
<meta property="og:description" content="@yield( 'description', $default_description )">
<meta property="og:image" content="{{ asset( 'images/og-image.png' ) }}">
<meta property="og:url" content="{{ url()->current() }}">
<meta property="og:type" content="website">

{{-- Twitter card tags (TODO: Needs update)
<meta name="twitter:card" content="summary_large_image">
<meta name="twitter:title" content="@yield( 'title', $default_title )">
<meta name="twitter:description" content="@yield( 'description', $default_description )">
<meta name="twitter:image" content="{{ asset('images/social-share-image.png') }}">
 --}}

{{-- Favicon (Add other sizes) --}}
<link rel="icon" href="{{ asset('images/favicon.png') }}" type="image/png">

{{-- CSS and JS --}}
@vite( [ 'resources/scss/app.scss', 'resources/js/app.js' ] )
@stack( 'page-vite' )

<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=Figtree:ital,wght@0,300..900;1,300..900&display=swap" rel="stylesheet">

{{-- HREF lang tags --}}
{!! generate_hreflang_tags() !!}

<title>@yield( 'title', $default_title )</title>

@if ( app()->environment( 'production' ) )
	<!-- Microsoft Clarity -->
	<script type="text/javascript">
		(function(c,l,a,r,i,t,y){
			c[a]=c[a]||function(){(c[a].q=c[a].q||[]).push(arguments)};
			t=l.createElement(r);t.async=1;t.src="https://www.clarity.ms/tag/"+i;
			y=l.getElementsByTagName(r)[0];y.parentNode.insertBefore(t,y);
		})(window, document, "clarity", "script", "ugh0nnrnar");
	</script>
@endif