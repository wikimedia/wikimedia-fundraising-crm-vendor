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
use Omnimail\Silverpop\Connector\SilverpopGuzzleConnector;
use Omnimail\Silverpop\Responses\ResponseInterface;
use SilverpopConnector\SilverpopConnector;

abstract class BaseRequest implements RequestInterface
{
  /**
   * @var \SilverpopConnector\SilverpopXmlConnector
   */
  protected $silverPop;

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
    $this->silverPop = SilverpopGuzzleConnector::getInstance($this->getEndPoint());
    if ($this->client) {
      $this->silverPop->setClient($this->client);
    }
    $this->silverPop->authenticateXml($this->getCredential('username'), $this->getCredential('password'));
  }

  /**
   * Get defaults for the api.
   *
   * @return array
   */
  public function getDefaultParameters() {
    return array(
      'endpoint' => 'https://api4.silverpop.com',
    );
  }

}
