<?php

namespace Omnimail\Silverpop\Connector;

use Omnimail\Silverpop\Connector\Xml\GetAggregateTrackingForMailing;
use Omnimail\Silverpop\Connector\Xml\CalculateQuery;
use Omnimail\Silverpop\Connector\Xml\GetMailingTemplate;
use SilverpopConnector\SilverpopRestConnector;
use SilverpopConnector\SilverpopXmlConnector;
use SilverpopConnector\SilverpopConnectorException;
use SimpleXmlElement;
use phpseclib\Net\Sftp;
use GuzzleHttp\Client;

/**
 * This is an override on SilverpopXmlConnector
 *
 * Currently it's not doing anything as, pending upstream attention to
 * https://github.com/mrmarkfrench/silverpop-php-connector/pull/27,
 * we are running on a fork of the silverpop-php-connector. On the fork, or with that
 * patch merged the GuzzleXml connector is actually not required.
 *
 * However, retaining it for now as easier to add overrides if we keep it.
 */
class SilverpopGuzzleXmlConnector extends SilverpopXmlConnector {
    protected static $instance = null;
    
}
