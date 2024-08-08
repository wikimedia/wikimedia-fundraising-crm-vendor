<?php namespace SmashPig\PaymentProviders\Gravy\Tests;

use SmashPig\Core\GlobalConfiguration;
use SmashPig\Tests\TestingProviderConfiguration;

class GravyTestConfiguration extends TestingProviderConfiguration {

	public static function instance( $mockApi, GlobalConfiguration $globalConfig ) {
		$config = static::createForProvider( 'gravy', $globalConfig );
		$config->objects['api'] = $mockApi;
		return $config;
	}

	public static function getSuccessfulApproveResult() {
		return [
			"type" => "transaction",
			"id" => "fe26475d-ec3e-4884-9553-f7356683f7f9",
			"amount" => 1299,
			"auth_response_code" => "00",
			"authorized_amount" => 1299,
			"authorized_at" => "2013-07-16T19:23:00.000+00:00",
			"approval_expires_at" => "2013-07-16T19:23:00.000+00:00",
			"avs_response_code" => "partial_match_address",
			"buyer" => [
				"type" => "buyer",
				"id" => "fe26475d-ec3e-4884-9553-f7356683f7f9",
				"billing_details" => [
					"type" => "billing-details",
					"first_name" => "John",
					"last_name" => "Lunn",
					"email_address" => "john@example.com",
					"phone_number" => "+1234567890",
					"address" => [
						"city" => "London",
						"country" => "GB",
						"postal_code" => "789123",
						"state" => "Greater London",
						"state_code" => "GB-LND",
						"house_number_or_name" => "10",
						"line1" => "10 Oxford Street",
						"line2" => "New Oxford Court",
						"organization" => "Gr4vy",
					],
					"tax_id" => [
						"value" => "12345678931",
						"kind" => "gb.vat",
					],
				],
				"display_name" => "John L.",
				"external_identifier" => "user-789123",
			],
			"captured_amount" => 999,
			"captured_at" => "2013-07-16T19:23:00.000+00:00",
			"cart_items" => [],
			"checkout_session_id" => "fe26475d-ec3e-4884-9553-f7356683f7f9",
			"country" => "US",
			"created_at" => "2013-07-16T19:23:00.000+00:00",
			"currency" => "USD",
			"cvv_response_code" => "match",
			"error_code" => "missing_redirect_url",
			"external_identifier" => "user-789123",
			"instrument_type" => "network_token",
			"intent" => "authorize",
			"intent_outcome" => "pending",
			"is_subsequent_payment" => true,
			"merchant_account_id" => "default",
			"merchant_initiated" => true,
			"metadata" => [
				"key" => "value",
			],
			"method" => "card",
			"multi_tender" => true,
			"payment_method" => [
				"type" => "payment-method",
				"id" => "77a76f7e-d2de-4bbc-ada9-d6a0015e6bd5",
				"approval_target" => "any",
				"approval_url" => "https://api.example.app.gr4vy.com/payment-methods/ffc88ec9-e1ee-45ba-993d-b5902c3b2a8c/approve",
				"country" => "US",
				"currency" => "USD",
				"details" => [
					"card_type" => "credit",
					"bin" => "412345",
				],
				"expiration_date" => "11/25",
				"external_identifier" => "user-789123",
				"label" => "1111",
				"last_replaced_at" => "2023-07-26T19:23:00.000+00:00",
				"method" => "card",
				"payment_account_reference" => "V0010014629724763377327521982",
				"scheme" => "visa",
				"fingerprint" => "20eb353620155d2b5fc864cc46a73ea77cb92c725238650839da1813fa987a17",
			],
			"payment_service" => [
				"type" => "payment-service",
				"id" => "stripe-card-faaad066-30b4-4997-a438-242b0752d7e1",
				"display_name" => "Stripe (Main)",
				"method" => "card",
				"payment_service_definition_id" => "stripe-card",
			],
			"payment_service_transaction_id" => "charge_xYqd43gySMtori",
			"payment_source" => "recurring",
			"pending_review" => true,
			"raw_response_code" => "incorrect-zip",
			"raw_response_description" => "The card's postal code is incorrect. Check the card's postal code or use a different card.",
			"reconciliation_id" => "7jZXl4gBUNl0CnaLEnfXbt",
			"refunded_amount" => 100,
			"scheme_transaction_id" => "123456789012345",
			"shipping_details" => [
				"type" => "shipping-details",
				"id" => "8724fd24-5489-4a5d-90fd-0604df7d3b83",
				"buyer_id" => "8724fd24-5489-4a5d-90fd-0604df7d3b83",
				"first_name" => "John",
				"last_name" => "Lunn",
				"email_address" => "john@example.com",
				"phone_number" => "+1234567890",
				"address" => [
					"city" => "London",
					"country" => "GB",
					"postal_code" => "789123",
					"state" => "Greater London",
					"state_code" => "GB-LND",
					"house_number_or_name" => "10",
					"line1" => "10 Oxford Street",
					"line2" => "New Oxford Court",
					"organization" => "Gr4vy",
				],
			],
			"statement_descriptor" => [
				"name" => "GR4VY",
				"description" => "Card payment",
				"city" => "London",
				"phone_number" => "+1234567890",
				"url" => "www.gr4vy.com",
			],
			"status" => "processing",
			"updated_at" => "2013-07-16T19:23:00.000+00:00",
			"voided_at" => "2013-07-16T19:23:00.000+00:00",
		];
	}

	public static function getErrorCreatePaymentResult() {
		return [
			'additionalData' => [
				'cvcResult' => 3,
				'avsResult' => 'Unavailable',
			],
			'pspReference' => 'MOCK_REFERENCE',
			'resultCode' => 'Error',
			'refusalReason' => 'Acquirer Error',
		];
	}

	public static function getSuccessfulCreatePaymentResult( $id ) {
		return [
			'additionalData' => [
				'cvcResult' => '6 No CVC/CVV provided',
				'authCode' => '099013',
				'avsResult' => '2 Neither postal code nor address match',
				'scaExemptionRequested' => 'lowValue',
				'paymentMethod' => 'visa',
				'paymentMethodVariant' => 'visa',
			],
			'pspReference' => '00000000000000AB',
			'resultCode' => 'Authorised',
			'amount' => [
				'currency' => 'USD',
				'value' => 1000,
			],
			'merchantReference' => $id,
		];
	}
}
