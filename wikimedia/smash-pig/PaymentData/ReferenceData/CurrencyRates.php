<?php
/**
 * Automatically generated from make_exchange_refs.drush.inc:templates/ref_source.php.twig
 * -- do not edit! --
 * Instead, run drush make-exchange-refs /output/dir and look in the specified folder.
 */
namespace SmashPig\PaymentData\ReferenceData;

class CurrencyRates {
	/**
	 * Supplies rough (not up-to-date) conversion rates for currencies
	 */

	static public $lastUpdated = '2017-05-02';

	static public function getCurrencyRates() {
		// Not rounding numbers under 1 because I don't think that's a big issue and could cause issues with the max check.
		$currencyRates = array(
			'ADF' => 6.01,
			'ADP' => 152,
			'AED' => 3.67,
			'AFA' => 68,
			'AFN' => 68,
			'ALL' => 122,
			'AMD' => 468,
			'ANG' => 1.77,
			'AOA' => 165,
			'AON' => 165,
			'ARS' => 15,
			'ATS' => 13,
			'AUD' => 1.33,
			'AWG' => 1.79,
			'AZM' => 8510,
			'AZN' => 1.7,
			'BAM' => 1.79,
			'BBD' => 2,
			'BDT' => 81,
			'BEF' => 37,
			'BGL' => 1.79,
			'BGN' => 1.79,
			'BHD' => 0.37445,
			'BIF' => 1681,
			'BMD' => 0.99960015993603,
			'BND' => 1.39,
			'BOB' => 6.8,
			'BRL' => 3.17,
			'BSD' => 0.99205399999998,
			'BTN' => 64,
			'BWP' => 10,
			'BYR' => 20020,
			'BZD' => 1.97,
			'CAD' => 1.37,
			'CDF' => 1369,
			'CHF' => 0.99441000000001,
			'CLP' => 662,
			'CNY' => 6.89,
			'COP' => 2920,
			'CRC' => 547,
			'CUC' => 1,
			'CUP' => 25,
			'CVE' => 101,
			'CYP' => 0.53631881826845,
			'CZK' => 25,
			'DEM' => 1.79,
			'DJF' => 178,
			'DKK' => 6.81,
			'DOP' => 47,
			'DZD' => 109,
			'ECS' => 25589,
			'EEK' => 14,
			'EGP' => 18,
			'ESP' => 152,
			'ETB' => 23,
			'EUR' => 0.91635510593065,
			'FIM' => 5.45,
			'FJD' => 2.08,
			'FKP' => 0.77474996881631,
			'FRF' => 6.01,
			'GBP' => 0.77474936857926,
			'GEL' => 2.43,
			'GHC' => 41911,
			'GHS' => 4.19,
			'GIP' => 0.77474996881631,
			'GMD' => 44,
			'GNF' => 9172,
			'GRD' => 312,
			'GTQ' => 7.17,
			'GYD' => 198,
			'HKD' => 7.78,
			'HNL' => 23,
			'HRK' => 6.83,
			'HTG' => 68,
			'HUF' => 286,
			'IDR' => 13316,
			'IEP' => 0.72168829264717,
			'ILS' => 3.61,
			'INR' => 64,
			'IQD' => 1149,
			'IRR' => 32438,
			'ISK' => 106,
			'ITL' => 1774,
			'JMD' => 128,
			'JOD' => 0.70751999999998,
			'JPY' => 112,
			'KES' => 101,
			'KGS' => 68,
			'KHR' => 3972,
			'KMF' => 451,
			'KPW' => 135,
			'KRW' => 1129,
			'KWD' => 0.30384,
			'KYD' => 0.81632,
			'KZT' => 314,
			'LAK' => 8068,
			'LBP' => 1491,
			'LKR' => 151,
			'LRD' => 91,
			'LSL' => 13,
			'LTL' => 3.16,
			'LUF' => 37,
			'LVL' => 0.64401803386849,
			'LYD' => 1.39,
			'MAD' => 9.85,
			'MDL' => 19,
			'MGA' => 3160,
			'MGF' => 9149,
			'MKD' => 56,
			'MMK' => 1339,
			'MNT' => 2410,
			'MOP' => 7.84,
			'MRO' => 356,
			'MTL' => 0.39339124697602,
			'MUR' => 33,
			'MVR' => 15,
			'MWK' => 721,
			'MXN' => 19,
			'MYR' => 4.33,
			'MZM' => 70440,
			'MZN' => 70,
			'NAD' => 13,
			'NGN' => 312,
			'NIO' => 29,
			'NLG' => 2.02,
			'NOK' => 8.59,
			'NPR' => 101,
			'NZD' => 1.44,
			'OMR' => 0.38365,
			'PAB' => 1,
			'PEN' => 3.21,
			'PGK' => 3.13,
			'PHP' => 50,
			'PKR' => 104,
			'PLN' => 3.86,
			'PTE' => 184,
			'PYG' => 5467,
			'QAR' => 3.64,
			'ROL' => 41607,
			'RON' => 4.16,
			'RSD' => 112,
			'RUB' => 57,
			'RWF' => 822,
			'SAR' => 3.75,
			'SBD' => 7.69,
			'SCR' => 13,
			'SDD' => 665,
			'SDG' => 6.65,
			'SDP' => 2272,
			'SEK' => 8.83,
			'SGD' => 1.39,
			'SHP' => 0.7742200167341,
			'SIT' => 220,
			'SKK' => 28,
			'SLL' => 7451,
			'SOS' => 545,
			'SRD' => 7.48,
			'SRG' => 7480,
			'STD' => 22420,
			'SVC' => 8.54,
			'SYP' => 215,
			'SZL' => 13,
			'THB' => 34,
			'TJS' => 8.5,
			'TMM' => 17022,
			'TMT' => 3.4,
			'TND' => 2.41,
			'TOP' => 2.31,
			'TRL' => 3540300,
			'TRY' => 3.54,
			'TTD' => 6.65,
			'TWD' => 30,
			'TZS' => 2213,
			'UAH' => 26,
			'UGX' => 3612,
			'USD' => 1,
			'UYU' => 28,
			'UZS' => 3728,
			'VEB' => 10022,
			'VEF' => 10,
			'VND' => 22484,
			'VUV' => 107,
			'WST' => 2.59,
			'XAF' => 601,
			'XAG' => 0.059131500177986,
			'XAU' => 0.00079611410970997,
			'XCD' => 2.69,
			'XEU' => 0.91635510593065,
			'XOF' => 600,
			'XPD' => 0.001223538648212,
			'XPF' => 109,
			'XPT' => 0.001073866397524,
			'YER' => 250,
			'YUN' => 112,
			'ZAR' => 13,
			'ZMK' => 5328,
			'ZWD' => 376,
		);

		return $currencyRates;
	}
}
