<?php

/**
 * DataValidator
 * This class is responsible for performing all kinds of data validation, 
 * wherever we may need it. 
 * 
 * All functions should be static, so we don't have to construct anything in 
 * order to use it any/everywhere. 
 * 
 * @author khorn
 * @author awight
 */
class DataValidator {

	// because of the hacky way we test for translation existence in getErrorMessage,
	// we need to explicitly specify which fields we want to try to get country-
	// specific names for.
	protected static $countrySpecificFields = array(
		'fiscal_number',
	);

	/**
	 * getErrorToken, intended to be used by classes that exist relatively close 
	 * to the form classes, returns the error token (defined on the forms) that 
	 * specifies *where* the error will appear within the form output. 
	 * @param string $field The field that ostensibly has an error that needs to 
	 * be displayed to the user. 
	 * @return string The error token corresponding to a field, probably in 
	 * RapidHTML. 
	 */
	public static function getErrorToken( $field ){
		switch ( $field ) {
			case 'amountGiven' :
			case 'amountOther' :
				$error_token = 'amount';
				break;
			case 'email' :
				$error_token = 'emailAdd';
				break;
			case 'amount' :
			case 'currency_code' :
			case 'card_num':
			case 'card_type':
			case 'cvv':
			case 'fiscal_number':
			case 'fname':
			case 'lname':
			case 'city':
			case 'country':
			case 'street':
			case 'state':
			case 'zip':
				$error_token = $field;
				break;
			default:
				$error_token = 'general';
				break;
		}
		return $error_token;
	}
	
	/**
	 * getEmptyErrorArray
	 * This only exists anymore, to make badly-coded forms happy when they start
	 * pulling keys all over the place without checking to see if they're set or
	 * not. 
	 * @return array All the possible error tokens as keys, with blank errors. 
	 */
	public static function getEmptyErrorArray() {
		return array(
			'general' => '',
			'retryMsg' => '',
			'amount' => '',
			'card_num' => '',
			'card_type' => '',
			'cvv' => '',
			'fiscal_number' => '',
			'fname' => '',
			'lname' => '',
			'city' => '',
			'country' => '',
			'street' => '',
			'state' => '',
			'zip' => '',
			'emailAdd' => '',
		);
	}


	/**
	 * getErrorMessage - returns the translated error message appropriate for a
	 * validation error on the specified field, of the specified type.
	 * @param string $field - The common name of the field containing the data
	 * that is causing the error.
	 * @param string $type - The type of error being caused, from a set.
	 *    Possible values are:
	 *        'not_empty' - the value is required and not currently present
	 *        'valid_type' - in general, the wrong format
	 *        'calculated' - fields that failed some kind of multiple-field data
	 * integrity check.
	 * @param string $language MediaWiki language code
	 * @param string $country ISO country code
	 * @return String
	 */
	public static function getErrorMessage( $field, $type, $language, $country = null ){
		//this is gonna get ugly up in here. 
		//error_log( __FUNCTION__ . " $field, $type, $value " );

		//NOTE: We are just using the next bit because it's convenient. 
		//getErrorToken is actually for something entirely different: 
		//Figuring out where on the form the error should land.  
		$message_field = self::getErrorToken( $field );
		if ( $field === 'expiration' ){
			///the inevitable special case.
			$message_field = $field;
		}
		//postal code is a weird one. More L10n than I18n. 
		//'donate_interface-error-msg-postal' => 'postal code',

		$error_message_field_key = 'donate_interface-error-msg-' . $message_field;

		if ( $country !== null && in_array( $message_field, self::$countrySpecificFields ) ) {
			$translated_field_name = MessageUtils::getCountrySpecificMessage( $error_message_field_key, $country, $language );
		} else if ( MessageUtils::messageExists( $error_message_field_key, $language ) ) {
			$translated_field_name = WmfFramework::formatMessage( $error_message_field_key );
		} else {
			$translated_field_name = false;
		}

		//Empty messages should get: 
		//'donate_interface-error-msg' => 'Please enter your $1';
		//If they have no defined error message, give 'em the default. 
		if ($type === 'not_empty'){
			if ( $message_field != 'general' && $translated_field_name ) {
				return WmfFramework::formatMessage(
					'donate_interface-error-msg',
					$translated_field_name
				);
			} 
		}
		
		if ( $type === 'valid_type' || $type === 'calculated' ) {
			//NOTE: We are just using the next bit because it's convenient. 
			//getErrorToken is actually for something entirely different: 
			//Figuring out where on the form the error should land.  
			$token = self::getErrorToken( $field );
			$suffix = $token; //defaultness
			switch ($token){
				case 'amount': 
					$suffix = 'invalid-amount';
					break;
			}

			$error_key_calc = 'donate_interface-error-msg-' . $suffix . '-calc';

			if ( $type === 'calculated'){
				// try for the special "calculated" error message.
				// Note: currently only used for country
				if ( MessageUtils::messageExists( $error_key_calc, $language ) ) {
					return WmfFramework::formatMessage( $error_key_calc );
				}
			}

			//try for new more specific default correction message
			if ( $message_field != 'general' 
				&& $translated_field_name
				&& MessageUtils::messageExists( 'donate_interface-error-msg-field-correction', $language ) ) {
				return WmfFramework::formatMessage(
					'donate_interface-error-msg-field-correction',
					$translated_field_name
				);
			}
		}
		
		//ultimate defaultness. 
		return WmfFramework::formatMessage( 'donate_interface-error-msg-general' );
	}

	/**
	 * validate
	 * Run all the validation rules we have defined against a (hopefully
	 * normalized) DonationInterface data set.
	 * @param GatewayAdapter $gateway
	 * @param array $data The DonationInterface data set, or a subset thereof.
	 * @param array $check_not_empty An array of fields to do empty validation
	 * on. If this is not populated, no fields will throw errors for being empty,
	 * UNLESS they are required for a field that uses them for more complex
	 * validation (the 'calculated' phase).
	 * @throws BadMethodCallException
	 * @return array An array of errors in a format ready for any derivitive of
	 * the main DonationInterface Form class to display. The array will be empty
	 * if no errors were generated and everything passed OK.
	 */
	public static function validate( GatewayAdapter $gateway, $data, $check_not_empty = array()  ){
		//return the array of errors that should be generated on validate.
		//just the same way you'd do it if you were a form passing the error array around. 
		
		/**
		 * We need to run the validation in an order that makes sense. 
		 * 
		 * First: If we need to validate that some things are not empty, do that. 
		 * Second: Do regular data type validation on things that are not empty.
		 * Third: Do validation that depends on multiple fields (making sure you 
		 * validated that all the required fields exist on step 1, regardless of 
		 * $check_not_empty)
		 * 
		 * $check_not_empty should contain an array of values that need to be populated. 
		 * One likely candidate for a source there, is the required stomp fields as defined in DonationData. 
		 * Although, a lot of those don't have to have any data in them either. Boo.
		 * 
		 * How about we build an array of shit to do, 
		 * look at it to make sure it's complete, and in order...
		 * ...and do it. 
		 */

		// Define all default validations.
		$validations = array(
			'not_empty' => array(
				'amount' => 'validate_non_zero',
				'country',
				'currency_code',
				'gateway',
			),
			'valid_type' => array(
				'_cache_' => 'validate_boolean',
				'account_number' => 'validate_numeric',
				'amount' => 'validate_numeric',
				'amountGiven' => 'validate_numeric',
				'amountOther' => 'validate_numeric',
				'anonymous' => 'validate_boolean',
				'contribution_tracking_id' => 'validate_numeric',
				'currency_code' => 'validate_alphanumeric',
				'gateway' => 'validate_alphanumeric',
				'numAttempt' => 'validate_numeric',
				'optout' => 'validate_boolean',
				'posted' => 'validate_boolean',
				'recurring' => 'validate_boolean',
			),
			// Note that order matters for this group, dependencies must come first.
			'calculated' => array(
				'gateway' => 'validate_gateway',
				'address' => 'validate_address',
				'city' => 'validate_address',
				'country' => 'validate_country_allowed',
				'email' => 'validate_email',
				'street' => 'validate_address',
				'currency_code' => 'validate_currency_code',
				'fname' => 'validate_name',
				'lname' => 'validate_name',
				'name' => 'validate_name',

				// Depends on currency_code and gateway.
				'amount' => 'validate_amount',

				// Depends on country
				'fiscal_number' => 'validate_fiscal_number',
			),
		);

		// Additional fields we should check for emptiness.
		if ( $check_not_empty ) {
			$validations['not_empty'] = array_unique( array_merge(
				$check_not_empty, $validations['not_empty']
			) );
		}

		$errors = array();

		$language = DataValidator::guessLanguage( $data );
		if ( empty( $data['country'] ) ) {
			$country = null;
		} else {
			$country = $data['country'];
		}

		foreach ( $validations as $phase => $fields ) {
			foreach ( $fields as $key => $custom ) {
				// Here we decode list vs map elements.
				if ( is_numeric( $key ) ) {
					$field = $custom;
					$validation_function = "validate_{$phase}";
				} else {
					$field = $key;
					$validation_function = $custom;
				}

				if ( !isset( $data[$field] ) ) {
					if ( $phase !== 'not_empty' ) {
						// Skip if not required and nothing to validate.
						continue;
					} else {
						// Stuff with nothing.
						$data[$field] = null;
					}
				}

				// Skip if we've already determined this field group is invalid.
				$errorToken = self::getErrorToken( $field );
				if ( array_key_exists( $errorToken, $errors ) ) {
					continue;
				}

				// Prepare to call the thing.
				$callable = array( 'DataValidator', $validation_function );
				if ( !is_callable( $callable ) ) {
					throw new BadMethodCallException( __FUNCTION__ . " BAD PROGRAMMER. No function {$validation_function} for $field" );
				}
				$result = null;
				// Handle special cases.
				switch ( $validation_function ) {
					case 'validate_amount':
						if ( self::checkValidationPassed( array( 'currency_code', 'gateway' ), $results ) ){
							$priceFloor = $gateway->getGlobal( 'PriceFloor' );
							$priceCeiling = $gateway->getGlobal( 'PriceCeiling' );
							$result = call_user_func( $callable, $data[$field], $data['currency_code'], $priceFloor, $priceCeiling );
						} //otherwise, just don't do the validation. The other stuff will be complaining already. 
						break;
					case 'validate_currency_code':
						$result = call_user_func( $callable, $data[$field], $gateway->getCurrencies() );
						break;
					case 'validate_fiscal_number':
						$result = call_user_func( $callable, $data[$field], $data['country'] );
						break;
					default:
						$result = call_user_func( $callable, $data[$field] );
						break;
				}

				// Store results.
				$results[$phase][$field] = $result;
				if ( $result === false ) {
					// We did the check, and it failed.
					$errors[$errorToken] = self::getErrorMessage( $field, $phase, $language, $country );
				}
			}
		}

		return $errors;
	}
	
	
	/**
	 * checkValidationPassed is a validate helper function. 
	 * In order to determine that we are ready to do the third stage of data 
	 * validation (calculated) for any given field, we need to determine that 
	 * all fields required to validate the original have, themselves, passed 
	 * validation. 
	 * @param array $fields An array of field names to check.
	 * @param array $results Intermediate result of validation.
	 * @return boolean true if all fields specified in $fields passed their 
	 * not_empty and valid_type validation. Otherwise, false.
	 */
	protected static function checkValidationPassed( $fields, $results ){
		foreach ( $fields as $field ){
			foreach ( $results as $phase => $results_fields ) {
				if ( array_key_exists( $field, $results_fields )
					&& $results_fields[$field] !== true
				) {
					return false;
				}
			}
		}
		return true;
	}

	/**
	 * validate_email
	 * Determines if the $value passed in is a valid email address. 
	 * @param string $value The piece of data that is supposed to be an email 
	 * address. 
	 * @return boolean True if $value is a valid email address, otherwise false.  
	 */
	protected static function validate_email( $value ) {
		return WmfFramework::validateEmail( $value )
			&& !DataValidator::cc_number_exists_in_str( $value );
	}

	/**
	 * validate_amount
	 *
	 * Determines if the $value passed in is a valid amount. 
	 * @param string $value The piece of data that is supposed to be an amount. 
	 * @param string $currency_code The amount was given in this currency.
	 * @param float $priceFloor Minimum valid amount (USD).
	 * @param float $priceCeiling Maximum valid amount (USD).
	 *
	 * @return boolean True if $value is a valid amount, otherwise false.  
	 */
	protected static function validate_amount( $value, $currency_code, $priceFloor, $priceCeiling ) {
		if ( !$value || !$currency_code || !is_numeric( $value ) ) {
			return false;
		}

		if ( !preg_match( '/^\d+(\.(\d+)?)?$/', $value ) ||
			( ( float ) self::convert_to_usd( $currency_code, $value ) < ( float ) $priceFloor ||
			( float ) self::convert_to_usd( $currency_code, $value ) > ( float ) $priceCeiling ) ) {
			return false;
		}
		
		return true;
	}

	protected static function validate_currency_code( $value, $acceptedCurrencies ) {
		if ( !$value ) {
			return false;
		}

		return in_array( $value, $acceptedCurrencies );
	}
	
	/**
	 * validate_card_type
	 * Determines if the $value passed in is (possibly) a valid credit card type.
	 * @param string $value The piece of data that is supposed to be a credit card type.
	 * @param string $card_number The card number associated with this card type. Optional.
	 * @return boolean True if $value is a reasonable credit card type, otherwise false.  
	 */
	protected static function validate_card_type( $value, $card_number = '' ) {
		//@TODO: Find a better way to stop making assumptions about what payment
		//type we're trying to be, in the data validadtor.
		if ( $card_number != '' ){
			if ( !array_key_exists( $value, self::$card_types ) ){
				return false;
			}
			$calculated_card_type = self::getCardType( $card_number );
			if ( $calculated_card_type != $value ){
				return false;
			}
		}
		
		return true;
	}
	
	
	/**
	 * validate_credit_card
	 * Determines if the $value passed in is (possibly) a valid credit card number.
	 * @param string $value The piece of data that is supposed to be a credit card number.
	 * @return boolean True if $value is a reasonable credit card number, otherwise false.  
	 */
	protected static function validate_credit_card( $value ) {
		$calculated_card_type = self::getCardType( $value );
		if ( !$calculated_card_type ){
			return false;
		}
		
		return true;
	}
	
	
	/**
	 * validate_boolean
	 * Determines if the $value passed in is a valid boolean. 
	 * @param string $value The piece of data that is supposed to be a boolean.
	 * @return boolean True if $value is a valid boolean, otherwise false.  
	 */
	protected static function validate_boolean( $value ){
		// FIXME: this doesn't do the strict comparison we intended.  'hello' would match the "case true" statement.
		switch ($value) {
			case 0:
			case '0':
			case false:
			case 'false':
			case 1:
			case '1':
			case true:
			case 'true':
				return true;
				break;
		}
		return false;
	}
	
	
	/**
	 * validate_numeric
	 * Determines if the $value passed in is numeric. 
	 * @param string $value The piece of data that is supposed to be numeric.
	 * @return boolean True if $value is numeric, otherwise false.  
	 */
	protected static function validate_numeric( $value ){
		//instead of validating here, we should probably be doing something else entirely. 
		if ( is_numeric( $value ) ) { 
			return true;
		}
		return false;
	}

	/**
	 * validate_gateway
	 * Checks to make sure the gateway is populated with a valid and enabled 
	 * gateway. 
	 * @param string $value The value that is meant to be a gateway. 
	 * @return boolean True if $value is a valid gateway, otherwise false
	 */
	protected static function validate_gateway( $value ){
		global $wgDonationInterfaceGatewayAdapters;

		foreach ( $wgDonationInterfaceGatewayAdapters as $adapterClass ) {
			if ( $adapterClass::getIdentifier() === $value ) {
				return true;
			}
		}
		return false;
	}

	/**
	 * validate_not_empty
	 * Checks to make sure that the $value is present in the $data array, and not null or an empty string. 
	 * Anything else that is 'falseish' is still perfectly valid to have as a data point. 
	 * TODO: Consider doing this in a batch. 
	 * @param string $value The value to check for non-emptyness.
	 * @return boolean True if the $value is not missing or empty, otherwise false.
	 */
	protected static function validate_not_empty( $value ){
		return ( $value !== null && $value !== '' );
	}

	/**
	 * validate_alphanumeric
	 * Checks to make sure the value is populated with an alphanumeric value...
	 * ...which would be great, if it made sense at all. 
	 * TODO: This is duuuuumb. Make it do something good, or get rid of it.
	 * If we can think of a way to make this useful, we should do something here. 
	 * @param string $value The value that is meant to be alphanumeric
	 * @return boolean True if $value is ANYTHING. Or not. :[
	 */
	protected static function validate_alphanumeric( $value ){
		return true;
	}
	
	/**
	 * Validates that somebody didn't just punch in a bunch of punctuation, and
	 * nothing else. Doing so for certain fields can short-circuit AVS checking
	 * at some banks, and so we want to treat data like this as empty in the
	 * adapter staging phase. 
	 * @param string $value The value to check
	 * @return bool true if it's more than just punctuation, false if it is. 
	 */
	public static function validate_not_just_punctuation( $value ){
		$value = html_entity_decode( $value ); //Just making sure.
		$regex = '/([\x20-\x2F]|[\x3A-\x40]|[\x5B-\x60]|[\x7B-\x7E]){' . strlen($value) . '}/';
		if ( preg_match( $regex, $value ) ){
			return false;
		}
		return true;
	}
	
	/**
	 * Validate that the country is legally allowed to give us a donation. 
	 * Failure here should halt everything, all the time. 
	 * @param string $value The value to check
	 * @return boolean true if we are allowed to accept donations from this
	 * country, false if not. 
	 */
	public static function validate_country_allowed( $value ){
		global $wgDonationInterfaceForbiddenCountries;
		if ( in_array( strtoupper($value), $wgDonationInterfaceForbiddenCountries ) ){
			return false;
		}
		// TODO: return DataValidator::is_valid_iso_country_code( $value );
		return true;
	}

	/**
	 * Some people are silly and enter their CC numbers as their name. This performs a luhn check
	 * on the name to make sure it's not actually a potentially valid CC number.
	 *
	 * @param string $value Ze name!
	 * @return boolean True if the name is not suspiciously like a CC number
	 */
	public static function validate_name( $value ) {
		return !DataValidator::cc_number_exists_in_str( $value );
	}

	/**
	 * Gets rid of numbers that pass luhn in address fields - @see validate_name
	 * @param $value
	 * @return bool True if suspiciously like a CC number
	 */
	public static function validate_address( $value ) {
		return !DataValidator::cc_number_exists_in_str( $value );
	}

	/**
	 * Checks to make sure that the $value is present in the $data array, and not equivalent to zero.
	 * @param string $value The value to check for non-zeroness.
	 * @param array $data The whole data set.
	 * @return boolean True if the $value is not missing or zero, otherwise false.
	 */
	public static function validate_non_zero( $value ) {
		if ( $value === null || $value === ''
			|| preg_match( '/^0+(\.0+)?$/', $value )
		) {
			return false;
		}
		return true;
	}

	/**
	 * Rudimentary tests of fiscal number format for different countries
	 * @param string $value The alleged fiscal number
	 * @param array $country The country whose format we care about
	 * @return boolean True if the $value is a valid fiscal number, false otherwise
	 */
	public static function validate_fiscal_number( $value, $country ) {
		$countryRules = array(
			'AR' => array(
				'numeric' => true,
				'min' => 8,
				'max' => 8,
			),
			'BR' => array(
				'numeric' => true,
				'min' => 11,
				'max' => 14,
			),
			'CO' => array(
				'numeric' => true,
				'min' => 11,
				'max' => 14,
			),
			'CL' => array(
				'min' => 8,
				'max' => 9,
			),
			'MX' => array(
				'min' => 10,
				'max' => 18,
			),
			'PE' => array(
				'numeric' => true,
				'min' => 8,
				'max' => 9,
			),
			'UY' => array(
				'numeric' => true,
				'min' => 6,
				'max' => 8,
			),
		);

		if ( !isset( $countryRules[$country] ) ) {
			return true;
		}

		$rules = $countryRules[$country];
		$unpunctuated = preg_replace( '/[^A-Za-z0-9]/', '', $value );

		if ( !empty( $rules['numeric'] ) ) {
			if ( !is_numeric( $unpunctuated ) ) {
				return false;
			}
		}

		$length = strlen( $unpunctuated );
		if ( $length < $rules['min'] || $length > $rules['max'] ) {
			return false;
		}

		return true;
	}

	/**
	 * Analyzes a string to see if any credit card numbers are hiding out in it
	 *
	 * @param $str
	 *
	 * @return bool True if a CC number was found sneaking about in the shadows
	 */
	public static function cc_number_exists_in_str( $str ) {
		$luhnRegex = <<<EOT
/
(?#amex)(3[47][0-9]{13})|
(?#bankcard)(5610[0-9]{12})|(56022[1-5][0-9]{10})|
(?#diners carte blanche)(300[0-5][0-9]{11})|
(?#diners intl)(36[0-9]{12})|
(?#diners US CA)(5[4-5][0-9]{14})|
(?#discover)(6011[0-9]{12})|(622[0-9]{13})|(64[4-5][0-9]{13})|(65[0-9]{14})|
(?#InstaPayment)(63[7-9][0-9]{13})|
(?#JCB)(35[2-8][0-9]{13})|
(?#Laser)(6(304|7(06|09|71))[0-9]{12,15})|
(?#Maestro)((5018|5020|5038|5893|6304|6759|6761|6762|6763|0604)[0-9]{8,15})|
(?#MasterCard)(5[1-5][0-9]{14})|
(?#Solo)((6334|6767)[0-9]{12,15})|
(?#Switch)((4903|4905|4911|4936|6333|6759)[0-9]{12,15})|((564182|633110)[0-9]{10,13})|
(?#Visa)(4([0-9]{15}|[0-9]{12}))
/
EOT;

		$nonLuhnRegex = <<<EOT
/
(?#china union pay)(62[0-9]{14,17})|
(?#diners enroute)((2014|2149)[0-9]{11})
/
EOT;

		// Transform the regex to get rid of the new lines
		$luhnRegex = preg_replace( '/\s/', '', $luhnRegex );
		$nonLuhnRegex = preg_replace( '/\s/', '', $nonLuhnRegex );

		// Remove common CC# delimiters
		$str = preg_replace( '/[\s\-]/', '', $str );

		// Now split the string on everything else and join again so the regexen have an 'easy' time
		$str = join( ' ', preg_split( '/[^0-9]+/', $str, PREG_SPLIT_NO_EMPTY ) );

		// First do we have any numbers that match a pattern but is not luhn checkable?
		$matches = array();
		if ( preg_match_all( $nonLuhnRegex, $str, $matches ) > 0 ) {
			return true;
		}

		// Find potential CC numbers that do luhn check and run 'em
		$matches = array();
		preg_match_all( $luhnRegex, $str, $matches );
		foreach ( $matches[0] as $candidate ) {
			if ( DataValidator::luhn_check( $candidate ) ) {
				return true;
			}
		}

		// All our checks have failed; probably doesn't contain a CC number
		return false;
	}

	/**
	 * Performs a Luhn algorithm check on a string.
	 *
	 * @param $str
	 *
	 * @return bool True if the number was valid according to the algorithm
	 */
	public static function luhn_check( $str ) {
		$odd = (strlen( $str ) % 2);
		$sum = 0;

		for( $i = 0; $i < strlen( $str ); $i++ ) {
			if ( $odd ) {
				$sum += $str[$i];
			} else {
				if ( ( $str[$i] * 2 ) > 9 ) {
					$sum += $str[$i] * 2 - 9;
				} else {
					$sum += $str[$i] * 2;
				}
			}

			$odd = !$odd;
		}
		return( ( $sum % 10 ) == 0 );
	}

	/**
	 * Convert an amount for a particular currency to an amount in USD
	 *
	 * This is grosley rudimentary and likely wildly inaccurate.
	 * This mimicks the hard-coded values used by the WMF to convert currencies
	 * for validatoin on the front-end on the first step landing pages of their
	 * donation process - the idea being that we can get a close approximation
	 * of converted currencies to ensure that contributors are not going above
	 * or below the price ceiling/floor, even if they are using a non-US currency.
	 *
	 * In reality, this probably ought to use some sort of webservice to get real-time
	 * conversion rates.
	 *
	 * @param string $currency_code
	 * @param float $amount
	 * @return float
	 * @throws UnexpectedValueException
	 */
	public static function convert_to_usd( $currency_code, $amount ) {
		$rates = CurrencyRates::getCurrencyRates();
		$code = strtoupper( $currency_code );
		if ( array_key_exists( $code, $rates ) ) {
			$usd_amount = $amount / $rates[$code];
		} else {
			throw new UnexpectedValueException( 'Bad programmer!  Bad currency made it too far through the portcullis' );
		}
		return $usd_amount;
	}
	
	
	/**
	 * Calculates and returns the card type for a given credit card number. 
	 * @param numeric $card_num A credit card number.
	 * @return string|bool 'amex', 'mc', 'visa', 'discover', or false. 
	 */
	public static function getCardType( $card_num ) {
		// validate that credit card number entered is correct and set the card type
		if ( preg_match( '/^3[47][0-9]{13}$/', $card_num ) ) { // american express
			return 'amex';
		} elseif ( preg_match( '/^5[1-5][0-9]{14}$/', $card_num ) ) { //	mastercard
			return 'mc';
		} elseif ( preg_match( '/^4[0-9]{12}(?:[0-9]{3})?$/', $card_num ) ) {// visa
			return 'visa';
		} elseif ( preg_match( '/^6(?:011|5[0-9]{2})[0-9]{12}$/', $card_num ) ) { // discover
			return 'discover';
		} else { // an unrecognized card type was entered
			return false;
		}
	}
	
	/**
	 * Returns a valid mediawiki language code to use for all the DonationInterface translations.
	 *
	 * Will only look at the currently configured language if the 'language' key 
	 * doesn't exist in the data set: Users may not have a language preference 
	 * set if we're bouncing between mediawiki instances for payments.
	 * @param array $data A normalized DonationInterface data set. 
	 * @return string A valid mediawiki language code. 
	 */
	public static function guessLanguage( $data ) {
		if ( array_key_exists( 'language', $data )
			&& WmfFramework::isValidBuiltInLanguageCode( $data['language'] ) ) {
			return $data['language'];
		} else {
			return WmfFramework::getLanguageCode();
		}		
	}
	
	/**
	 * Takes either an IP address, or an IP address with a CIDR block, and 
	 * expands it to an array containing all the relevent addresses so we can do 
	 * things like save the expanded list to memcache, and use in_array(). 
	 * @param string $ip Either a single address, or a block. 
	 * @return array An expanded list of IP addresses denoted by $ip. 
	 */
	public static function expandIPBlockToArray( $ip ){
		$parts = explode('/', $ip);
		if ( count( $parts ) === 1 ){
			return array( $ip );
		} else {
			//expand that mess.
			//this next bit was stolen from php.net and smacked around some
			$corr = ( pow( 2, 32 ) - 1) - ( pow( 2, 32 - $parts[1] ) - 1 );
			$first = ip2long( $parts[0] ) & ( $corr );
			$length = pow( 2, 32 - $parts[1] ) - 1;
			$ips = array( );
			for ( $i = 0; $i <= $length; $i++ ) {
				$ips[] = long2ip( $first + $i );
			}
			return $ips;
		}
	}

	/**
	 * Check whether IP matches a block list
	 *
	 * TODO: We might want to store the expanded list in memcache.
	 *
	 * @param string $ip The IP addx we want to check
	 * @param array $ip_list IP list to check against
	 * @return bool
	 */
	public static function ip_is_listed( $ip, $ip_list ) {
		$expanded = array();
		foreach ( $ip_list as $address ){
			$expanded = array_merge( $expanded, self::expandIPBlockToArray( $address ) );
		}

		return in_array( $ip, $expanded, true );
	}
	
	/**
	 * Test to determine if a value appears in a haystack. The haystack may have
	 * explicit +/- rules (a - will take precedence over a +; if there is no
	 * + rule, but there is a - rule everything is implicitly accepted); and may
	 * also have an 'ALL' condition.
	 *
	 * @param mixed $needle Value, or array of values, to match
	 * @param mixed $haystack Value, or array of values, that are acceptable
	 * @return bool
	 */
	public static function value_appears_in( $needle, $haystack ) {
		$needle = ( is_array( $needle) ) ? $needle : array( $needle );
		$haystack = ( is_array( $haystack) ) ? $haystack : array( $haystack );

		$plusCheck = array_key_exists( '+', $haystack );
		$minusCheck = array_key_exists( '-', $haystack );

		if ( $plusCheck || $minusCheck ) {
			// With +/- checks we will first explicitly deny anything in '-'
			// Then if '+' is defined accept anything there
			//    but if '+' is not defined we just let everything that wasn't denied by '-' through
			// Otherwise we assume both were defined and deny everything :)

			if ( $minusCheck && DataValidator::value_appears_in( $needle, $haystack['-'] ) ) {
				return false;
			}
			if ( $plusCheck && DataValidator::value_appears_in( $needle, $haystack['+'] ) ) {
				return true;
			} elseif ( !$plusCheck ) {
				// Implicit acceptance
				return true;
			}
			return false;
		}

		if ( ( count( $haystack ) === 1 ) && ( in_array( 'ALL', $haystack ) ) ) {
			// If the haystack can accept anything, then whoo!
			return true;
		}

		$haystack = array_filter( $haystack, function( $value ) {
			return !is_array( $value );
		} );
		$result = array_intersect( $haystack, $needle );
		if ( !empty( $result ) ) {
			return true;
		} else {
			return false;
		}
	}
	
	/**
	 * More of a validation helper function. If an amount is ever expressed for 
	 * the fractional currencies defined in this function,
	 * they should not have an associated fractional amount (so: full integers only).
	 * @param string $currency_code The three-digit currency code.
	 * @return boolean
	 */
	public static function is_fractional_currency( $currency_code ){
		// these currencies cannot have cents.
		$non_fractional_currencies = array( 'CLP', 'DJF', 'IDR', 'JPY', 'KMF', 'KRW', 'MGA', 'PYG', 'VND', 'XAF', 'XOF', 'XPF' );
		
		if ( in_array( strtoupper( $currency_code ), $non_fractional_currencies ) ) {
			return false;
		}
		return true;
	}

	/**
	 * Checks to see if $country is a valid iso 3166-1 country code.
	 * DOES NOT VERIFY THAT WE FUNDRAISE THERE. Only that the code makes sense.
	 * @param string $country the code we want to check
	 * @return boolean
	 */
	public static function is_valid_iso_country_code( $country ) {
		/**
		 * List of valid iso 3166 country codes, regenerated on 1380836686
		 * Code generated by a happy script at
		 * https://gerrit.wikimedia.org/r/#/admin/projects/wikimedia/fundraising/tools,branches
		 */
		$iso_3166_codes = array (
			'AF', 'AX', 'AL', 'DZ', 'AS', 'AD', 'AO', 'AI', 'AQ', 'AG', 'AR', 'AM', 'AW', 'AU',
			'AT', 'AZ', 'BS', 'BH', 'BD', 'BB', 'BY', 'BE', 'BZ', 'BJ', 'BM', 'BT', 'BO', 'BQ',
			'BA', 'BW', 'BV', 'BR', 'IO', 'BN', 'BG', 'BF', 'BI', 'KH', 'CM', 'CA', 'CV', 'KY',
			'CF', 'TD', 'CL', 'CN', 'CX', 'CC', 'CO', 'KM', 'CG', 'CD', 'CK', 'CR', 'CI', 'HR',
			'CU', 'CW', 'CY', 'CZ', 'DK', 'DJ', 'DM', 'DO', 'EC', 'EG', 'SV', 'GQ', 'ER', 'EE',
			'ET', 'FK', 'FO', 'FJ', 'FI', 'FR', 'GF', 'PF', 'TF', 'GA', 'GM', 'GE', 'DE', 'GH',
			'GI', 'GR', 'GL', 'GD', 'GP', 'GU', 'GT', 'GG', 'GN', 'GW', 'GY', 'HT', 'HM', 'VA',
			'HN', 'HK', 'HU', 'IS', 'IN', 'ID', 'IR', 'IQ', 'IE', 'IM', 'IL', 'IT', 'JM', 'JP',
			'JE', 'JO', 'KZ', 'KE', 'KI', 'KP', 'KR', 'KW', 'KG', 'LA', 'LV', 'LB', 'LS', 'LR',
			'LY', 'LI', 'LT', 'LU', 'MO', 'MK', 'MG', 'MW', 'MY', 'MV', 'ML', 'MT', 'MH', 'MQ',
			'MR', 'MU', 'YT', 'MX', 'FM', 'MD', 'MC', 'MN', 'ME', 'MS', 'MA', 'MZ', 'MM', 'NA',
			'NR', 'NP', 'NL', 'NC', 'NZ', 'NI', 'NE', 'NG', 'NU', 'NF', 'MP', 'NO', 'OM', 'PK',
			'PW', 'PS', 'PA', 'PG', 'PY', 'PE', 'PH', 'PN', 'PL', 'PT', 'PR', 'QA', 'RE', 'RO',
			'RU', 'RW', 'BL', 'SH', 'KN', 'LC', 'MF', 'PM', 'VC', 'WS', 'SM', 'ST', 'SA', 'SN',
			'RS', 'SC', 'SL', 'SG', 'SX', 'SK', 'SI', 'SB', 'SO', 'ZA', 'GS', 'SS', 'ES', 'LK',
			'SD', 'SR', 'SJ', 'SZ', 'SE', 'CH', 'SY', 'TW', 'TJ', 'TZ', 'TH', 'TL', 'TG', 'TK',
			'TO', 'TT', 'TN', 'TR', 'TM', 'TC', 'TV', 'UG', 'UA', 'AE', 'GB', 'US', 'UM', 'UY',
			'UZ', 'VU', 'VE', 'VN', 'VG', 'VI', 'WF', 'EH', 'YE', 'ZM', 'ZW',
		);

		if ( in_array( $country, $iso_3166_codes ) ) {
			return true;
		}
		return false;
	}

	/**
	 * Okay, so this isn't all validation, but there's a validation
	 * component in there so I'm calling it close enough.
	 * @param string $value the value that should be zero-padded out to $total_length
	 * @param int $total_length The fixed number of characters that $value should be padded out to
	 * @return The zero-padded value, or false if it was too long to work with.
	 */
	static function getZeroPaddedValue( $value, $total_length ) {
		//first, trim all leading zeroes off the value.
		$ret = ltrim( $value, '0' );

		//now, check to see if it's going to be a valid value at all,
		//and give up if it's hopeless.
		if ( strlen( $ret ) > $total_length ) {
			return false;
		}

		//...and if we're still here, left pad with zeroes to required length
		$ret = str_pad( $ret, $total_length, '0', STR_PAD_LEFT );

		return $ret;
	}

}
