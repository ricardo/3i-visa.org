<?php

namespace App\Console\Commands;

use App\Services\CurrencyService;
use Illuminate\Console\Command;

class UpdateExchangeRates extends Command {
	/**
	 * The name and signature of the console command.
	 *
	 * @var string
	 */
	protected $signature = 'currency:update
							{--force : Force update even if rates are not stale}';

	/**
	 * The console command description.
	 *
	 * @var string
	 */
	protected $description = 'Update exchange rates from exchangerate-api.io';

	/**
	 * The currency service instance.
	 *
	 * @var CurrencyService
	 */
	protected $currencyService;

	/**
	 * Create a new command instance.
	 *
	 * @param CurrencyService $currencyService
	 */
	public function __construct( CurrencyService $currencyService ) {
		parent::__construct();
		$this->currencyService = $currencyService;
	}

	/**
	 * Execute the console command.
	 */
	public function handle() {
		$this->info( 'Updating exchange rates...' );

		// Check if update is needed (unless forced)
		if ( ! $this->option( 'force' ) ) {
			if ( ! $this->currencyService->hasStaleRates() ) {
				$lastUpdate = $this->currencyService->getLastUpdateTime();
				$this->info( "Exchange rates are up to date (last updated: {$lastUpdate->diffForHumans()})." );
				return 0;
			}
		}

		// Update rates from API
		$success = $this->currencyService->updateRates();

		if ( $success ) {
			$lastUpdate = $this->currencyService->getLastUpdateTime();
			$this->info( '✓ Exchange rates updated successfully!' );
			$this->info( "Last updated: {$lastUpdate}" );
			return 0;
		} else {
			$this->error( '✗ Failed to update exchange rates. Check the logs for details.' );
			return 1;
		}
	}
}
