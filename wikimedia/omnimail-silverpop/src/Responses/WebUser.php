<?php
/**
 * Created by IntelliJ IDEA.
 * User: emcnaughton
 * Date: 5/4/17
 * Time: 7:34 AM
 */

namespace Omnimail\Silverpop\Responses;

use SilverpopConnector\SilverpopConnector;
use SilverpopConnector\SilverpopConnectorException;
use Omnimail\Exception\InvalidRequestException;

class WebUser
{
    /**
     * @var  SilverpopConnector
     */
    protected $silverPop;

    /**
     * @var array
     */
    protected $data = [];

    /**
     * @var string
     */
    protected $email;

    /**
     * Provider identifier for the sent mailing.
     *
     * @var string
     */
    protected $mailingIdentifier;

    /**
     *  Provider identifier for the contact.
     *
     * @var string
     */
    protected $contactIdentifier;

    /**
     * @var int Recipient Action.
     *   One of the event constants.
     */
    protected $recipientAction;

    /**
     * @var string timestamp
     */
    protected $recipientActionTimestamp;

    /**
     * @var string
     */
    protected $contactReference;

    public function __construct($data)
    {
        $this->data = $data;
    }

    /**
     * @return string
     */
    public function getEmail()
    {
        return addslashes((string) $this->email ?: $this->data['Email']);
    }

    /**
     * @param string $email
     */
    public function setEmail($email)
    {
        $this->email = $email;
    }

    /**
     * @return mixed
     */
    public function getRecipientReferrerType()
    {
        return $this->data['REFERRER_TYPE'];
    }

    /**
     * @return mixed
     */
    public function getRecipientReferrerUrl()
    {
        return $this->data['SESSION_LEAD_SOURCE'];
    }

    /**
     * @return mixed
     */
    public function getRecipientActionUrl()
    {
        return $this->data['SITE_URL'];
    }

    /**
     * @return string
     */
    public function getRecipientActionUrlName(): string
    {
        return (string) $this->data['SITE_NAME'];
    }

    /**
     * @return mixed
     */
    public function getRecipientActionUrlIdentifier()
    {
        return $this->data['SITE_ID'];
    }

    /**
     * @return mixed
     */
    public function getMailingName()
    {
        return $this->data['REFERRER_NAME'];
    }

    /**
     * @return string
     */
    public function getRecipientAction(): string
    {
        return (string) $this->data['EVENT_TYPE_NAME'];
    }

    /**
     * @return mixed
     */
    public function getRecipientActionName()
    {
        return $this->data['EVENT_NAME'];
    }

    /**
     * @param mixed $recipientAction
     */
    public function setRecipientAction($recipientAction)
    {
        $this->recipientAction = $recipientAction;
    }

    /**
     * @return mixed
     */
    public function getRecipientActionTimestamp()
    {
        return strtotime($this->data['EVENT_TS'] . ' GMT');
    }

    public function getRecipientActionIsoDateTime() {
        return (date('Y-m-d H:i:s', $this->getRecipientActionTimestamp()));
    }

    /**
     * @param mixed $recipientActionTimestamp
     */
    public function setRecipientActionTimestamp($recipientActionTimestamp)
    {
        $this->recipientActionTimestamp = $recipientActionTimestamp;
    }

    /**
     * @return mixed
     */
    public function getContactReference()
    {
        return (string) $this->contactReference ?: $this->data['ContactID'];
    }

    /**
     * @param mixed $contactReference
     */
    public function setContactReference($contactReference)
    {
        $this->contactReference = $contactReference;
    }

    /**
     * @return mixed
     */
    public function getContactIdentifier()
    {
        return $this->contactIdentifier ?: $this->data['RECIPIENT_ID'];
    }

    /**
     * @param mixed $contactIdentifier
     */
    public function setContactIdentifier($contactIdentifier)
    {
        $this->contactIdentifier = $contactIdentifier;
    }

    /**
     * @var string The field that maps to the contact reference.
     *
     *   In silverpop this is a totally custom field so it could be 'anything'.
     *   We use ContactID as a default, but if you use something different you
     *   need to set the field name.
     */
    protected $contactReferenceField;

    /**
     * @return string
     */
    public function getContactReferenceField() {
         return empty($this->contactReferenceField) ? 'ContactID' : $this->contactReferenceField;
    }

    /**
     * @param string $contactReferenceField
     */
    public function setContactReferenceField($contactReferenceField) {
        $this->contactReferenceField = $contactReferenceField;
    }

    public function getMailingIdentifier(): string
    {
        return (string) $this->data['REFERRER_MAILING_ID'];
    }

    /**
     * Get Silverpop connector object.
     *
     * @return \SilverpopConnector\SilverpopXmlConnector
     */
    protected function getSilverPop()
    {
        if (!$this->silverPop) {
            $this->silverPop = SilverpopConnector::getInstance();
        }
        return $this->silverPop;
    }

}
