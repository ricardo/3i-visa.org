<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
	/**
	 * Run the migrations.
	 */
	public function up(): void {
		Schema::create('travelers', function (Blueprint $table) {
			$table->id();
			$table->foreignId('visa_application_id')->constrained()->cascadeOnDelete();
			$table->integer('traveler_index')->unsigned();
			$table->boolean('is_primary_contact')->default(false);

			// Personal information
			$table->string('first_name');
			$table->string('last_name');
			$table->string('email')->nullable();
			$table->date('date_of_birth');

			// Passport information
			$table->string('nationality_country_code', 2);
			$table->string('passport_number')->nullable();
			$table->date('passport_expiration_date')->nullable();
			$table->boolean('add_passport_later')->default(false);

			// Country-specific additional data
			$table->json('additional_data')->nullable();

			$table->timestamps();

			// Indexes
			$table->index(['visa_application_id', 'traveler_index']);
		});
	}

	/**
	 * Reverse the migrations.
	 */
	public function down(): void {
		Schema::dropIfExists('travelers');
	}
};
