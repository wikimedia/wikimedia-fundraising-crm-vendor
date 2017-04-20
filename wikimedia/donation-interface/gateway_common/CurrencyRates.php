<?php
/**
 * Automatically generated from make_exchange_refs.drush.inc:templates/ref_source.php.twig
 * -- do not edit! --
 * Instead, run drush make-exchange-refs and look in the 'generated' folder.
 */

class CurrencyRates {
	/**
	 * Supplies rough (not up-to-date) conversion rates for currencies
	 */

	static public $lastUpdated = '2017-01-31';

	static public function getCurrencyRates() {
		// Not rounding numbers under 1 because I don't think that's a big issue and could cause issues with the max check.
		$currencyRates = array(
			'ADF' => 6.11,
			'ADP' => 155,
			'AED' => 3.67,
			'AFA' => 67,
			'AFN' => 67,
			'ALL' => 126,
			'AMD' => 486,
			'ANG' => 1.78,
			'AOA' => 165,
			'AON' => 165,
			'ARS' => 16,
			'ATS' => 13,
			'AUD' => 1.32,
			'AWG' => 1.79,
			'AZM' => 9583,
			'AZN' => 1.92,
			'BAM' => 1.82,
			'BBD' => 2,
			'BDT' => 78,
			'BEF' => 38,
			'BGL' => 1.81,
			'BGN' => 1.81,
			'BHD' => 0.37423,
			'BIF' => 1663,
			'BMD' => 0.99960000000003,
			'BND' => 1.41,
			'BOB' => 6.73,
			'BRL' => 3.13,
			'BSD' => 0.99555000000002,
			'BTN' => 68,
			'BWP' => 10,
			'BYR' => 20020,
			'BZD' => 1.96,
			'CAD' => 1.31,
			'CDF' => 1250,
			'CHF' => 0.99298000000003,
			'CLP' => 647,
			'CNY' => 6.88,
			'COP' => 2922,
			'CRC' => 537,
			'CUC' => 1,
			'CUP' => 23,
			'CVE' => 103,
			'CYP' => 0.54534900000001,
			'CZK' => 25,
			'DEM' => 1.82,
			'DJF' => 178,
			'DKK' => 6.93,
			'DOP' => 46,
			'DZD' => 109,
			'ECS' => 25589,
			'EEK' => 15,
			'EGP' => 19,
			'ESP' => 155,
			'ETB' => 22,
			'EUR' => 0.931784,
			'FIM' => 5.54,
			'FJD' => 2.05,
			'FKP' => 0.79897000000003,
			'FRF' => 6.11,
			'GBP' => 0.79918799999998,
			'GEL' => 2.69,
			'GHC' => 43112,
			'GHS' => 4.31,
			'GIP' => 0.79897000000003,
			'GMD' => 43,
			'GNF' => 9274,
			'GRD' => 318,
			'GTQ' => 7.27,
			'GYD' => 198,
			'HKD' => 7.76,
			'HNL' => 23,
			'HRK' => 6.96,
			'HTG' => 67,
			'HUF' => 289,
			'IDR' => 13316,
			'IEP' => 0.73384000000003,
			'ILS' => 3.77,
			'INR' => 68,
			'IQD' => 1160,
			'IRR' => 32364,
			'ISK' => 116,
			'ITL' => 1804,
			'JMD' => 127,
			'JOD' => 0.70624,
			'JPY' => 113,
			'KES' => 102,
			'KGS' => 69,
			'KHR' => 3959,
			'KMF' => 456,
			'KPW' => 135,
			'KRW' => 1159,
			'KWD' => 0.30433,
			'KYD' => 0.81291999999997,
			'KZT' => 322,
			'LAK' => 8028,
			'LBP' => 1485,
			'LKR' => 149,
			'LRD' => 91,
			'LSL' => 13,
			'LTL' => 3.22,
			'LUF' => 38,
			'LVL' => 0.65486200000002,
			'LYD' => 1.4,
			'MAD' => 9.96,
			'MDL' => 20,
			'MGA' => 3161,
			'MGF' => 9149,
			'MKD' => 57,
			'MMK' => 1329,
			'MNT' => 2444,
			'MOP' => 7.79,
			'MRO' => 353,
			'MTL' => 0.400015,
			'MUR' => 34,
			'MVR' => 15,
			'MWK' => 718,
			'MXN' => 21,
			'MYR' => 4.43,
			'MZM' => 70310,
			'MZN' => 70,
			'NAD' => 13,
			'NGN' => 307,
			'NIO' => 29,
			'NLG' => 2.05,
			'NOK' => 8.29,
			'NPR' => 107,
			'NZD' => 1.37,
			'OMR' => 0.38365,
			'PAB' => 1,
			'PEN' => 3.25,
			'PGK' => 3.11,
			'PHP' => 50,
			'PKR' => 104,
			'PLN' => 4.03,
			'PTE' => 187,
			'PYG' => 5648,
			'QAR' => 3.64,
			'ROL' => 41891,
			'RON' => 4.19,
			'RSD' => 115,
			'RUB' => 60,
			'RWF' => 817,
			'SAR' => 3.75,
			'SBD' => 7.64,
			'SCR' => 12,
			'SDD' => 646,
			'SDG' => 6.46,
			'SDP' => 2272,
			'SEK' => 8.8,
			'SGD' => 1.42,
			'SHP' => 0.79937099999998,
			'SIT' => 223,
			'SKK' => 28,
			'SLL' => 7418,
			'SOS' => 542,
			'SRD' => 7.45,
			'SRG' => 7447,
			'STD' => 22700,
			'SVC' => 8.51,
			'SYP' => 215,
			'SZL' => 13,
			'THB' => 35,
			'TJS' => 7.9,
			'TMM' => 17016,
			'TMT' => 3.4,
			'TND' => 2.28,
			'TOP' => 2.28,
			'TRL' => 3781090,
			'TRY' => 3.78,
			'TTD' => 6.62,
			'TWD' => 31,
			'TZS' => 2184,
			'UAH' => 27,
			'UGX' => 3541,
			'USD' => 1,
			'UYU' => 28,
			'UZS' => 3212,
			'VEB' => 9974,
			'VEF' => 9.97,
			'VND' => 22263,
			'VUV' => 106,
			'WST' => 2.55,
			'XAF' => 612,
			'XAG' => 0.0577804,
			'XAU' => 0.00083116099999998,
			'XCD' => 2.69,
			'XEU' => 0.931784,
			'XOF' => 612,
			'XPD' => 0.00132912,
			'XPF' => 111,
			'XPT' => 0.00100675,
			'YER' => 250,
			'YUN' => 115,
			'ZAR' => 13,
			'ZMK' => 5328,
			'ZWD' => 376,
		);

		return $currencyRates;
	}
}
