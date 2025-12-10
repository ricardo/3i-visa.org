<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration {
	/**
	 * Run the migrations.
	 */
	public function up(): void {
		Schema::create('exchange_rates', function (Blueprint $table) {
			$table->id();
			$table->string('currency_code', 3)->unique(); // USD, BRL, COP
			$table->decimal('rate', 16, 8); // Exchange rate (e.g., 5.12345678)
			$table->string('base_currency', 3)->default('USD'); // Base currency (always USD)
			$table->timestamp('last_updated_at')->nullable(); // When rate was last fetched from API
			$table->timestamps();

			// Index for fast lookups
			$table->index('currency_code');
		});

		// Seed with initial hardcoded rates as fallback
		DB::table('exchange_rates')->insert([
			[
				'currency_code' => 'USD',
				'rate' => 1.00000000,
				'base_currency' => 'USD',
				'last_updated_at' => now(),
				'created_at' => now(),
				'updated_at' => now(),
			],
			[
				'currency_code' => 'BRL',
				'rate' => 5.00000000,
				'base_currency' => 'USD',
				'last_updated_at' => now(),
				'created_at' => now(),
				'updated_at' => now(),
			],
			[
				'currency_code' => 'COP',
				'rate' => 4000.00000000,
				'base_currency' => 'USD',
				'last_updated_at' => now(),
				'created_at' => now(),
				'updated_at' => now(),
			],
		]);
	}

	/**
	 * Reverse the migrations.
	 */
	public function down(): void {
		Schema::dropIfExists('exchange_rates');
	}
};
