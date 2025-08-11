<?php

/**
 * Test that Stream::probe() produces a properly functioning SSL configuration
 */
class CA_Config_StreamTest extends CA_Config_TestBase {
    public function probe($params = array()) {
      return CA_Config_Stream::probe($params);
    }

    public function get($url, $caConfig) {
        $context = stream_context_create(array(
            'ssl' => $caConfig->toStreamOptions(),
            'http' => [
                'header' => "User-Agent: Mozilla/5.0 (iPad; U; CPU OS 3_2 like Mac OS X; en-us) AppleWebKit/531.21.10 (KHTML, like Gecko) Version/4.0.4 Mobile/7B334b Safari/531.21.102011-10-16 20:23:10\r\n",
            ],
        ));
        try {
            return file_get_contents($url, 0, $context);
        } catch (Exception $e) {
            return NULL;
        }
    }
}