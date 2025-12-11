@extends( 'layouts.main' )
@section( 'title', __( 'Refund Policy' ) )
@section( 'content' )
	<section class="legal-page">
		<div class="container">

			<h1>{{ __( 'Refund Policy' ) }}</h1>
			<p><strong>{{ __( 'Effective Date:' ) }}</strong> 10th of December 2025</p>

			<h2>{{ __( '1. Introduction' ) }}</h2>
			<p>
				{{ __( 'We aim to provide a clear, fair, and transparent refund process. This Refund Policy explains the conditions under which refunds may be issued for services purchased through our platform.' ) }}
			</p>

			<h2>{{ __( '2. Right to Request a Refund' ) }}</h2>
			<ul>
				<li>{{ __( 'You may request a refund within 14 days of placing your order.' ) }}</li>
				<li>{{ __( 'Refund requests must be submitted by contacting us at' ) }} <a href="mailto:support@3i-visa.org">support@3i-visa.org</a>.</li>
			</ul>

			<h2>{{ __( '3. Refund Eligibility' ) }}</h2>
			<p>{{ __( 'Refunds may be granted under the following conditions:' ) }}</p>

			<h3>{{ __( 'Full Refund – Before Processing Begins' ) }}</h3>
			<p>
				{{ __( 'If you cancel your application before our team begins processing, you are eligible for a full refund of the service fee.' ) }}
			</p>

			<h3>{{ __( 'Partial Refund – After Processing Has Started' ) }}</h3>
			<p>
				{{ __( 'If processing has already begun, a partial refund of 50% may be issued when the application cannot be completed due to factors outside of your control. If your application requires corrections or additional information, we will reprocess your application at no additional cost rather than issuing a refund.' ) }}
			</p>

			<h3>{{ __( 'No Refund – After Completed Processing' ) }}</h3>
			<p>
				{{ __( 'Once your application has been completed and the relevant documentation has been delivered to you, refunds cannot be provided, as the service has already been fulfilled.' ) }}
			</p>

			<h2>{{ __( '4. Refund Processing Time' ) }}</h2>
			<p>
				{{ __( 'Approved refunds will be issued to the original payment method within 5–10 business days, depending on your financial institution.' ) }}
			</p>

			<h2>{{ __( '5. Non-Refundable Situations' ) }}</h2>
			<p>{{ __( 'Refunds will not be granted in the following situations:' ) }}</p>
			<ul>
				<li>{{ __( 'The service has been fully completed and delivered.' ) }}</li>
				<li>{{ __( 'Delays or decisions made by government or immigration authorities.' ) }}</li>
				<li>{{ __( 'Failure to provide accurate information or required documentation.' ) }}</li>
			</ul>

			<h2>{{ __( '6. Disputes & Chargeback Prevention' ) }}</h2>
			<p>
				{{ __( 'If you encounter any issue with your order, please contact us first at' ) }}
				<a href="mailto:support@3i-visa.org">support@3i-visa.org</a>
				{{ __( 'before filing a chargeback. Many issues can be resolved quickly with our support team.' ) }}
			</p>

			<h2>{{ __( '7. Changes to This Policy' ) }}</h2>
			<p>
				{{ __( 'We may update this Refund Policy at any time. Any modifications take effect immediately upon being posted on this page.' ) }}
			</p>

		</div>
	</section>
@endsection
