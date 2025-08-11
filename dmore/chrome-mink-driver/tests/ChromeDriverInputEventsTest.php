<?php

namespace DMore\ChromeDriverTests;

/**
 * Note that the majority of driver test coverage is provided via minkphp/driver-testsuite.
 *
 * Consider building on coverage there first!
 */
class ChromeDriverInputEventsTest extends ChromeDriverTestBase
{
    /**
     * Validate that input, keyboard and change events fire in the expected sequence when setting text values on fields
     *
     * @dataProvider textInputTypeEventsProvider
     * @throws DriverException
     */
    public function testInteractionEventsFireWhenSettingTextValues($fieldHTML, $value, $expected)
    {
        $this->visitPageWithContent(
            <<<HTML
            <html>
            <body>
            <form>
                $fieldHTML
                <input type="submit" value="Upload file" />
            </form>
            <script>
              window._test = [];
              const input = document.querySelector('[name=input1]');
              input.addEventListener('beforeinput', (e) => _test.push([`beforeinput '\${input.value}'`]))
              input.addEventListener('input', (e) => _test.push([`input '\${input.value}'`]))
              input.addEventListener('change', (e) => _test.push([`change '\${input.value}'`]))
              input.addEventListener('keydown', (e) => _test.push([`keydown '\${e.key}'`]))
              input.addEventListener('keyup', (e) => _test.push([`keyup '\${e.key}'`]))
            </script>
            </div>
            </body>
            </html>
            HTML
        );

        $this->driver->setValue('//*[./@name="input1"]', $value);
        $this->assertSame(
            $expected . "\n",
            $this->driver->evaluateScript('window._test.join("\n")') . "\n"
        );
    }

    /**
     * Validate that input, keyboard and change events fired on an input bubble to the document as expected
     *
     * @dataProvider textInputTypeEventsProvider
     * @throws DriverException
     */
    public function testInteractionEventsBubbleToDocumentWhenSettingTextValues($fieldHTML, $value, $expected)
    {
        $this->visitPageWithContent(
            <<<HTML
            <html>
            <body>
            <form>
                $fieldHTML
                <input type="submit" value="Upload file" />
            </form>
            <script>
              window._test = [];
              document.addEventListener('beforeinput', (e) => _test.push([`beforeinput '\${e.target.value}'`]))
              document.addEventListener('input', (e) => _test.push([`input '\${e.target.value}'`]))
              document.addEventListener('change', (e) => _test.push([`change '\${e.target.value}'`]))
              document.addEventListener('keydown', (e) => _test.push([`keydown '\${e.key}'`]))
              document.addEventListener('keyup', (e) => _test.push([`keyup '\${e.key}'`]))
            </script>
            </div>
            </body>
            </html>
            HTML
        );

        $this->driver->setValue('//*[./@name="input1"]', $value);
        $this->assertSame(
            $expected . "\n",
            $this->driver->evaluateScript('window._test.join("\n")') . "\n"
        );
    }

    /**
     * Verify that events are fired and bubbled as expected with <div content-editable>
     *
     * This is essentially the same test as for the <input> and <textarea> elements, but reading the current value from
     * the element is different (uses `.innerText` instead of `.value`) so split to a separate test to keep the
     * javascript simple & clean.
     *
     * @testWith ["document"]
     *           ["div"]
     *
     * @throws \Behat\Mink\Exception\DriverException
     * @throws \Behat\Mink\Exception\UnsupportedDriverActionException
     */
    public function testInteractionEventsFireAndBubbleAsExpectedWithContentEditable($listenOn)
    {
        $this->visitPageWithContent(
            <<<HTML
            <html>
            <body>
            <div contenteditable="true"></div>
            <script>
              window._test = [];
              const div = document.querySelector('div[contenteditable]');
              {$listenOn}.addEventListener('beforeinput', (e) => _test.push([`beforeinput '\${e.target.innerText}'`]))
              {$listenOn}.addEventListener('input', (e) => _test.push([`input '\${e.target.innerText}'`]))
              {$listenOn}.addEventListener('change', (e) => _test.push([`change '\${e.target.innerText}'`]))
              {$listenOn}.addEventListener('keydown', (e) => _test.push([`keydown '\${e.key}'`]))
              {$listenOn}.addEventListener('keyup', (e) => _test.push([`keyup '\${e.key}'`]))
            </script>
            </div>
            </body>
            </html>
            HTML
        );
        $this->driver->setValue('//div[./@contenteditable]', "I\nedit");

        $this->assertSame(
            <<<EVENTS
            keydown 'I'
            beforeinput ''
            input 'I'
            keyup 'I'
            keydown 'Enter'
            beforeinput 'I'
            input 'I

            '
            keyup 'Enter'
            keydown 'e'
            beforeinput 'I

            '
            input 'I
            e'
            keyup 'e'
            keydown 'd'
            beforeinput 'I
            e'
            input 'I
            ed'
            keyup 'd'
            keydown 'i'
            beforeinput 'I
            ed'
            input 'I
            edi'
            keyup 'i'
            keydown 't'
            beforeinput 'I
            edi'
            input 'I
            edit'
            keyup 't'
            EVENTS,
            $this->driver->evaluateScript('window._test.join("\n")')
        );
    }

    /**
     * Array of: input type, value to input, list of events to expect when entering that value
     *
     * @return \string[][]
     */
    public function textInputTypeEventsProvider()
    {
        /*
         * List of all input types per https://html.spec.whatwg.org/multipage/input.html#attr-input-type-keywords
         * | type           | Chrome supports simple char-by-char keyboard input     |
         * |----------------|--------------------------------------------------------|
         * | hidden         | no                                                     |
         * | text           | yes                                                    |
         * | search         | yes                                                    |
         * | tel            | yes                                                    |
         * | url            | yes                                                    |
         * | email          | yes                                                    |
         * | password       | yes                                                    |
         * | date           | locale-specific, better set as an explicit value       |
         * | month          | not as a single string (needs tab to change component) |
         * | week           | locale-specific & not as a single string               |
         * | time           | locale-specific & not as a single string               |
         * | datetime-local | locale-specific & not as a single string               |
         * | number         | yes, but only of valid values                          |
         * | range          | no                                                     |
         * | color          | no                                                     |
         * | checkbox       | no                                                     |
         * | radio          | no                                                     |
         * | file           | no                                                     |
         * | submit         | no                                                     |
         * | made_up_type   | yes - treated as any type of `text`                    |
         * | image          | no                                                     |
         * | reset          | no                                                     |
         * | button         | no                                                     |
         */

        // These event streams can be verified in a real browser by opening a page with the test content and typing
        // into a field. Note that the `keydown 'Shift'` events that fire in real browsers are intentionally not
        // included in the list of expected events. Replicating these is keyboard/locale-specific and is not required
        // to simply set a value as the `key` / `text` properties on KeyboardEvent already carry the result of the key
        // combined with any modifier keys that were pressed at the time.
        //
        // Applications that need to send / test specific combinations of modifier keys should send keystrokes directly
        // rather than attempting to use the `setValue` helper.
        return [
            'text'             => [
                '<input type="text" name="input1">',
                'changed Value',
                <<<EVENTS
                keydown 'c'
                beforeinput ''
                input 'c'
                keyup 'c'
                keydown 'h'
                beforeinput 'c'
                input 'ch'
                keyup 'h'
                keydown 'a'
                beforeinput 'ch'
                input 'cha'
                keyup 'a'
                keydown 'n'
                beforeinput 'cha'
                input 'chan'
                keyup 'n'
                keydown 'g'
                beforeinput 'chan'
                input 'chang'
                keyup 'g'
                keydown 'e'
                beforeinput 'chang'
                input 'change'
                keyup 'e'
                keydown 'd'
                beforeinput 'change'
                input 'changed'
                keyup 'd'
                keydown ' '
                beforeinput 'changed'
                input 'changed '
                keyup ' '
                keydown 'V'
                beforeinput 'changed '
                input 'changed V'
                keyup 'V'
                keydown 'a'
                beforeinput 'changed V'
                input 'changed Va'
                keyup 'a'
                keydown 'l'
                beforeinput 'changed Va'
                input 'changed Val'
                keyup 'l'
                keydown 'u'
                beforeinput 'changed Val'
                input 'changed Valu'
                keyup 'u'
                keydown 'e'
                beforeinput 'changed Valu'
                input 'changed Value'
                keyup 'e'
                change 'changed Value'
                EVENTS,
            ],
            'search'           => [
                '<input type="search" name="input1">',
                'search me!',
                <<<EVENTS
                keydown 's'
                beforeinput ''
                input 's'
                keyup 's'
                keydown 'e'
                beforeinput 's'
                input 'se'
                keyup 'e'
                keydown 'a'
                beforeinput 'se'
                input 'sea'
                keyup 'a'
                keydown 'r'
                beforeinput 'sea'
                input 'sear'
                keyup 'r'
                keydown 'c'
                beforeinput 'sear'
                input 'searc'
                keyup 'c'
                keydown 'h'
                beforeinput 'searc'
                input 'search'
                keyup 'h'
                keydown ' '
                beforeinput 'search'
                input 'search '
                keyup ' '
                keydown 'm'
                beforeinput 'search '
                input 'search m'
                keyup 'm'
                keydown 'e'
                beforeinput 'search m'
                input 'search me'
                keyup 'e'
                keydown '!'
                beforeinput 'search me'
                input 'search me!'
                keyup '!'
                change 'search me!'
                EVENTS,
            ],
            'tel'              => [
                '<input type="tel" name="input1">',
                '+44 (712) 55-11 ex 15',
                <<<EVENTS
                keydown '+'
                beforeinput ''
                input '+'
                keyup '+'
                keydown '4'
                beforeinput '+'
                input '+4'
                keyup '4'
                keydown '4'
                beforeinput '+4'
                input '+44'
                keyup '4'
                keydown ' '
                beforeinput '+44'
                input '+44 '
                keyup ' '
                keydown '('
                beforeinput '+44 '
                input '+44 ('
                keyup '('
                keydown '7'
                beforeinput '+44 ('
                input '+44 (7'
                keyup '7'
                keydown '1'
                beforeinput '+44 (7'
                input '+44 (71'
                keyup '1'
                keydown '2'
                beforeinput '+44 (71'
                input '+44 (712'
                keyup '2'
                keydown ')'
                beforeinput '+44 (712'
                input '+44 (712)'
                keyup ')'
                keydown ' '
                beforeinput '+44 (712)'
                input '+44 (712) '
                keyup ' '
                keydown '5'
                beforeinput '+44 (712) '
                input '+44 (712) 5'
                keyup '5'
                keydown '5'
                beforeinput '+44 (712) 5'
                input '+44 (712) 55'
                keyup '5'
                keydown '-'
                beforeinput '+44 (712) 55'
                input '+44 (712) 55-'
                keyup '-'
                keydown '1'
                beforeinput '+44 (712) 55-'
                input '+44 (712) 55-1'
                keyup '1'
                keydown '1'
                beforeinput '+44 (712) 55-1'
                input '+44 (712) 55-11'
                keyup '1'
                keydown ' '
                beforeinput '+44 (712) 55-11'
                input '+44 (712) 55-11 '
                keyup ' '
                keydown 'e'
                beforeinput '+44 (712) 55-11 '
                input '+44 (712) 55-11 e'
                keyup 'e'
                keydown 'x'
                beforeinput '+44 (712) 55-11 e'
                input '+44 (712) 55-11 ex'
                keyup 'x'
                keydown ' '
                beforeinput '+44 (712) 55-11 ex'
                input '+44 (712) 55-11 ex '
                keyup ' '
                keydown '1'
                beforeinput '+44 (712) 55-11 ex '
                input '+44 (712) 55-11 ex 1'
                keyup '1'
                keydown '5'
                beforeinput '+44 (712) 55-11 ex 1'
                input '+44 (712) 55-11 ex 15'
                keyup '5'
                change '+44 (712) 55-11 ex 15'
                EVENTS,
            ],
            'url'              => [
                '<input type="url" name="input1">',
                'http://foo.test?a=1',
                <<<EVENTS
                keydown 'h'
                beforeinput ''
                input 'h'
                keyup 'h'
                keydown 't'
                beforeinput 'h'
                input 'ht'
                keyup 't'
                keydown 't'
                beforeinput 'ht'
                input 'htt'
                keyup 't'
                keydown 'p'
                beforeinput 'htt'
                input 'http'
                keyup 'p'
                keydown ':'
                beforeinput 'http'
                input 'http:'
                keyup ':'
                keydown '/'
                beforeinput 'http:'
                input 'http:/'
                keyup '/'
                keydown '/'
                beforeinput 'http:/'
                input 'http://'
                keyup '/'
                keydown 'f'
                beforeinput 'http://'
                input 'http://f'
                keyup 'f'
                keydown 'o'
                beforeinput 'http://f'
                input 'http://fo'
                keyup 'o'
                keydown 'o'
                beforeinput 'http://fo'
                input 'http://foo'
                keyup 'o'
                keydown '.'
                beforeinput 'http://foo'
                input 'http://foo.'
                keyup '.'
                keydown 't'
                beforeinput 'http://foo.'
                input 'http://foo.t'
                keyup 't'
                keydown 'e'
                beforeinput 'http://foo.t'
                input 'http://foo.te'
                keyup 'e'
                keydown 's'
                beforeinput 'http://foo.te'
                input 'http://foo.tes'
                keyup 's'
                keydown 't'
                beforeinput 'http://foo.tes'
                input 'http://foo.test'
                keyup 't'
                keydown '?'
                beforeinput 'http://foo.test'
                input 'http://foo.test?'
                keyup '?'
                keydown 'a'
                beforeinput 'http://foo.test?'
                input 'http://foo.test?a'
                keyup 'a'
                keydown '='
                beforeinput 'http://foo.test?a'
                input 'http://foo.test?a='
                keyup '='
                keydown '1'
                beforeinput 'http://foo.test?a='
                input 'http://foo.test?a=1'
                keyup '1'
                change 'http://foo.test?a=1'
                EVENTS,
            ],
            'email'            => [
                '<input type="email" name="input1">',
                'foo@bar.test',
                <<<EVENTS
                keydown 'f'
                beforeinput ''
                input 'f'
                keyup 'f'
                keydown 'o'
                beforeinput 'f'
                input 'fo'
                keyup 'o'
                keydown 'o'
                beforeinput 'fo'
                input 'foo'
                keyup 'o'
                keydown '@'
                beforeinput 'foo'
                input 'foo@'
                keyup '@'
                keydown 'b'
                beforeinput 'foo@'
                input 'foo@b'
                keyup 'b'
                keydown 'a'
                beforeinput 'foo@b'
                input 'foo@ba'
                keyup 'a'
                keydown 'r'
                beforeinput 'foo@ba'
                input 'foo@bar'
                keyup 'r'
                keydown '.'
                beforeinput 'foo@bar'
                input 'foo@bar.'
                keyup '.'
                keydown 't'
                beforeinput 'foo@bar.'
                input 'foo@bar.t'
                keyup 't'
                keydown 'e'
                beforeinput 'foo@bar.t'
                input 'foo@bar.te'
                keyup 'e'
                keydown 's'
                beforeinput 'foo@bar.te'
                input 'foo@bar.tes'
                keyup 's'
                keydown 't'
                beforeinput 'foo@bar.tes'
                input 'foo@bar.test'
                keyup 't'
                change 'foo@bar.test'
                EVENTS,
            ],
            'password'         => [
                '<input type="password" name="input1">',
                'se$@Me',
                <<<EVENTS
                keydown 's'
                beforeinput ''
                input 's'
                keyup 's'
                keydown 'e'
                beforeinput 's'
                input 'se'
                keyup 'e'
                keydown '$'
                beforeinput 'se'
                input 'se$'
                keyup '$'
                keydown '@'
                beforeinput 'se$'
                input 'se$@'
                keyup '@'
                keydown 'M'
                beforeinput 'se$@'
                input 'se$@M'
                keyup 'M'
                keydown 'e'
                beforeinput 'se$@M'
                input 'se$@Me'
                keyup 'e'
                change 'se$@Me'
                EVENTS,
            ],
            'number - valid'   => [
                '<input type="number" name="input1">',
                '15.23',
                <<<EVENTS
                keydown '1'
                beforeinput ''
                input '1'
                keyup '1'
                keydown '5'
                beforeinput '1'
                input '15'
                keyup '5'
                keydown '.'
                beforeinput '15'
                input '15'
                keyup '.'
                keydown '2'
                beforeinput '15'
                input '15.2'
                keyup '2'
                keydown '3'
                beforeinput '15.2'
                input '15.23'
                keyup '3'
                change '15.23'
                EVENTS,
            ],
            'number - invalid' => [
                '<input type="number" name="input1">',
                'bad',
                <<<EVENTS
                keydown 'b'
                beforeinput ''
                keyup 'b'
                keydown 'a'
                beforeinput ''
                keyup 'a'
                keydown 'd'
                beforeinput ''
                keyup 'd'
                EVENTS,
            ],
            'made up type'     => [
                '<input type="made_up_type" name="input1">',
                'I am anything',
                <<<EVENTS
                keydown 'I'
                beforeinput ''
                input 'I'
                keyup 'I'
                keydown ' '
                beforeinput 'I'
                input 'I '
                keyup ' '
                keydown 'a'
                beforeinput 'I '
                input 'I a'
                keyup 'a'
                keydown 'm'
                beforeinput 'I a'
                input 'I am'
                keyup 'm'
                keydown ' '
                beforeinput 'I am'
                input 'I am '
                keyup ' '
                keydown 'a'
                beforeinput 'I am '
                input 'I am a'
                keyup 'a'
                keydown 'n'
                beforeinput 'I am a'
                input 'I am an'
                keyup 'n'
                keydown 'y'
                beforeinput 'I am an'
                input 'I am any'
                keyup 'y'
                keydown 't'
                beforeinput 'I am any'
                input 'I am anyt'
                keyup 't'
                keydown 'h'
                beforeinput 'I am anyt'
                input 'I am anyth'
                keyup 'h'
                keydown 'i'
                beforeinput 'I am anyth'
                input 'I am anythi'
                keyup 'i'
                keydown 'n'
                beforeinput 'I am anythi'
                input 'I am anythin'
                keyup 'n'
                keydown 'g'
                beforeinput 'I am anythin'
                input 'I am anything'
                keyup 'g'
                change 'I am anything'
                EVENTS,
            ],
            'textarea'         => [
                '<textarea name="input1"></textarea>',
                "Some\ntext",
                <<<EVENTS
                keydown 'S'
                beforeinput ''
                input 'S'
                keyup 'S'
                keydown 'o'
                beforeinput 'S'
                input 'So'
                keyup 'o'
                keydown 'm'
                beforeinput 'So'
                input 'Som'
                keyup 'm'
                keydown 'e'
                beforeinput 'Som'
                input 'Some'
                keyup 'e'
                keydown 'Enter'
                beforeinput 'Some'
                input 'Some
                '
                keyup 'Enter'
                keydown 't'
                beforeinput 'Some
                '
                input 'Some
                t'
                keyup 't'
                keydown 'e'
                beforeinput 'Some
                t'
                input 'Some
                te'
                keyup 'e'
                keydown 'x'
                beforeinput 'Some
                te'
                input 'Some
                tex'
                keyup 'x'
                keydown 't'
                beforeinput 'Some
                tex'
                input 'Some
                text'
                keyup 't'
                change 'Some
                text'
                EVENTS
                ,
            ],
        ];
    }
}
