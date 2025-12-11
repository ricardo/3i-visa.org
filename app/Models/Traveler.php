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
	 * The accessors to append to the model's array form.
	 *
	 * @var array<string>
	 */
	protected $appends = [
		'date_of_birth_month',
		'date_of_birth_day',
		'date_of_birth_year',
		'passport_expiration_month',
		'passport_expiration_day',
		'passport_expiration_year',
		'marketing_optin',
		'nationality',
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
	 * Get the date of birth month.
	 */
	public function getDateOfBirthMonthAttribute(): ?int {
		return $this->date_of_birth ? $this->date_of_birth->month : null;
	}

	/**
	 * Get the date of birth day.
	 */
	public function getDateOfBirthDayAttribute(): ?int {
		return $this->date_of_birth ? $this->date_of_birth->day : null;
	}

	/**
	 * Get the date of birth year.
	 */
	public function getDateOfBirthYearAttribute(): ?int {
		return $this->date_of_birth ? $this->date_of_birth->year : null;
	}

	/**
	 * Get the passport expiration month.
	 */
	public function getPassportExpirationMonthAttribute(): ?int {
		return $this->passport_expiration_date ? $this->passport_expiration_date->month : null;
	}

	/**
	 * Get the passport expiration day.
	 */
	public function getPassportExpirationDayAttribute(): ?int {
		return $this->passport_expiration_date ? $this->passport_expiration_date->day : null;
	}

	/**
	 * Get the passport expiration year.
	 */
	public function getPassportExpirationYearAttribute(): ?int {
		return $this->passport_expiration_date ? $this->passport_expiration_date->year : null;
	}

	/**
	 * Get the marketing optin value from additional_data.
	 */
	public function getMarketingOptinAttribute(): bool {
		return $this->additional_data['marketing_optin'] ?? false;
	}

	/**
	 * Get the nationality (lowercase country code for UI compatibility).
	 */
	public function getNationalityAttribute(): ?string {
		return $this->nationality_country_code ? strtolower( $this->nationality_country_code ) : null;
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
