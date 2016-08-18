<?php

class Amount implements ValidationHelper {

	public function validate( GatewayType $adapter, $normalized, &$errors ) {
		if (
			!isset( $normalized['amount'] ) ||
			!isset( $normalized['currency_code'] )
		) {
			// Not enough info to validate
			return;
		}
		if ( isset( $errors['currency_code'] ) ) {
			// Already displaying an error
			return;
		}
		$value = $normalized['amount'];

		if ( self::isZeroIsh( $value ) ) {
			$errors['amount'] = DataValidator::getErrorMessage(
				'amount',
				'not_empty',
				$normalized['language']
			);
			return;
		}
		$currency = $normalized['currency_code'];
		$min = self::convert( $adapter->getGlobal( 'PriceFloor' ), $currency );
		$max = self::convert( $adapter->getGlobal( 'PriceCeiling' ), $currency );
		if (
			!is_numeric( $value ) ||
			$value < 0
		) {
			$errors['amount'] = WmfFramework::formatMessage(
				'donate_interface-error-msg-invalid-amount'
			);
		} else if ( $value > $max ) {
			// FIXME: should format the currency values in this message
			$errors['amount'] = WmfFramework::formatMessage(
				'donate_interface-bigamount-error',
				$max,
				$currency,
				$adapter->getGlobal( 'MajorGiftsEmail' )
			);
		} else if ( $value < $min ) {
			$locale = $normalized['language'] . '_' . $normalized['country'];
			$formattedMin = self::format( $min, $currency, $locale );
			$errors['amount'] = WmfFramework::formatMessage(
				'donate_interface-smallamount-error',
				$formattedMin
			);
		}
	}

	/**
	 * Checks if the $value is missing or equivalent to zero.
	 *
	 * @param string $value The value to check for zero-ness
	 * @return boolean True if the $value is missing or zero, otherwise false
	 */
	protected static function isZeroIsh( $value ) {
		if (
			$value === null ||
			trim( $value ) === '' ||
			( is_numeric( $value ) && abs( $value ) < 0.01 )
		) {
			return true;
		}

		return false;
	}

	/**
	 * Convert an amount in USD to a particular currency
	 *
	 * This is grossly rudimentary and likely wildly inaccurate.
	 * This mimics the hard-coded values used by the WMF to convert currencies
	 * for validation on the front-end on the first step landing pages of their
	 * donation process - the idea being that we can get a close approximation
	 * of converted currencies to ensure that contributors are not going above
	 * or below the price ceiling/floor, even if they are using a non-US currency.
	 *
	 * In reality, this probably ought to use some sort of webservice to get real-time
	 * conversion rates.
	 *
	 * @param float $amount
	 * @param string $currency
	 * @return float
	 * @throws UnexpectedValueException
	 */
	public static function convert( $amount, $currency ) {
		$rates = CurrencyRates::getCurrencyRates();
		$code = strtoupper( $currency );
		if ( array_key_exists( $code, $rates ) ) {
			return $amount * $rates[$code];
		}
		throw new UnexpectedValueException(
			'Bad programmer!  Bad currency made it too far through the portcullis'
		);
	}

	/**
	 * Some currencies, like JPY, don't exist in fractional amounts.
	 * This rounds an amount to the appropriate number of decimal places.
	 * Use the results of this for internal use, and use @see Amount::format
	 * for values displayed to donors.
	 *
	 * @param float $amount
	 * @param string $currencyCode
	 * @return string rounded amount
	 */
	public static function round( $amount, $currencyCode ) {
		if ( self::is_fractional_currency( $currencyCode ) ){
			return number_format( $amount, 2, '.', '' );
		} else {
			return ( string ) floor( $amount );
		}
	}

	/**
	 * If an amount is ever expressed for the fractional currencies defined in
	 * this function, they should not have an associated fractional amount
	 * (so: full integers only).
	 *
	 * @param string $currency_code The three-digit currency code.
	 * @return boolean
	 */
	public static function is_fractional_currency( $currency_code ){
		// these currencies cannot have cents.
		$non_fractional_currencies = array(
			'CLP', 'DJF', 'IDR', 'JPY', 'KMF', 'KRW', 'MGA', 'PYG', 'VND', 'XAF', 'XOF', 'XPF'
		);

		if ( in_array( strtoupper( $currency_code ), $non_fractional_currencies ) ) {
			return false;
		}
		return true;
	}

	/**
	 * Checks if ISO 4217 defines the currency's minor units as being expressed using
	 * exponent 3 (three decimal places).
	 * @param string $currency_code The three-character currency code.
	 * @return boolean
	 */
	public static function is_exponent3_currency( $currency_code ){

		$exponent3_currencies = array( 'BHD', 'CLF', 'IQD', 'KWD', 'LYD', 'MGA', 'MRO', 'OMR', 'TND' );

		if ( in_array( strtoupper( $currency_code ), $exponent3_currencies ) ) {
			return true;
		}
		return false;
	}

	/**
	 * Format an amount and currency for display to users.
	 *
	 * @param float $amount
	 * @param string $currencyCode
	 * @param string $locale e.g. en_US
	 * @return string
	 */
	public static function format( $amount, $currencyCode, $locale ) {
		$amount = self::round( $amount, $currencyCode );
		if ( class_exists( 'NumberFormatter' ) ) {
			$formatter = new NumberFormatter( $locale, NumberFormatter::CURRENCY );
			return $formatter->formatCurrency(
				$amount,
				$currencyCode
			);
		} else {
			return "$amount $currencyCode";
		}
	}
}
