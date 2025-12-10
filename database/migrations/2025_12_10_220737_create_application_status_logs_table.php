<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
	/**
	 * Run the migrations.
	 */
	public function up(): void {
		Schema::create('application_status_logs', function (Blueprint $table) {
			$table->id();
			$table->foreignId('visa_application_id')->constrained()->cascadeOnDelete();
			$table->string('from_status')->nullable();
			$table->string('to_status');
			$table->foreignId('changed_by_user_id')->nullable()->constrained('users')->nullOnDelete();
			$table->text('notes')->nullable();
			$table->timestamp('created_at');

			// Indexes
			$table->index(['visa_application_id', 'created_at']);
		});
	}

	/**
	 * Reverse the migrations.
	 */
	public function down(): void {
		Schema::dropIfExists('application_status_logs');
	}
};
