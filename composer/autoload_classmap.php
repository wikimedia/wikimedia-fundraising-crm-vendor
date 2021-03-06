<?php

// autoload_classmap.php @generated by Composer

$vendorDir = dirname(dirname(__FILE__));
$baseDir = dirname($vendorDir);

return array(
    'AdyenAdapter' => $vendorDir . '/wikimedia/donation-interface/adyen_gateway/adyen.adapter.php',
    'AdyenGateway' => $vendorDir . '/wikimedia/donation-interface/adyen_gateway/adyen_gateway.body.php',
    'AdyenGatewayResult' => $vendorDir . '/wikimedia/donation-interface/adyen_gateway/adyen_resultswitcher.body.php',
    'AdyenHostedSignature' => $vendorDir . '/wikimedia/donation-interface/adyen_gateway/AdyenHostedSignature.php',
    'AdyenMethodCodec' => $vendorDir . '/wikimedia/donation-interface/adyen_gateway/AdyenMethodCodec.php',
    'AdyenSubmethodCodec' => $vendorDir . '/wikimedia/donation-interface/adyen_gateway/AdyenSubmethodCodec.php',
    'AmazonAdapter' => $vendorDir . '/wikimedia/donation-interface/amazon_gateway/amazon.adapter.php',
    'AmazonBillingApi' => $vendorDir . '/wikimedia/donation-interface/amazon_gateway/amazon.api.php',
    'AmazonGateway' => $vendorDir . '/wikimedia/donation-interface/amazon_gateway/amazon_gateway.body.php',
    'Amount' => $vendorDir . '/wikimedia/donation-interface/gateway_common/Amount.php',
    'AmountInCents' => $vendorDir . '/wikimedia/donation-interface/gateway_common/AmountInCents.php',
    'AmountInMinorUnits' => $vendorDir . '/wikimedia/donation-interface/adyen_gateway/AmountInMinorUnits.php',
    'ArithmeticError' => $vendorDir . '/symfony/polyfill-php70/Resources/stubs/ArithmeticError.php',
    'ArrayHelper' => $vendorDir . '/wikimedia/donation-interface/gateway_common/ArrayHelper.php',
    'AssertionError' => $vendorDir . '/symfony/polyfill-php70/Resources/stubs/AssertionError.php',
    'AstroPayAdapter' => $vendorDir . '/wikimedia/donation-interface/astropay_gateway/astropay.adapter.php',
    'AstroPayFinancialNumbers' => $vendorDir . '/wikimedia/donation-interface/astropay_gateway/AstroPayFinancialNumbers.php',
    'AstroPayGateway' => $vendorDir . '/wikimedia/donation-interface/astropay_gateway/astropay_gateway.body.php',
    'AstroPayGatewayResult' => $vendorDir . '/wikimedia/donation-interface/astropay_gateway/astropay_resultswitcher.body.php',
    'AstroPayMethodCodec' => $vendorDir . '/wikimedia/donation-interface/astropay_gateway/AstroPayMethodCodec.php',
    'AstroPaySignature' => $vendorDir . '/wikimedia/donation-interface/astropay_gateway/AstroPaySignature.php',
    'AstroPayStatusQuery' => $vendorDir . '/wikimedia/donation-interface/astropay_gateway/scripts/status.php',
    'BannerHistoryLogIdProcessor' => $vendorDir . '/wikimedia/donation-interface/extras/banner_history/BannerHistoryLogIdProcessor.php',
    'BlankAddressFields' => $vendorDir . '/wikimedia/donation-interface/adyen_gateway/BlankAddressFields.php',
    'CleanupRecurringLength' => $vendorDir . '/wikimedia/donation-interface/paypal_gateway/CleanupRecurringLength.php',
    'ClientErrorApi' => $vendorDir . '/wikimedia/donation-interface/gateway_common/clientError.api.php',
    'ClientSideValidationHelper' => $vendorDir . '/wikimedia/donation-interface/gateway_common/ClientSideValidationHelper.php',
    'Composer\\InstalledVersions' => $vendorDir . '/composer/InstalledVersions.php',
    'ConfigurationReader' => $vendorDir . '/wikimedia/donation-interface/gateway_common/ConfigurationReader.php',
    'Console_Table' => $vendorDir . '/pear/console_table/Table.php',
    'ContributionTrackingPlusUnique' => $vendorDir . '/wikimedia/donation-interface/gateway_common/ContributionTrackingPlusUnique.php',
    'CountryValidation' => $vendorDir . '/wikimedia/donation-interface/gateway_common/CountryValidation.php',
    'CurrencyCountryRule' => $vendorDir . '/wikimedia/donation-interface/gateway_common/CurrencyCountryRule.php',
    'DataValidator' => $vendorDir . '/wikimedia/donation-interface/gateway_common/DataValidator.php',
    'DivisionByZeroError' => $vendorDir . '/symfony/polyfill-php70/Resources/stubs/DivisionByZeroError.php',
    'DonationApi' => $vendorDir . '/wikimedia/donation-interface/gateway_common/donation.api.php',
    'DonationApiBase' => $vendorDir . '/wikimedia/donation-interface/gateway_common/DonationApiBase.php',
    'DonationData' => $vendorDir . '/wikimedia/donation-interface/gateway_common/DonationData.php',
    'DonationInterface' => $vendorDir . '/wikimedia/donation-interface/DonationInterface.class.php',
    'DonationLogProcessor' => $vendorDir . '/wikimedia/donation-interface/gateway_common/DonationLogProcessor.php',
    'DonationLoggerFactory' => $vendorDir . '/wikimedia/donation-interface/gateway_common/DonationLoggerFactory.php',
    'DonationProfiler' => $vendorDir . '/wikimedia/donation-interface/gateway_common/DonationProfiler.php',
    'DonorEmail' => $vendorDir . '/wikimedia/donation-interface/gateway_common/DonorEmail.php',
    'DonorFullName' => $vendorDir . '/wikimedia/donation-interface/gateway_common/DonorFullName.php',
    'DonorLanguage' => $vendorDir . '/wikimedia/donation-interface/gateway_common/DonorLanguage.php',
    'DonorLocale' => $vendorDir . '/wikimedia/donation-interface/gateway_common/DonorLocale.php',
    'DrupalFinder\\DrupalFinder' => $vendorDir . '/webflo/drupal-finder/src/DrupalFinder.php',
    'EmployerSearchAPI' => $vendorDir . '/wikimedia/donation-interface/gateway_common/employerSearch.api.php',
    'EncodingMangler' => $vendorDir . '/wikimedia/donation-interface/gateway_common/EncodingMangler.php',
    'EndowmentHooks' => $vendorDir . '/wikimedia/donation-interface/gateway_common/EndowmentHooks.php',
    'Error' => $vendorDir . '/symfony/polyfill-php70/Resources/stubs/Error.php',
    'ErrorState' => $vendorDir . '/wikimedia/donation-interface/gateway_common/ErrorState.php',
    'FallbackLogPrefixer' => $vendorDir . '/wikimedia/donation-interface/gateway_common/FallbackLogPrefixer.php',
    'FiscalNumber' => $vendorDir . '/wikimedia/donation-interface/gateway_common/FiscalNumber.php',
    'FraudFilter' => $vendorDir . '/wikimedia/donation-interface/extras/FraudFilter.php',
    'FullNameWithExceptions' => $vendorDir . '/wikimedia/donation-interface/adyen_gateway/FullNameWithExceptions.php',
    'GatewayAdapter' => $vendorDir . '/wikimedia/donation-interface/gateway_common/gateway.adapter.php',
    'GatewayPage' => $vendorDir . '/wikimedia/donation-interface/gateway_common/GatewayPage.php',
    'GatewayType' => $vendorDir . '/wikimedia/donation-interface/gateway_common/GatewayType.php',
    'Gateway_Extras' => $vendorDir . '/wikimedia/donation-interface/extras/extras.body.php',
    'Gateway_Extras_ConversionLog' => $vendorDir . '/wikimedia/donation-interface/extras/conversion_log/conversion_log.body.php',
    'Gateway_Extras_CustomFilters' => $vendorDir . '/wikimedia/donation-interface/extras/custom_filters/custom_filters.body.php',
    'Gateway_Extras_CustomFilters_Functions' => $vendorDir . '/wikimedia/donation-interface/extras/custom_filters/filters/functions/functions.body.php',
    'Gateway_Extras_CustomFilters_IP_Velocity' => $vendorDir . '/wikimedia/donation-interface/extras/custom_filters/filters/ip_velocity/ip_velocity.body.php',
    'Gateway_Extras_CustomFilters_MinFraud' => $vendorDir . '/wikimedia/donation-interface/extras/custom_filters/filters/minfraud/minfraud.body.php',
    'Gateway_Extras_CustomFilters_Referrer' => $vendorDir . '/wikimedia/donation-interface/extras/custom_filters/filters/referrer/referrer.body.php',
    'Gateway_Extras_CustomFilters_Source' => $vendorDir . '/wikimedia/donation-interface/extras/custom_filters/filters/source/source.body.php',
    'Gateway_Extras_SessionVelocityFilter' => $vendorDir . '/wikimedia/donation-interface/extras/session_velocity/session_velocity.body.php',
    'Gateway_Form' => $vendorDir . '/wikimedia/donation-interface/gateway_forms/Form.php',
    'Gateway_Form_Mustache' => $vendorDir . '/wikimedia/donation-interface/gateway_forms/Mustache.php',
    'GlobalCollect3DSecure' => $vendorDir . '/wikimedia/donation-interface/globalcollect_gateway/GlobalCollect3DSecure.php',
    'GlobalCollectAdapter' => $vendorDir . '/wikimedia/donation-interface/globalcollect_gateway/globalcollect.adapter.php',
    'GlobalCollectGateway' => $vendorDir . '/wikimedia/donation-interface/globalcollect_gateway/globalcollect_gateway.body.php',
    'GlobalCollectGatewayResult' => $vendorDir . '/wikimedia/donation-interface/globalcollect_gateway/globalcollect_resultswitcher.body.php',
    'GlobalCollectGetDirectory' => $vendorDir . '/wikimedia/donation-interface/globalcollect_gateway/scripts/get_directory.php',
    'GlobalCollectOrphanAdapter' => $vendorDir . '/wikimedia/donation-interface/globalcollect_gateway/orphan.adapter.php',
    'GlobalCollectOrphanRectifier' => $vendorDir . '/wikimedia/donation-interface/globalcollect_gateway/GlobalCollectOrphanRectifier.php',
    'GlobalCollectRefundMaintenance' => $vendorDir . '/wikimedia/donation-interface/globalcollect_gateway/scripts/refund.php',
    'Google_Service_Exception' => $vendorDir . '/google/apiclient/src/Google/Service/Exception.php',
    'Google_Service_Resource' => $vendorDir . '/google/apiclient/src/Google/Service/Resource.php',
    'Ingenico3DSecure' => $vendorDir . '/wikimedia/donation-interface/ingenico_gateway/Ingenico3DSecure.php',
    'IngenicoAdapter' => $vendorDir . '/wikimedia/donation-interface/ingenico_gateway/ingenico.adapter.php',
    'IngenicoFinancialNumber' => $vendorDir . '/wikimedia/donation-interface/globalcollect_gateway/IngenicoFinancialNumber.php',
    'IngenicoFormVariant' => $vendorDir . '/wikimedia/donation-interface/ingenico_gateway/IngenicoFormVariant.php',
    'IngenicoGateway' => $vendorDir . '/wikimedia/donation-interface/ingenico_gateway/ingenico_gateway.body.php',
    'IngenicoGatewayResult' => $vendorDir . '/wikimedia/donation-interface/ingenico_gateway/ingenico_resultswitcher.body.php',
    'IngenicoGetOrderStatusMaintenance' => $vendorDir . '/wikimedia/donation-interface/globalcollect_gateway/scripts/get_orderstatus.php',
    'IngenicoLanguage' => $vendorDir . '/wikimedia/donation-interface/globalcollect_gateway/IngenicoLanguage.php',
    'IngenicoLocale' => $vendorDir . '/wikimedia/donation-interface/ingenico_gateway/IngenicoLocale.php',
    'IngenicoMethodCodec' => $vendorDir . '/wikimedia/donation-interface/globalcollect_gateway/IngenicoMethodCodec.php',
    'IngenicoOrphanAdapter' => $vendorDir . '/wikimedia/donation-interface/ingenico_gateway/orphan.adapter.php',
    'IngenicoOrphanRectifier' => $vendorDir . '/wikimedia/donation-interface/ingenico_gateway/IngenicoOrphanRectifier.php',
    'IngenicoPaymentSubmethod' => $vendorDir . '/wikimedia/donation-interface/ingenico_gateway/IngenicoPaymentSubmethod.php',
    'IngenicoReturntoHelper' => $vendorDir . '/wikimedia/donation-interface/globalcollect_gateway/IngenicoReturntoHelper.php',
    'IsoDate' => $vendorDir . '/wikimedia/donation-interface/gateway_common/IsoDate.php',
    'LocalClusterPsr6Cache' => $vendorDir . '/wikimedia/donation-interface/gateway_common/LocalClusterPsr6Cache.php',
    'LogPrefixProvider' => $vendorDir . '/wikimedia/donation-interface/gateway_common/LogPrefixProvider.php',
    'MessageUtils' => $vendorDir . '/wikimedia/donation-interface/gateway_common/MessageUtils.php',
    'MustacheErrorForm' => $vendorDir . '/wikimedia/donation-interface/gateway_forms/MustacheErrorForm.php',
    'MustacheHelper' => $vendorDir . '/wikimedia/donation-interface/gateway_forms/MustacheHelper.php',
    'Normalizer' => $vendorDir . '/symfony/polyfill-intl-normalizer/Resources/stubs/Normalizer.php',
    'ParseError' => $vendorDir . '/symfony/polyfill-php70/Resources/stubs/ParseError.php',
    'PayPalCountry' => $vendorDir . '/wikimedia/donation-interface/paypal_gateway/PayPalCountry.php',
    'PaymentMethod' => $vendorDir . '/wikimedia/donation-interface/gateway_common/PaymentMethod.php',
    'PaymentResult' => $vendorDir . '/wikimedia/donation-interface/gateway_common/PaymentResult.php',
    'PaymentTransactionResponse' => $vendorDir . '/wikimedia/donation-interface/gateway_common/PaymentTransactionResponse.php',
    'PaypalExpressAdapter' => $vendorDir . '/wikimedia/donation-interface/paypal_gateway/express_checkout/paypal_express.adapter.php',
    'PaypalExpressGateway' => $vendorDir . '/wikimedia/donation-interface/paypal_gateway/express_checkout/paypal_express_gateway.body.php',
    'PaypalExpressGatewayResult' => $vendorDir . '/wikimedia/donation-interface/paypal_gateway/express_checkout/paypal_express_resultswitcher.body.php',
    'PaypalExpressReturnUrl' => $vendorDir . '/wikimedia/donation-interface/paypal_gateway/express_checkout/PaypalExpressReturnUrl.php',
    'PaypalLegacyAdapter' => $vendorDir . '/wikimedia/donation-interface/paypal_gateway/legacy/paypal_legacy.adapter.php',
    'PaypalLegacyGateway' => $vendorDir . '/wikimedia/donation-interface/paypal_gateway/legacy/paypal_legacy_gateway.body.php',
    'PaypalLegacyLocale' => $vendorDir . '/wikimedia/donation-interface/paypal_gateway/legacy/PaypalLegacyLocale.php',
    'PaypalRefundMaintenance' => $vendorDir . '/wikimedia/donation-interface/paypal_gateway/scripts/refund.php',
    'PlaceholderFiscalNumber' => $vendorDir . '/wikimedia/donation-interface/astropay_gateway/PlaceholderFiscalNumber.php',
    'RecurringConversion' => $vendorDir . '/wikimedia/donation-interface/gateway_common/RecurringConversion.php',
    'RecurringConversionApi' => $vendorDir . '/wikimedia/donation-interface/gateway_common/RecurringConversion.api.php',
    'ResponseProcessingException' => $vendorDir . '/wikimedia/donation-interface/gateway_common/ResponseProcessingException.php',
    'ResultPages' => $vendorDir . '/wikimedia/donation-interface/gateway_common/ResultPages.php',
    'ResultSwitcher' => $vendorDir . '/wikimedia/donation-interface/gateway_common/ResultSwitcher.php',
    'RiskScore' => $vendorDir . '/wikimedia/donation-interface/adyen_gateway/RiskScore.php',
    'SessionUpdateTimestampHandlerInterface' => $vendorDir . '/symfony/polyfill-php70/Resources/stubs/SessionUpdateTimestampHandlerInterface.php',
    'StagingHelper' => $vendorDir . '/wikimedia/donation-interface/gateway_common/StagingHelper.php',
    'StreetAddress' => $vendorDir . '/wikimedia/donation-interface/gateway_common/StreetAddress.php',
    'Subdivisions' => $vendorDir . '/wikimedia/donation-interface/gateway_forms/includes/Subdivisions.php',
    'TypeError' => $vendorDir . '/symfony/polyfill-php70/Resources/stubs/TypeError.php',
    'UnstagingHelper' => $vendorDir . '/wikimedia/donation-interface/gateway_common/UnstagingHelper.php',
    'ValidationHelper' => $vendorDir . '/wikimedia/donation-interface/gateway_common/ValidationHelper.php',
    'WmfFrameworkLogHandler' => $vendorDir . '/wikimedia/donation-interface/gateway_common/WmfFrameworkLogHandler.php',
    'WmfFramework_Drupal' => $vendorDir . '/wikimedia/donation-interface/gateway_common/WmfFramework.drupal.php',
    'WmfFramework_Mediawiki' => $vendorDir . '/wikimedia/donation-interface/gateway_common/WmfFramework.mediawiki.php',
);
