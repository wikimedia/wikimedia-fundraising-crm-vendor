<?php
/**
 * Donation Interface - Lang only
 *
 * When installed, this will *ONLY* load i18n messages from DonationInterface. This
 * will not expose any other DonationInterface functionality.
 *
 * To install the DontaionInterface extension, put the following line in LocalSettings.php:
 * require_once( "\$IP/extensions/DonationInterface/donationinterface_langonly.php" );
 *
 */

# Alert the user that this is not a valid entry point to MediaWiki if they try to access the special pages file directly.
if ( !defined( 'MEDIAWIKI' ) ) {
	echo <<<EOT
To install the DontaionInterface lang only extension, put the following line in LocalSettings.php:
require_once( "\$IP/extensions/DonationInterface/donationinterface_langonly.php" );
EOT;
	exit( 1 );
}

// Extension credits that will show up on Special:Version
$wgExtensionCredits['specialpage'][] = array(
	'name' => 'Donation Interface - Language Only',
	'author' => array( 'Katie Horn', 'Ryan Kaldari' , 'Arthur Richards', 'Matt Walker', 'Adam Wight', 'Peter Gehres', 'Jeremy Postlethwaite' ),
	'version' => '2.0.0',
	'descriptionmsg' => 'donate_interface-langonly-desc',
	'url' => 'https://www.mediawiki.org/wiki/Extension:DonationInterface',
);

// Load the interface messages that are shared across all gateways
$wgMessagesDirs['DonationInterface'][] = __DIR__ . '/gateway_common/i18n/interface';
$wgMessagesDirs['DonationInterface'][] = __DIR__ . '/gateway_common/i18n/countries';
$wgMessagesDirs['DonationInterface'][] = __DIR__ . '/gateway_common/i18n/us-states';
$wgMessagesDirs['DonationInterface'][] = __DIR__ . '/gateway_common/i18n/canada-provinces';

// GlobalCollect-specific messaging
$wgMessagesDirs['DonationInterface'][] = __DIR__ . '/globalcollect_gateway/i18n';
$wgExtensionMessagesFiles['GlobalCollectGatewayAlias'] = __DIR__ . '/globalcollect_gateway/globalcollect_gateway.alias.php';
