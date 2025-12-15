<?php

return [

	/**
	 * Colombia Check-MIG Form Pricing
	 */
	'colombia' => [

		/**
		 * Base form price per traveler (in USD)
		 */
		'base_form_price_usd' => .75,

		/**
		 * Document type name
		 * This will be translated and used in UI (e.g., "Check-MIG Form", "Visa", "eTA")
		 * If not specified, defaults to "Visa"
		 */
		'document_type' => 'Check-MIG Form',

		/**
		 * Allowed nationalities (country codes)
		 * Only these passport holders can apply for this destination
		 */
		'allowed_nationalities' => [ 'br', 'us' ],

		/**
		 * Visa details
		 */
		'visa_details' => [
			'valid_for_days' => 90,
			'valid_for_description' => '90 days after arrival',
			'max_stay_description' => '90 days per entry',
			'entries_description' => 'Single entry',
		],

		/**
		 * Processing time options
		 *
		 * price_usd is the TOTAL additional fee (not per traveler)
		 */
		'processing_options' => [
			'standard' => [
				'name' => 'Standard',
				'description' => '3 day processing',
				'days' => 3,
				'price_usd' => 0,
			],
			// 'rush' => [
			// 	'name' => 'Rush',
			// 	'description' => '1 day processing',
			// 	'days' => 1,
			// 	'price_usd' => 35,
			// ],
		],

		/**
		 * Denial protection
		 *
		 * price_usd is the TOTAL additional fee (not per traveler)
		 */
		'denial_protection' => [
			'price_usd' => 30,
			'description' => 'Get a 100% refund if your application is rejected by the government for any reason.',
		],

	],

];
