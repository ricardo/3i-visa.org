<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Traveler extends Model {
	/**
	 * The attributes that are mass assignable.
	 *
	 * @var array<string>
	 */
	protected $fillable = [
		'visa_application_id',
		'traveler_index',
		'is_primary_contact',
		'first_name',
		'last_name',
		'email',
		'date_of_birth',
		'nationality_country_code',
		'passport_number',
		'passport_expiration_date',
		'add_passport_later',
		'additional_data',
	];

	/**
	 * The attributes that should be cast.
	 *
	 * @var array<string, string>
	 */
	protected $casts = [
		'is_primary_contact' => 'boolean',
		'date_of_birth' => 'date',
		'passport_expiration_date' => 'date',
		'add_passport_later' => 'boolean',
		'additional_data' => 'array',
	];

	/**
	 * Get the visa application that owns the traveler.
	 */
	public function visaApplication(): BelongsTo {
		return $this->belongsTo( VisaApplication::class );
	}

	/**
	 * Get the traveler's full name.
	 */
	public function getFullNameAttribute(): string {
		return "{$this->first_name} {$this->last_name}";
	}

	/**
	 * Check if passport information has been provided.
	 */
	public function hasPassportInfo(): bool {
		return ! empty( $this->passport_number ) && ! empty( $this->passport_expiration_date );
	}

	/**
	 * Scope to get only primary contacts.
	 */
	public function scopePrimaryContacts( $query ) {
		return $query->where( 'is_primary_contact', true );
	}
}
