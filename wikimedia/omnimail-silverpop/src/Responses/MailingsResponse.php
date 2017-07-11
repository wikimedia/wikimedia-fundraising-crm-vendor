<?php
/**
 * Created by IntelliJ IDEA.
 * User: emcnaughton
 * Date: 5/4/17
 * Time: 7:34 AM
 */
namespace Omnimail\Silverpop\Responses;

class MailingsResponse extends BaseResponse
{

  public function __construct($input = [], $flags = 0, $iterator_class = "ArrayIterator") {
    $mailers = array();
    foreach ($input as $xml) {
      $mailers[] = new Mailing($xml);
    }
    parent::__construct($mailers, $flags, $iterator_class);
  }

}
