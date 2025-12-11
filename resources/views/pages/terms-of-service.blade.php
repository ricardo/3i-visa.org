@extends( 'layouts.main' )
@section( 'title', __( 'Terms of Service' ) )
@section( 'content' )
	<section class="legal-page">
		<div class="container">

			<h1>{{ __('Terms of Service') }}</h1>

			<p>
				{{ __('Welcome to 3i-visa.org (“we,” “us,” or “our”). These Terms of Service (“Terms”) govern your use of our website, tools, and related services designed to assist with travel document applications (collectively, the “Service”).') }}
			</p>

			<p>
				{{ __('By accessing or using the Service, you agree to these Terms as well as our Privacy Policy. If you do not agree, you must discontinue use of the Service immediately.') }}
			</p>

			<h2>1. {{ __('About Our Service') }}</h2>
			<p>{{ __('3i-visa.org is an online platform that helps users prepare and submit travel documentation, including visa applications, electronic travel authorizations, and related forms. We provide support, guidance, and document handling to simplify the application process.') }}</p>
			<p>{{ __('We are an independent private service and are not affiliated with any government, embassy, or regulatory authority. All final decisions regarding visa approval or denial are made solely by the issuing government.') }}</p>
			<p>{{ __('Our service fee is displayed during checkout and does not include government or consular fees unless explicitly stated. Government fees vary by country and document type.') }}</p>

			<h2>2. {{ __('User Responsibilities') }}</h2>
			<p>{{ __('By using the Service, you agree to:') }}</p>

			<ul>
				<li>{{ __('Provide complete, accurate, and truthful information. Incorrect or incomplete data may result in delays or denial of your application.') }}</li>
				<li>{{ __('Verify all information and documents provided in your final application once received from us.') }}</li>
				<li>{{ __('Ensure your passport and required documents meet the criteria set by the issuing authority.') }}</li>
				<li>{{ __('Comply with the entry laws and regulations of both your destination and any transit countries.') }}</li>
			</ul>

			<h2>3. {{ __('Eligibility') }}</h2>
			<p>{{ __('To use the Service, you must be at least 18 years old or have permission from a parent or legal guardian. You must also provide a valid payment method and use the Service only for lawful purposes.') }}</p>

			<h2>4. {{ __('Payments and Refunds') }}</h2>
			<p>{{ __('Service fees are charged at the time of application. All payments are processed securely through third-party payment providers.') }}</p>
			<p>{{ __('Government fees, when applicable, are separate from our service fee unless otherwise specified.') }}</p>

			<h3>{{ __('Refund Policy') }}</h3>
			<p>{{ __('Refunds may be granted if:') }}</p>
			<ul>
				<li>{{ __('Your application has not yet been processed by our team; or') }}</li>
				<li>{{ __('An error was made by us while handling your application.') }}</li>
			</ul>

			<p>{{ __('Refunds are not available if:') }}</p>
			<ul>
				<li>{{ __('Your application has already been submitted to a government system; or') }}</li>
				<li>{{ __('The issuing authority rejects or delays your application for any reason.') }}</li>
			</ul>

			<h2>5. {{ __('Use of the Service') }}</h2>
			<p>{{ __('By using the Service, you agree to receive essential communications related to your application, including email updates and status notifications. These messages are required for proper functionality of the Service.') }}</p>

			<h3>{{ __('Prohibited Activities') }}</h3>
			<p>{{ __('You may not:') }}</p>
			<ul>
				<li>{{ __('Submit false, misleading, or fraudulent information.') }}</li>
				<li>{{ __('Attempt to interfere with or bypass website security features.') }}</li>
				<li>{{ __('Use the Service to facilitate unlawful travel or entry into any country.') }}</li>
			</ul>

			<h2>6. {{ __('Service Limitations') }}</h2>
			<p>{{ __('While we aim to provide accurate guidance and timely assistance, we do not control government decisions. Approval is not guaranteed.') }}</p>
			<p>{{ __('Processing times displayed on the website are estimates and may vary based on the issuing authority.') }}</p>

			<h2>7. {{ __('Privacy and Data Protection') }}</h2>
			<p>{{ __('We collect and process your information in accordance with our Privacy Policy. By using the Service, you consent to the processing of your personal data as described therein.') }}</p>

			<h2>8. {{ __('Intellectual Property') }}</h2>
			<p>{{ __('All content available on 3i-visa.org, including text, graphics, images, and software, is the property of 3i-visa.org or its licensors and is protected by applicable intellectual property laws. You may not reproduce, distribute, or modify any material without written permission.') }}</p>

			<h2>9. {{ __('Limitation of Liability') }}</h2>
			<p>{{ __('To the fullest extent permitted by law, we are not liable for any direct, indirect, incidental, or consequential damages arising from your use of the Service.') }}</p>
			<p>{{ __('Our maximum liability is limited to the total service fee paid by you for the specific application in question.') }}</p>

			<h2>10. {{ __('Dispute Resolution') }}</h2>
			<p>{{ __('Any disputes arising from these Terms will be governed by applicable international contract principles. You agree to attempt informal resolution by contacting us before pursuing legal action.') }}</p>

			<h2>11. {{ __('Modifications to Terms or Service') }}</h2>
			<p>{{ __('We may update, modify, or discontinue the Service or these Terms at any time. Changes will be posted on this page, and continued use of the Service constitutes acceptance of the revised Terms.') }}</p>

			<h2>12. {{ __('Contact Information') }}</h2>
			<p>{{ __('For support or questions regarding these Terms, you may contact us at:') }}</p>
			<p><strong>Email:</strong> <a href="mailto:support@3i-visa.org">support@3i-visa.org</a></p>

			<h2>13. {{ __('Entire Agreement') }}</h2>
			<p>{{ __('These Terms, together with our Privacy Policy, represent the complete agreement between you and 3i-visa.org regarding your use of the Service.') }}</p>

		</div>
	</section>
@endsection
