<?php

namespace Omnimail\Silverpop\Connector;

use SilverpopConnector\SilverpopBaseConnector;
use SilverpopConnector\SilverpopConnectorException;
use SilverpopConnector\SilverpopRestConnector;
use SilverpopConnector\SilverpopConnector;
use Omnimail\Silverpop\Connector\SilverpopGuzzleXmlConnector;

/**
 * This is an extension of the Silverpop Connector provided by the SilverpopConnector package.
 *
 * It overrides the parent class to return the SilverpopGuzzleXmlConnector rather than the
 * SilverpopXmlConnector.
 *
 * Using Guzzle rather than CURL means we can add listeners to the request and offer mock
 * responses during testing. The Guzzle class also has some additional functions submitted
 * in this PR https://github.com/mrmarkfrench/silverpop-php-connector/pull/25
 *
 * UPDATE to below - since writing this some patches I wrote have been merged upstream
 * and I feel positive about getting those things merged & switching back to the main repo.
 * I have retained these override classes for now but probably will switch back
 *
 * Longer term I'm on the fence as to whether to simply improve the classes
 * in this package to the point where the dependency on the silverpop-php-connector package gets
 * removed. There are a couple of things I don't like about the silverpop-php-connector package
 * 1) [UPDATE: have support for structure change to address this]
 *   The addition of api calls appears to be adhoc and, in particular, I feel like the choice of parameters
 * the functions take feels a bit arbitrary and it's not clear how you extend those
 * 2) [UPDATE: NO LONGER TRUE] Although the package is active I have yet to get a response to the PR I submitted.
 * 3) This is kind of really minor, but also quite a pain... The retention of trailing spaces means
 * I have to reconfigure my IDE to work with this code. It's not a convention I hit anywhere else.
 *
 * On the other hand
 * 1) the package is quite active
 * 2) I think the way it wraps the different interfaces is quite nice. I did start with a different
 * package & switched to this as it seemed more developed & active.
 * 3) The intent of the Omnimail package is to wrap & standardise the underlying package for upstream use,
 * so there is no pressing need to change the underlying package.
 */
class SilverpopGuzzleConnector extends SilverpopConnector{

    /**
     * Instance of the connector object.
     *
     * @var SilverpopBaseConnector
     */
    protected static $instance=null;

    /**
     * Construct a connector object. If you will be authenticating with only a
     * single set of credentials, it is recommended that you use the singleton
     * getInstance() method instead. Use this constructor if you require
     * multiple connector objects for more than one set of credentials.
     *
     * Overridden to return the SilverpopGuzzleXmlConnector.
     *
     * @param string $baseUrl The base API URL for all requests.
     */
    public function __construct($baseUrl='http://api.pilot.silverpop.com', $dateFormat='MM/dd/yyyy') {
        $this->restConnector = SilverpopRestConnector::getInstance();
        $this->xmlConnector  = SilverpopGuzzleXmlConnector::getInstance();
        $this->setBaseUrl($baseUrl);
        $this->setDateFormat($dateFormat);
    }

    /**
     * Get a singleton instance of the connector. If you will be
     * authenticating with only a single set of credentials, fetching a
     * singleton may be simpler for your code to manage than creating your
     * own instance object which you are required to manage by hand. If,
     * however, you need multiple connectors in order to connect with
     * different sets of credentials, you should call the constructor to
     * obtain individual SilverpopConnector objects.
     *
     * Note that this method is implemented with "static" not "self", so
     * if you extend the connector to add your own functionality, you can
     * continue to use the singleton provided by this method by calling
     * YourChildClassName::getInstance(), but you will need to provide a
     * "protected static $instance=null;" property in your child class
     * for this method to reference.
     *
     * @return SilverpopConnector
     */
    public static function getInstance($baseUrl='http://api.pilot.silverpop.com', $dateFormat='MM/dd/yyyy', $timeout=10.0) {
        if (static::$instance == null) {
            static::$instance = new static($baseUrl);
        }
        return static::$instance;
    }

    /**
     * Performs Silverpop authentication using the supplied REST credentials,
     * or with the cached credentials if none are supplied. Any new credentials
     * will be cached for the next request.
     *
     * Overridden to return the SilverpopGuzzleXmlConnector.
     *
     * @param string $clientId
     * @param string $clientSecret
     *
     * @return mixed|void
     * @throws SilverpopConnectorException
     */
    public function authenticateXml($username=null, $password=null) {
        $this->username = empty($username) ? $this->username : $username;
        $this->password = empty($password) ? $this->password : $password;

        $this->xmlConnector = SilverpopGuzzleXmlConnector::getInstance();
        $this->xmlConnector->authenticate(
            $this->username,
            $this->password);
    }

}
