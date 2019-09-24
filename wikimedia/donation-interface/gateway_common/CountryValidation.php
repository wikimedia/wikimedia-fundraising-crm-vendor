<?php

use SmashPig\Core\ValidationError;
use GeoIp2\Database\Reader;

class CountryValidation implements ValidationHelper {

	/**
	 * Check that country code is present and valid
	 *
	 * @param GatewayType $adapter
	 * @param array $normalized Donation data in normalized form.
	 * @param ErrorState &$errors Reference to error array
	 */
	public function validate( GatewayType $adapter, $normalized, &$errors ) {
		global $wgDonationInterfaceForbiddenCountries;
		$hasCountryCode = false;
		$country = '';

		if ( !empty( $normalized['country'] ) ) {
			$country = strtoupper( $normalized['country'] );
			if ( self::isValidIsoCode( $country ) ) {
				$hasCountryCode = true;
			}
		}
		if ( $hasCountryCode ) {
			if (
				is_array( $wgDonationInterfaceForbiddenCountries ) &&
				in_array( $country, $wgDonationInterfaceForbiddenCountries )
			) {
				$errors->addError( new ValidationError(
					'country',
					'donate_interface-error-msg-country-calc'
				) );
			}
		} elseif ( in_array( 'country', $adapter->getRequiredFields( $normalized ) ) ) {
			$otherWays = $adapter->getGlobal( 'OtherWaysURL' );
			$otherWays = str_replace( '$language', $normalized['language'], $otherWays );
			$otherWays = str_replace( '$country', '', $otherWays );

			$errors->addError( new ValidationError(
				'country',
				'donate_interface-error-msg-invalid-country',
				[ $otherWays ]
			) );
		}
	}

	/**
	 * Checks to see if $country is a valid iso 3166-1 country code.
	 * DOES NOT VERIFY THAT WE FUNDRAISE THERE. Only that the code makes sense.
	 * @param string $country the code we want to check
	 * @return bool
	 */
	public static function isValidIsoCode( $country ) {
		/**
		 * List of valid iso 3166 country codes, regenerated on 1380836686
		 * Code generated by a happy script at
		 * https://gerrit.wikimedia.org/r/#/admin/projects/wikimedia/fundraising/tools,branches
		 */
		$iso_3166_codes = [
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
		];

		if ( in_array( $country, $iso_3166_codes ) ) {
			return true;
		}
		return false;
	}

	/**
	 * @param string $ip Request IP address
	 * @return string|null country ISO code, or null if there's a problem with the db.
	 */
	public static function lookUpCountry( $ip ) {
		if ( WmfFramework::validateIP( $ip ) ) {
			try {
				$dbPath = GatewayAdapter::getGlobal( 'GeoIpDbPath' );
				$reader = new Reader( $dbPath );
				return $reader->country( $ip )->country->isoCode;
			} catch ( Exception $e ) {
				// Suppressing missing database exception thrown in CI
			}
		}
		return null;
	}
}
