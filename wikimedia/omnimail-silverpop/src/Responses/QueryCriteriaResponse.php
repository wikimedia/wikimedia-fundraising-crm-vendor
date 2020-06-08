<?php
/**
 * Created by IntelliJ IDEA.
 * User: emcnaughton
 * Date: 5/4/17
 * Time: 7:34 AM
 */
namespace Omnimail\Silverpop\Responses;

class QueryCriteriaResponse extends BaseResponse
{

    public function __construct($data)
    {
        $this->data = $data;
    }

    public function getQueryName() {
        return (string) $this->data->QUERY_NAME;
    }

    public function getQueryCriteria() {
        $string = (string) $this->data->QUERY_CRITERIA;
        $xml = simplexml_load_string($string);
        $parsedString = '';
        $parsedArray = [];
        foreach ($xml->children() as $x) {
            $parsedArray[] = !empty($parsedArray) ? $x->CONJUNCTION : NULL;
            $parens = (string) ($x->PARENS ?? NULL);
            $parsedArray[] = $parens === '(' ? '(' : NULL;
            $parsedArray[] = $x->COLUMN ?? NULL;
            $parsedArray[] = $x->OPERATOR ?? NULL;
            $parsedArray[] = $x->ID ?? NULL;
            $parsedArray[] = $x->VALUE ?? NULL;
            $parsedArray[] = $parens === ')' ? ')' : NULL;
        }
        return implode(' ', array_filter($parsedArray));
    }

}
