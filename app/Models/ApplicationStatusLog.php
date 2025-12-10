<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ApplicationStatusLog extends Model {
	/**
	 * Indicates if the model should be timestamped.
	 *
	 * @var bool
	 */
	public $timestamps = false;

	/**
	 * The attributes that are mass assignable.
	 *
	 * @var array<string>
	 */
	protected $fillable = [
		'visa_application_id',
		'from_status',
		'to_status',
		'changed_by_user_id',
		'notes',
	];

	/**
	 * The attributes that should be cast.
	 *
	 * @var array<string, string>
	 */
	protected $casts = [
		'created_at' => 'datetime',
	];

	/**
	 * Get the visa application that this log belongs to.
	 */
	public function visaApplication(): BelongsTo {
		return $this->belongsTo( VisaApplication::class );
	}

	/**
	 * Get the user who changed the status.
	 */
	public function changedBy(): BelongsTo {
		return $this->belongsTo( User::class, 'changed_by_user_id' );
	}
}
