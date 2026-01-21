<?php
/**
 * Created by IntelliJ IDEA.
 * User: emcnaughton
 * Date: 5/4/17
 * Time: 7:34 AM
 */
namespace Omnimail\Silverpop\Responses;

class WebTrackingResponse extends DownloadResponse
{

    /**
     * This is like the other response class - but neither seem to be called.
     * Map keys to a normalised array, intended to be generic of the provider.
     *
     * @param array $csvHeaders
     *
     * @return array
     *   New headers, normalized to something other providers might use.
    public function normalizeKeys($csvHeaders) {
        $newHeaders = [];
        $normalizedKeys = [
            'RECIPIENT_ID' => 'contact_identifier',
            'REFERRER_TYPE' => 'referrer_type',
            'REFERRER_NAME' => 'mailing_name',
            'REFERRER_MAILING_ID' => 'mailing_id',
            'SESSION_LEAD_SOURCE' => 'referrer_url',
            'SITE_URL' => 'action_url',
            'SITE_NAME' => 'action_url_name',
            'EVENT_TS' => 'recipient_action_timestamp',
            'EVENT_TYPE_NAME' => 'receipient_action',
            'Email' => 'email',
            'ContactID' => 'contact_id',
        ];
        foreach ($csvHeaders as $csvHeader) {
            if (isset($normalizedKeys[$csvHeader])) {
                $newHeaders[] = $normalizedKeys[$csvHeader];
            }
            else {
                $newHeaders[] = strtolower(str_replace(' ', '_', $csvHeader));
            }
        }
        return $newHeaders;
    }
    */

}
