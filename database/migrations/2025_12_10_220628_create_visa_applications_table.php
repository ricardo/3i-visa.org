<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
	/**
	 * Run the migrations.
	 */
	public function up(): void {
		Schema::create('visa_applications', function (Blueprint $table) {
			$table->id();
			$table->uuid('uuid')->unique();
			$table->foreignId('user_id')->nullable()->constrained()->nullOnDelete();
			$table->string('order_number')->unique();

			// Application status
			$table->enum('status', [
				'draft',
				'pending_payment',
				'paid',
				'processing',
				'approved',
				'rejected',
				'cancelled',
				'refunded'
			])->default('draft')->index();

			// Destination and nationality
			$table->string('destination_country_code', 2);
			$table->string('nationality_country_code', 2);
			$table->integer('number_of_travelers')->unsigned();

			// Processing and upsells
			$table->string('processing_option')->default('standard'); // standard, rush
			$table->boolean('has_denial_protection')->default(false);

			// Pricing in USD (base currency)
			$table->decimal('base_price_usd', 10, 2);
			$table->decimal('processing_fee_usd', 10, 2)->default(0);
			$table->decimal('denial_protection_fee_usd', 10, 2)->default(0);
			$table->decimal('total_amount_usd', 10, 2);

			// Pricing in user's currency (with exchange rate snapshot)
			$table->string('currency_code', 3);
			$table->decimal('total_amount_local', 10, 2);
			$table->decimal('exchange_rate', 16, 8);

			// Contact information
			$table->string('primary_contact_email');
			$table->string('primary_contact_phone')->nullable();

			// Locale and language
			$table->string('locale', 2);

			// Payment integration
			$table->string('stripe_payment_intent_id')->nullable()->index();

			// Country-specific data (flexible JSON storage)
			$table->json('country_specific_data')->nullable();

			// Important dates
			$table->timestamp('submitted_at')->nullable();
			$table->timestamp('paid_at')->nullable();
			$table->timestamp('completed_at')->nullable();
			$table->date('expected_completion_date')->nullable();

			$table->timestamps();

			// Indexes for common queries
			$table->index('created_at');
			$table->index(['user_id', 'status']);
			$table->index('primary_contact_email');
		});
	}

	/**
	 * Reverse the migrations.
	 */
	public function down(): void {
		Schema::dropIfExists('visa_applications');
	}
};
