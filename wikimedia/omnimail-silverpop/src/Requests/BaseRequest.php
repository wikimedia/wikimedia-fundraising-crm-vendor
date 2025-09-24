<?php
/**
 * Created by IntelliJ IDEA.
 * User: emcnaughton
 * Date: 5/4/17
 * Time: 7:34 AM
 */
namespace Omnimail\Silverpop\Requests;

use Omnimail\Common\Credentials;
use Omnimail\Common\Helper;
use SilverpopConnector\SilverpopConnector;
use SilverpopConnector\SilverpopXmlConnector;
use SilverpopConnector\SilverpopRestConnector;

abstract class BaseRequest implements RequestInterface
{
  /**
   * @var \SilverpopConnector\SilverpopXmlConnector
   */
  protected $silverPop;

  private $restConnector;

  /**
   * Timestamp for start of period.
   *
   * @var int
   */
  protected $startTimeStamp;

  /**
   * Timestamp for end of period.
   *
   * @var int
   */
  protected $endTimeStamp;

  /**
   * Url to direct requests to.
   *
   * @var string
   */
  protected $endPoint;

  /**
   * Domain for sftp endpoint.
   *
   * @var string
   */
  private $sftpEndPoint;

  /**
   * User name
   *
   * @var string
   */
  protected $username;

  /**
   * Password
   *
   * @var string
   */
  protected $password;

  /**
   * Guzzle client, overridable with mock object in tests.
   *
   * @var \GuzzleHttp\Client
   */
  protected $client;

  protected $xmlConnector;

  protected float $timeout = 10;

  public function getTimeout(): float {
    return $this->timeout;
  }

  public function setTimeout(float $timeout): void {
    $this->timeout = $timeout;
  }

    /**
     * @var \Omnimail\Common\Credentials
     */
  protected $credentials;

    /**
     * @return Credentials
     */
    public function getCredentials()
    {
        return $this->credentials;
    }

    /**
     * @param Credentials $credentials
     */
    public function setCredentials($credentials)
    {
        $this->credentials = $credentials;
    }

  /**
   * @return \GuzzleHttp\Client
   */
  public function getClient() {
    return $this->client;
  }

  /**
   * @param \GuzzleHttp\Client $client
   */
  public function setClient($client) {
    $this->client = $client;
  }

  /**
   * @return string
   */
  public function getEndPoint() {
    return $this->endPoint;
  }

  /**
   * @param string $endPoint
   */
  public function setEndPoint($endPoint) {
    $this->endPoint = $endPoint;
  }

  /**
   * @return string
   */
  public function getSftpEndPoint() {
    return $this->sftpEndPoint;
  }

  /**
   * @param string $endPoint
   */
  public function setSftpEndPoint(string $endPoint) {
    $this->sftpEndPoint = $endPoint;
  }

  /**
   * @return int
   */
  public function getStartTimeStamp() {
    return $this->startTimeStamp;
  }

  /**
   * @param int $startTimeStamp
   */
  public function setStartTimeStamp($startTimeStamp) {
    $this->startTimeStamp = $startTimeStamp;
  }

  /**
   * @return int
   */
  public function getEndTimeStamp() {
    return $this->endTimeStamp;
  }

  /**
   * @param int $endTimeStamp
   */
  public function setEndTimeStamp($endTimeStamp) {
    $this->endTimeStamp = $endTimeStamp;
  }

  /**
   * @return mixed
   */
  public function getUsername() {
    return $this->userName;
  }

  /**
   * @param mixed $userName
   */
  public function setUsername($userName) {
    $this->userName = $userName;
  }

  /**
   * @return mixed
   */
  public function getPassword() {
    return $this->password;
  }

  /**
   * @param mixed $password
   */
  public function setPassword($password) {
    $this->password = $password;
  }

    /**
     * Get a credential parameter.
     *
     * @return mixed
     */
    public function getCredential($parameter)
    {
        return $this->credentials->get($parameter);
    }

    /**
     * BaseRequest constructor.
     *
     * @param $parameters
     */
  public function __construct($parameters) {
    Helper::initialize($this, array_merge($this->getDefaultParameters(), $parameters));
    $this->silverPop = SilverpopConnector::getInstance($this->getEndPoint(), 'MM/dd/yyyy', $parameters['timeout'] ?? 10.0);
    if (isset($parameters['timeout'])) {
        $this->silverPop->setTimeout($parameters['timeout']);
    }
    if ($this->sftpEndPoint) {
        $this->silverPop->setSftpUrl($this->sftpEndPoint);
    }
    $this->silverPop->setClientId($this->getCredential('client_id'));
    $this->silverPop->setClientSecret($this->getCredential('client_secret'));
    $this->silverPop->setRefreshToken($this->getCredential('refresh_token'));
    $this->silverPop->setPassword($this->getCredential('password'));
    $this->silverPop->setUsername($this->getCredential('username'));
    $this->restConnector = SilverpopRestConnector::getInstance();
    $this->xmlConnector = SilverpopXmlConnector::getInstance();
    // Below here we probably can simplify to consistent setClient
    // which authenticates for both now.
    if (!empty($parameters['is_use_rest'])) {
      if (!empty($parameters['client'])) {
        $this->restConnector->setClient($parameters['client']);
      }
      $this->setDatabaseId($this->getCredential('database_id'));
      $this->xmlConnector->setClient($this->restConnector->getClient());
    }
    else {
      if ($this->client) {
        $this->xmlConnector->setClient($this->client);
      }
      $this->silverPop->authenticateXml($this->getCredential('username'), $this->getCredential('password'));
      $this->restConnector->setClient($this->xmlConnector->getClient());
    }
  }

  /**
   * Get defaults for the api.
   *
   * @return array
   */
  public function getDefaultParameters() {
    return [
      'endpoint' => 'https://api-campaign-us-4.goacoustic.com',
      'sftpEndPoint' => 'transfer-campaign-us-4.goacoustic.com',
    ];
  }

}
