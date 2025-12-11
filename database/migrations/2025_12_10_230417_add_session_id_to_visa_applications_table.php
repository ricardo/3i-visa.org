<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
	/**
	 * Run the migrations.
	 */
	public function up(): void {
		Schema::table( 'visa_applications', function ( Blueprint $table ) {
			if ( ! Schema::hasColumn( 'visa_applications', 'session_id' ) ) {
				$table->string( 'session_id' )->nullable()->after( 'user_id' );
			}
			$table->index( [ 'session_id', 'destination_country_code', 'status' ], 'va_session_country_status_idx' );
		} );
	}

	/**
	 * Reverse the migrations.
	 */
	public function down(): void {
		Schema::table( 'visa_applications', function ( Blueprint $table ) {
			$table->dropIndex( 'va_session_country_status_idx' );
			$table->dropColumn( 'session_id' );
		} );
	}
};
