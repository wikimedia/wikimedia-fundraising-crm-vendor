<?php
/**
 * Created by IntelliJ IDEA.
 * User: emcnaughton
 * Date: 5/4/17
 * Time: 7:34 AM
 */

namespace Omnimail\Silverpop\Responses;

use Omnimail\Silverpop\Connector\SilverpopGuzzleConnector;
use SilverpopConnector\SilverpopConnector;
use SilverpopConnector\SilverpopConnectorException;
use Omnimail\Exception\InvalidRequestException;

class Mailing
{

    /**
     * @var array
     */
    protected $data = array();

    /**
     * @var \SimpleXMLElement
     */
    protected $template;

    /**
     * Statistical data retrieved if required.
     *
     * @var array
     */
    protected $statistics;

    /**
     * @var  SilverpopConnector
     */
    protected $silverPop;

    public function __construct($data)
    {
        $this->data = $data;
    }

    public function getName()
    {
        return (string)$this->data->MailingName;
    }

    public function getMailingIdentifier()
    {
        return (string)$this->data->MailingId;
    }

    public function getSubject()
    {
        return (string)$this->data->Subject;
    }

    public function getScheduledDate()
    {
        return strtotime((string)$this->data->ScheduledTS);
    }

    public function getSendStartDate()
    {
        return strtotime((string)$this->data->SentTS);
    }

    public function getHtmlBody()
    {
        if (!$this->template) {
            $this->setTemplate();
        }
        return (string)$this->template->HTMLBody;
    }

    public function getTextBody()
    {
        if (!$this->template) {
            $this->setTemplate();
        }
        return (string)$this->template->TextBody;
    }

    protected function setTemplate()
    {
        $silverPop = $this->getSilverPop();
        $this->template = $silverPop->getMailingTemplate(array('MailingId' => (string)$this->data->ParentTemplateId));
    }

    public function getNumberSent()
    {
        return $this->getStatistic('NumSent');
    }

    /**
     * Get the number of times emails from this mailing have been opened.
     *
     * An individual opening the email 5 times would count as 5 opens.
     *
     * @return int
     */
    public function getNumberOpens()
    {
        return $this->getStatistic('NumGrossOpen');
    }

    /**
     * Get the number of unique times emails from this mailing have been opened.
     *
     * An individual opening the email 5 times would count as 1 open.
     *
     * @return int
     */
    public function getNumberUniqueOpens()
    {
        return $this->getStatistic('NumUniqueOpen');
    }

    /**
     * Get the number of unsubscribes received from the mailing.
     *
     * @return int
     */
    public function getNumberUnsubscribes()
    {
        return $this->getStatistic('NumUnsubscribes');
    }

    /**
     * Get the number of abuse reports made about the mailing.
     *
     * Most commonly abuse reports include marking an email as spam.
     *
     * @return int
     */
    public function getNumberAbuseReports()
    {
        return $this->getStatistic('NumGrossAbuse');
    }

    /**
     * Get the number of bounces from the email.
     *
     * @return int
     */
    public function getNumberBounces()
    {
        return $this->getStatistic('NumBounceSoft');
    }

    /**
     * Get the number of clicks on the mailing.
     *
     * @return int
     */
    public function getNumberClicked()
    {
        return $this->getStatistic('NumGrossClick');
    }

    /**
     * Get the number of clicks from unique individuals on the mailing.
     *
     * @return int
     */
    public function getNumberUniqueClicked()
    {
        return $this->getStatistic('NumUniqueClick');
    }

    /**
     * Get the number of emails suppressed by the provider.
     *
     * Mailing providers may contain their own lists of contacts to not sent mail to.
     * This number reflects the number of emails not sent due to the provider
     * suppressing them.
     *
     * @return int
     */
    public function getNumberSuppressedByProvider()
    {
        return $this->getStatistic('NumSuppressed');
    }

    /**
     * Get the number of emails blocked by the recipient's provider.
     *
     * Providers such as AOL, gmail may block some or all of the emails
     * based on whitelisting and blacklisting. This returns that number.
     *
     * @return int
     */
    public function getNumberBlocked()
    {
        return $this->getStatistic('NumGrossMailBlock');
    }

    public function getRecipients()
    {
        if ($this->getNumberSent()) {
            // $job = $this->getSilverPop()->getJobStatus(97258708);
            //$file = $this->getSilverPop()->calculateQuery(array('QueryId' => (string) $this->data->ListId));
            $result = $this->getSilverPop()
                ->rawRecipientDataExport($this->getMailingIdentifier(), "MAILING_ID", array(), array('MOVE_TO_FTP', 'ALL_EVENT_TYPES'));
            $jobId = (string)$result['jobId'];
            $jobStatus = $this->getSilverPop()->getJobStatus($jobId);
        }
    }

    /**
     * Get the specified statistic.
     *
     * @param string $key
     *
     * @return int
     */
    protected function getStatistic($key)
    {
        if (!$this->statistics) {
            $this->setStatistics();
        }
        return (int) $this->statistics->$key;
    }

    /**
     * Set the statistics for the job.
     *
     * @throws \Omnimail\Exception\InvalidRequestException
     */
    protected function setStatistics()
    {
        $silverPop = $this->getSilverPop();
        try {
            $this->statistics = $silverPop->getAggregateTrackingForMailing(array(
                'MailingId' => $this->getMailingIdentifier(),
                'ReportId' => $this->getReportID(),
            ));
        } catch (SilverpopConnectorException $e) {
            if (stristr($e->getMessage(), 'is not valid')) {
                throw new InvalidRequestException($e->getMessage());
            }
        }
    }

    public function getReportID(): string
    {
        return (string) $this->data->ReportId;
    }

    /**
     * Get Silverpop connector object.
     *
     * @return \SilverpopConnector\SilverpopXmlConnector
     */
    protected function getSilverPop()
    {
        if (!$this->silverPop) {
            $this->silverPop = SilverpopGuzzleConnector::getInstance();
        }
        return $this->silverPop;
    }

    /**
     * Get the id of the query list the mailing was sent to.
     *
     * @return int
     */
    public function getListID()
    {
        return (string) $this->data->ListId;
    }

    /**
     * Get the name of the query list the mailing was sent to.
     *
     * @return string
     */
    public function getListName()
    {
        return (string) $this->data->ListName;
    }

}
