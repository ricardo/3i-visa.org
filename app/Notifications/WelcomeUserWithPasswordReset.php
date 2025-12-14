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
	 * The locale for this notification.
	 *
	 * @var string
	 */
	public $locale;

	/**
	 * Create a new notification instance.
	 *
	 * @param \App\Models\VisaApplication $application
	 */
	public function __construct( VisaApplication $application ) {
		$this->application = $application;
		// Store the application's locale when the notification is created
		$this->locale = $application->locale ?? app()->getLocale();
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

		// Generate password reset token
		$token = Password::broker()->createToken( $notifiable );

		// Build password reset URL with locale prefix
		$locale_prefix = ( $user_locale && $user_locale !== 'en' ) ? $user_locale . '/' : '';
		$reset_url = url( $locale_prefix . 'password/reset/' . $token . '?email=' . urlencode( $notifiable->email ) );

		// Get country name
		$country_name = \App\Helpers\Countries::getCountryName( $this->application->destination_country_code );

		$message = ( new MailMessage )
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

		return $message;
	}
}
