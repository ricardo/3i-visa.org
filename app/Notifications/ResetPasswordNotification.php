<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class ResetPasswordNotification extends Notification implements ShouldQueue {
	use Queueable;

	/**
	 * The password reset token.
	 *
	 * @var string
	 */
	protected $token;

	/**
	 * The locale for this notification.
	 *
	 * @var string
	 */
	public $locale;

	/**
	 * Create a new notification instance.
	 *
	 * @param string $token
	 */
	public function __construct( $token ) {
		$this->token = $token;
		// Store the current locale when the notification is created
		$this->locale = app()->getLocale();
	}

	/**
	 * Get the notification's delivery channels.
	 *
	 * @param mixed $notifiable
	 * @return array
	 */
	public function via( $notifiable ) {
		return [ 'mail' ];
	}

	/**
	 * Get the mail representation of the notification.
	 *
	 * @param mixed $notifiable
	 * @return \Illuminate\Notifications\Messages\MailMessage
	 */
	public function toMail( $notifiable ) {
		// Determine the locale to use (user's locale, notification's locale, or fallback to 'en')
		$user_locale = $notifiable->locale ?? $this->locale ?? 'en';

		// Set the application locale for rendering
		app()->setLocale( $user_locale );

		// Build password reset URL with locale prefix
		$locale_prefix = ( $user_locale && $user_locale !== 'en' ) ? $user_locale . '/' : '';
		$reset_url = url( $locale_prefix . 'password/reset/' . $this->token . '?email=' . urlencode( $notifiable->email ) );

		$message = ( new MailMessage )
			->subject( __( 'Reset Your Password - 3i Visa' ) )
			->greeting( __( 'Hello!' ) )
			->line( __( 'You are receiving this email because we received a password reset request for your account.' ) )
			->action( __( 'Reset Password' ), $reset_url )
			->line( __( 'This password reset link will expire in :count minutes.', [ 'count' => config( 'auth.passwords.' . config( 'auth.defaults.passwords' ) . '.expire', 60 ) ] ) )
			->line( __( 'If you did not request a password reset, no further action is required.' ) );

		return $message;
	}
}
