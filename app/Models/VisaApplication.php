<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Support\Str;

class VisaApplication extends Model {
    /**
     * The attributes that are mass assignable.
     *
     * @var array<string>
     */
    protected $fillable = [
        'uuid',
        'user_id',
        'session_id',
        'order_number',
        'status',
        'destination_country_code',
        'nationality_country_code',
        'number_of_travelers',
        'processing_option',
        'has_denial_protection',
        'base_price_usd',
        'processing_fee_usd',
        'denial_protection_fee_usd',
        'total_amount_usd',
        'currency_code',
        'total_amount_local',
        'exchange_rate',
        'primary_contact_email',
        'primary_contact_phone',
        'locale',
        'stripe_payment_intent_id',
        'country_specific_data',
        'submitted_at',
        'paid_at',
        'completed_at',
        'expected_completion_date',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'has_denial_protection' => 'boolean',
        'base_price_usd' => 'decimal:2',
        'processing_fee_usd' => 'decimal:2',
        'denial_protection_fee_usd' => 'decimal:2',
        'total_amount_usd' => 'decimal:2',
        'total_amount_local' => 'decimal:2',
        'exchange_rate' => 'decimal:8',
        'country_specific_data' => 'array',
        'submitted_at' => 'datetime',
        'paid_at' => 'datetime',
        'completed_at' => 'datetime',
        'expected_completion_date' => 'date',
    ];

    /**
     * Boot the model.
     */
    protected static function boot() {
        parent::boot();

        // Generate UUID and order number on creation
        static::creating(function ($application) {
            if (empty($application->uuid)) {
                $application->uuid = (string) Str::uuid();
            }
            if (empty($application->order_number)) {
                $application->order_number = static::generateOrderNumber();
            }
        });

        // Log status changes
        static::updated(function ($application) {
            if ($application->isDirty('status')) {
                $application->statusLogs()->create([
                    'from_status' => $application->getOriginal('status'),
                    'to_status' => $application->status,
                    'changed_by_user_id' => auth()->id(),
                ]);
            }
        });
    }

    /**
     * Generate a unique order number.
     *
     * @return string
     */
    protected static function generateOrderNumber(): string {
        $year = date('Y');
        $lastOrder = static::whereYear('created_at', $year)
            ->orderBy('id', 'desc')
            ->first();

        $sequence = $lastOrder ? intval(substr($lastOrder->order_number, -6)) + 1 : 1;

        return sprintf('VA-%s-%06d', $year, $sequence);
    }

    /**
     * Get the user that owns the application.
     */
    public function user(): BelongsTo {
        return $this->belongsTo(User::class);
    }

    /**
     * Get the travelers for the application.
     */
    public function travelers(): HasMany {
        return $this->hasMany(Traveler::class)->orderBy('traveler_index');
    }

    /**
     * Get the primary contact traveler.
     */
    public function primaryContact(): HasOne {
        return $this->hasOne(Traveler::class)->where('is_primary_contact', true);
    }

    /**
     * Get the status change logs for the application.
     */
    public function statusLogs(): HasMany {
        return $this->hasMany(ApplicationStatusLog::class)->orderBy('created_at', 'desc');
    }

    /**
     * Scope to filter by status.
     */
    public function scopeStatus($query, $status) {
        return $query->where('status', $status);
    }

    /**
     * Scope to filter by user.
     */
    public function scopeForUser($query, $userId) {
        return $query->where('user_id', $userId);
    }

    /**
     * Scope to filter drafts.
     */
    public function scopeDrafts($query) {
        return $query->where('status', 'draft');
    }

    /**
     * Scope to filter paid applications.
     */
    public function scopePaid($query) {
        return $query->whereIn('status', ['paid', 'processing', 'approved', 'completed']);
    }

    /**
     * Check if the application is in draft status.
     */
    public function isDraft(): bool {
        return $this->status === 'draft';
    }

    /**
     * Check if the application has been paid.
     */
    public function isPaid(): bool {
        return in_array($this->status, ['paid', 'processing', 'approved', 'completed']);
    }

    /**
     * Check if the application is completed.
     */
    public function isCompleted(): bool {
        return $this->status === 'completed';
    }
}
