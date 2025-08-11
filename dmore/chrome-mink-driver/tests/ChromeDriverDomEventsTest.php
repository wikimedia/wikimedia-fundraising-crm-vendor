<?php

namespace DMore\ChromeDriverTests;

use function base64_encode;

class ChromeDriverDomEventsTest extends ChromeDriverTestBase
{
    /**
     * Check that the timestamp on a click event is a valid / realistic value
     *
     * Vuejs and potentially other javascript frameworks use the event.timeStamp to detect (and ignore) events that
     * were fired before the event listener was attached. For example, if an event causes a new parent element to be
     * rendered, the event may then bubble to that parent even though the parent was not present in the document when
     * the event was first dispatched.
     *
     * Therefore it's important that event timestamps mirror Chrome's native behaviour and carry the correct
     * high-performance timestamp for the interaction rather than a simulated value.
     *
     * @return void
     */
    public function testClickEventTimestamps()
    {
        $html = <<<'HTML'
        <html>
        <body>
            <a href="#">Click me</a>
            <script>
            (function () {
              window._test = {
                listenerAttached: performance.now(),
                clickedAt: null
              }

              document.querySelector('a').addEventListener('click', function (e) {
                  e.preventDefault();
                  window._test.clickedAt = e.timeStamp;
                });
            })();
            </script>
        </body>
        </html>
        HTML;
        $html = base64_encode($html);
        $url  = "data:text/html;charset=utf-8;base64,{$html}";
        $this->driver->visit($url);

        $this->driver->click('//a');
        $result = $this->driver->evaluateScript('window._test');
        $this->assertGreaterThan(
            $result['listenerAttached'],
            $result['clickedAt'],
            'Click event timestamp should be after event listener was attached'
        );
    }
}
