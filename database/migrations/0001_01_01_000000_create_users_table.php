<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
	/**
	 * Run the migrations.
	 */
	public function up(): void {
		Schema::create( 'users', function ( Blueprint $table ) {
			$table->id();
			$table->enum( 'role', [ 'user', 'admin' ] )->default( 'user' );
			$table->string( 'auth_provider' )->nullable();
			$table->string( 'first_name' )->nullable();
			$table->string( 'last_name' )->nullable();
			$table->string( 'email' )->unique();
			$table->timestamp( 'email_verified_at' )->nullable();
			$table->string( 'password' );
			$table->string( 'locale', 2 )->nullable();
			$table->boolean( 'email_notifications' )->default( true );
			$table->boolean( 'marketing_optin' )->default( false );

			// Laravel Cashier columns
			$table->string( 'stripe_id' )->nullable()->index();
			$table->string( 'pm_type' )->nullable();
			$table->string( 'pm_last_four', 4 )->nullable();
			$table->timestamp( 'trial_ends_at' )->nullable();

			$table->rememberToken();
			$table->timestamps();
		} );

		Schema::create( 'password_reset_tokens', function ( Blueprint $table ) {
			$table->string( 'email' )->primary();
			$table->string( 'token' );
			$table->timestamp( 'created_at' )->nullable();
		} );

		Schema::create( 'sessions', function ( Blueprint $table ) {
			$table->string( 'id' )->primary();
			$table->foreignId( 'user_id' )->nullable()->index();
			$table->string( 'ip_address', 45)->nullable();
			$table->text( 'user_agent' )->nullable();
			$table->longText( 'payload' );
			$table->integer( 'last_activity' )->index();
		} );
	}

	/**
	 * Reverse the migrations.
	 */
	public function down(): void {
		Schema::dropIfExists( 'users' );
		Schema::dropIfExists( 'password_reset_tokens' );
		Schema::dropIfExists( 'sessions' );
	}
};
