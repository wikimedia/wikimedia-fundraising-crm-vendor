<?php
/**
 * Created by IntelliJ IDEA.
 * User: emcnaughton
 * Date: 5/4/17
 * Time: 7:34 AM
 */
namespace Omnimail\Silverpop\Responses;

class RecipientsResponse extends DownloadResponse
{

  /**
   * Map keys to a normalised array, intended to be generic of the provider.
   * @todo - this appears to be unaccessed - a good intention. Remove this function.
   * @param array $csvHeaders
   *
   * @return array
   *   New headers, normalized to something other providers might use.
   */
  public function normalizeKeys($csvHeaders) {
    $newHeaders = [];
    $normalizedKeys = [
      'Recipient Id' => 'contact_identifier',
      'Mailing Id' => 'mailing_identifier',
      'Campaign Id' => 'campaign_identifier',
      'Event Timestamp' => 'recipient_action_timestamp',
      'Event_Type' => 'receipient_action',
      'Email' => 'email',
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

}
