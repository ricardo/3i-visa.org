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
	 * Create a new notification instance.
	 *
	 * @param string $token
	 */
	public function __construct( $token ) {
		$this->token = $token;
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
		// Build password reset URL
		$reset_url = url( route( 'password.reset', [
			'token' => $this->token,
			'email' => $notifiable->email,
		], false ) );

		return ( new MailMessage )
			->subject( __( 'Reset Your Password - 3i Visa' ) )
			->greeting( __( 'Hello!' ) )
			->line( __( 'You are receiving this email because we received a password reset request for your account.' ) )
			->action( __( 'Reset Password' ), $reset_url )
			->line( __( 'This password reset link will expire in :count minutes.', [ 'count' => config( 'auth.passwords.' . config( 'auth.defaults.passwords' ) . '.expire', 60 ) ] ) )
			->line( __( 'If you did not request a password reset, no further action is required.' ) );
	}
}
