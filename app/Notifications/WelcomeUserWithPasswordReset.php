<?php

namespace App\Notifications;

use App\Models\VisaApplication;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
use Illuminate\Support\Facades\Password;

class WelcomeUserWithPasswordReset extends Notification implements ShouldQueue {
	use Queueable;

	/**
	 * The visa application that was just paid.
	 *
	 * @var \App\Models\VisaApplication
	 */
	protected $application;

	/**
	 * Create a new notification instance.
	 *
	 * @param \App\Models\VisaApplication $application
	 */
	public function __construct( VisaApplication $application ) {
		$this->application = $application;
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
		// Generate password reset token
		$token = Password::broker()->createToken( $notifiable );

		// Build password reset URL
		$reset_url = url( route( 'password.reset', [
			'token' => $token,
			'email' => $notifiable->email,
		], false ) );

		// Get country name
		$country_name = \App\Helpers\Countries::getCountryName( $this->application->destination_country_code );

		return ( new MailMessage )
			->subject( __( 'Welcome to 3i Visa - Your Order Confirmation' ) )
			->greeting( __( 'Welcome to 3i Visa!' ) )
			->line( __( 'Thank you for your order! We have created an account for you to track your visa application.' ) )
			->line( __( 'Your order details:' ) )
			->line( '**' . __( 'Order Number' ) . ':** ' . $this->application->order_number )
			->line( '**' . __( 'Destination' ) . ':** ' . $country_name )
			->line( '**' . __( 'Number of Travelers' ) . ':** ' . $this->application->number_of_travelers )
			->line( __( 'To set your account password and access your order details, please click the button below:' ) )
			->action( __( 'Set Your Password' ), $reset_url )
			->line( __( 'Once you set your password, you can log in to view your application status and download your documents when ready.' ) )
			->line( __( 'If you did not create this account, please ignore this email.' ) );
	}
}
