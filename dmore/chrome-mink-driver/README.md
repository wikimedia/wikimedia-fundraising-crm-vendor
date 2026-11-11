# Chrome Mink Driver

Mink driver for controlling Chrome without the overhead of Selenium.

It communicates directly with Google Chrome over HTTP and WebSockets, which allows it to work at least twice as fast as Chrome with Selenium. For Chrome 59+ it supports headless mode, eliminating the need to install a display server, and the overhead that comes with it. This driver is tested and benchmarked against a behat suite of 1800 scenarios and 19000 steps. It can successfully run it in less than 18 minutes with Chrome 60 headless. The same suite running against Chrome 58 with xvfb and Selenium takes ~60 minutes.

[![Gitlab CI pipeline](https://gitlab.com/behat-chrome/chrome-mink-driver/badges/main/pipeline.svg)](https://gitlab.com/behat-chrome/chrome-mink-driver/badges/main/pipeline.svg)
[![OpenSSF Best Practices](https://bestpractices.coreinfrastructure.org/projects/6489/badge)](https://bestpractices.coreinfrastructure.org/projects/6489)

## Installation

```bash
composer require dmore/chrome-mink-driver
```

## Requirements

* Google Chrome or Chromium running, with remote debugging enabled.

Via available images: [zenika/alpine-chrome](https://github.com/zenika/alpine-chrome), [chromedp/headless-shell](https://github.com/chromedp/docker-headless-shell), and more options out there ... refer to CI test setup for example usages.

Or, headed Chrome:

```bash
google-chrome \
  --disable-client-side-phishing-detection --disable-component-update --disable-default-apps \
  --disable-features=PrivacySandboxSettings4,PrivacySandboxAdsAPIsOverride,PasswordLeakDetection \
  --disable-popup-blocking --disable-prompt-on-repost --disable-search-engine-choice-screen --disable-sync \
  --no-default-browser-check --no-first-run --password-store=basic \
  --remote-allow-origins=* --remote-debugging-address=0.0.0.0 --remote-debugging-port=9222 --use-mock-keychain
```

Or, headless Chrome (v59+):

```bash
google-chrome --disable-gpu --headless \
  --remote-debugging-address=0.0.0.0 --remote-debugging-port=9222 --remote-allow-origins=*
```

Refer https://www.chrome-flags.com/ for a full list of flags!

- `--remote-allow-origins=*` disables Chrome's WebSocket origin check (Chrome 111+), which otherwise rejects
DevTools connections unless they originate from the same host - relevant whenever Chrome is reached from a
different host/container than it's running on. This is separate from the older HTTP `Host` header check
Chrome also applies to the `/json/*` endpoints; this driver already works around that one itself (see
`HttpClient`).

Headless Chrome doesn't render any UI, so Chrome's own first-run/onboarding dialogs (Privacy Sandbox "Ad
privacy" consent, search engine choice, weak/leaked password warnings, ...) and its popup blocker aren't an
issue there. When running with a visible window, however, these can open as extra windows or silently swallow
`window.open()` calls, which confuses window/tab handling (including switching back to the main window) since
the driver has no way to tell them apart from windows opened by the page under test. The remaining flags in
the first example above suppress them; adjust the `--disable-features` list for your Chrome version if a
particular dialog still appears.

It is recommended to start Chrome with the `--disable-extensions` flag.

See https://gitlab.com/DMore/behat-chrome-skeleton for a fully working example.

### Checking Chrome connectivity

To manually test connectivity, you can use `curl` or similar and request the `json/version` endpoint:

```
curl http://chrome:9222/json/version
```

## Contributing

Contributions are welcome! Use the [issue queue and merge requests to propose changes](https://gitlab.com/behat-chrome/chrome-mink-driver). Please refer to
[Gitlab documentation](https://docs.gitlab.com/ee/user/) for how to use the Gitlab interface.

- To report an issue (bug, feature request etc) use the [issue queue](https://gitlab.com/behat-chrome/chrome-mink-driver/-/issues).
- If you are reporting a potential security issue, please check "This issue is confidential" when reporting the issue to
  the project.
- To propose code changes or a solution for an issue, use [merge requests](https://gitlab.com/behat-chrome/chrome-mink-driver/-/merge_requests).
- Test coverage is executed on merge requests. Contributions should extend test coverage where possible and ensure all
  tests pass.
- Coding standards checks are executed on merge requests. Contributions should maintain coding standards.
- PHP code should adhere to [PSR12](https://www.php-fig.org/psr/psr-12/).
- `composer.json` should be normalized using `composer normalize`.

## Tests

The base test coverage is that from upstream `mink/driver-testsuite`, and validates that core DriverInterface
functionality is correct per those expectations.

Test execution requires a webserver configured to serve fixtures from
[minkphp/driver-testsuite](https://github.com/minkphp/driver-testsuite/), which the Docker Compose environment below
provides via its `mink-fixtures` service.

Additional tests and web fixtures are included in this project. These supplement tests from minkphp/driver-testsuite -
if a test case applies to other drivers, it's encouraged to upstream tests there. Web fixtures are copied from this
project into Mink driver-testsuite's fixtures directory before execution in Gitlab CI and `make copy-fixtures` here. See
https://gitlab.com/behat-chrome/chrome-mink-driver/-/work_items/180 for details.

Tests are executed on each merge request via Gitlab CI. New functionality or bugfixes should seek to expand
test or at least maintain existing coverage.

### Development environment with Docker Compose

The following Make commands are available:

| command               | purpose                                                      |
|-----------------------|--------------------------------------------------------------|
| `make up-zenika`      | Start the stack with Chrome using zenika/alpine-chrome       |
| `make up-headless`    | Start the stack with Chrome using chromedp/headless-shell    |
| `make shell`          | Open an interactive shell in the test runner                 |
| `make install`        | Install dependencies with `composer`                         |
| `make phpcbf`         | Tidy code using `phpcbf`                                     |
| `make phpcs`          | Check coding standards with `phpcs`                          |
| `make test`           | Run PHPUnit against whichever Chrome image is already up     |
| `make test-zenika`    | Start the stack with zenika/alpine-chrome and run PHPUnit    |
| `make test-headless`  | Start the stack with chromedp/headless-shell and run PHPUnit |
| `make test-all`       | Run `test-zenika` and `test-headless`                        |
| `make chrome-version` | Show the active Chrome version                               |

You can also skip `make` and use Docker Compose directly. Bring up the environment in your preferred state, eg:

```
# defaults (zenika/alpine-chrome)
docker compose up -d

# with PHP v8.7
PHP_VERSION=8.7 docker compose up -d

# with chrome override to chromedp/headless-shell
docker compose -f docker-compose.yml -f docker-compose.headless-shell.yml up -d
```

Once the stack is up, you can use it directly for local testing rather than through `make`. There is a web
fixtures host (`mink-fixtures`), a headless Chrome instance (`chrome`), and a `test-runner` service used for
tests and interactive work.

Open a shell to the test runner for interactive testing / iterating on a feature:

```shell
docker compose run --rm --no-deps test-runner
# Then in the shell that opens (composer is already installed in the image):
composer install
./vendor/bin/phpunit
```

To run the full suite of unit tests in one shot:

```shell
docker compose run --rm --no-deps --entrypoint /workspace/vendor/bin/phpunit test-runner
```

To test on a different PHP version, specify the PHP_VERSION environment variable when starting the stack (you
may need to reinstall dependencies):

```shell
PHP_VERSION=8.7 docker compose up -d
PHP_VERSION=8.7 docker compose run --rm --no-deps --entrypoint /workspace/vendor/bin/phpunit test-runner
```

When you're done, tear the stack down:

```shell
docker compose down
```

## Versioning & releases

- Releases are distributed through Packagist at https://packagist.org/packages/dmore/chrome-mink-driver
- This project aims to follow [Semantic Versioning](https://semver.org/).
- Releases are coordinated using (["Release" label](https://gitlab.com/behat-chrome/chrome-mink-driver/-/issues/?state=all&label_name%5B%5D=Release)) issues in Gitlab.
- New releases are created via [git tags being pushed to Gitlab](https://gitlab.com/behat-chrome/chrome-mink-driver/-/tags).

### Making a release

When it's time to release:

1. Ensure a header for the release is added to CHANGELOG.md, retaining the "Unreleased" header at top.
2. A new release should be created using [Gitlab's Release UI](https://gitlab.com/behat-chrome/chrome-mink-driver/-/releases)
  - The release title and tag is the version only.
  - The release notes are the CHANGELOG entries for the version.
3. Packagist will detect the new release and [make it available](https://packagist.org/packages/dmore/chrome-mink-driver).

## Usage

```php
use Behat\Mink\Mink;
use Behat\Mink\Session;
use DMore\ChromeDriver\ChromeDriver;

$mink = new Mink([
  'browser' => new Session(new ChromeDriver('http://localhost:9222', null, 'http://www.google.com'))
]);
```

## Configuration

| Option           | Value                    | Description                               |
|------------------|--------------------------|-------------------------------------------|
| socketTimeout    | int, default: 10         | Connection timeout (seconds)              |
| domWaitTimeout   | int, default: 3000       | DOM ready waiting timeout (milliseconds)  |
| downloadBehavior | allow, default, deny     | Chrome switch to permit downloads. (v62+) |
| downloadPath     | e.g. /tmp/ (the default) | Where to download files to, if permitted. |

Pass configuration values as the third parameter to `new ChromeDriver()`.

## Rendering PDF and Screenshots

Despite the Mink functionality the driver supports printing PDF pages or capturing a screenshot.

```php
use Behat\Mink\Mink;
use Behat\Mink\Session;
use DMore\ChromeDriver\ChromeDriver;
$mink = new Mink(array(
    'browser' => new Session(new ChromeDriver('http://localhost:9222', null, 'http://www.google.com'))
));
$mink->setDefaultSessionName('browser');
$mink->getSession()->visit('https://gitlab.com/behat-chrome/chrome-mink-driver/blob/master/README.md');
$driver = $mink->getSession()->getDriver();
$driver->printToPdf('/tmp/readme.pdf');
```

The available options are documented here: https://chromedevtools.github.io/devtools-protocol/tot/Page/#method-printToPDF

Screenshots are supported using the Mink driver interface method `getScreenshot()`.

## Related projects

### Behat extension

To use this driver with [Behat](https://docs.behat.org/en/latest/), try [the `dmore/behat-chrome-extension` Behat extension](https://gitlab.com/behat-chrome/behat-chrome-extension).

### Containers and related projects

For project tests, development and CI, there are a few containers used. PHP images with development components are built
and pushed to the project's Gitlab Container Registry. These cover the various PHP versions which are tested in CI, and
can be pulled to use for local development.

This project also uses the following containers from external projects in development and CI:

- [zenika/alpine-chrome](https://github.com/zenika/alpine-chrome)
- [chromedp/headless-shell](https://github.com/chromedp/docker-headless-shell)

The primary test suite applied is Mink's [driver-testsuite](https://github.com/minkphp/driver-testsuite/), extended with
additional tests and fixtures from this project.
