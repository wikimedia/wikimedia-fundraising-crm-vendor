## 1.7.2

* Fix: prevent deletion of files outside the temporary directory and improve test coverage

## 1.7.1

> [!IMPORTANT]
> This version introduces a breaking change.
> The `$binaryPath` is checked with the `is_executable` PHP function. Thus, it is required to use an absolute or relative path without any argument (use the `$options` array or the `setOption` method instead).
> See the issue [#550](https://github.com/KnpLabs/snappy/pull/550)

* Fix: ensure binary is executable in `buildCommand`

## 1.7.0

* Fix: enhance `Pdf` constructor to allow specific schemes (see [#544](https://github.com/KnpLabs/snappy/pull/544))
* Chore: update PHPStan to v2 (see [#541](https://github.com/KnpLabs/snappy/pull/541))
* Doc: add warning based on wkhtmltopdf recommendations (see [#528](https://github.com/KnpLabs/snappy/pull/528))

## 1.6.0

* Feat: allow Symfony 8
* Test: migrate to PHPUnit 9 (see [#532](https://github.com/KnpLabs/snappy/pull/532))
* CI: add tests for PHP 8.5
* CI: upgrade GitHub Actions runner to Ubuntu 22.04

## 1.5.1

* Fix: PHP 8.4 deprecations

## 1.5.0

* Feat: support Symfony 7

## 1.4.4

* Fix: allowed output file protocols on Windows

## 1.4.3

* Fix: security advisory [GHSA-92rv-4j2h-8mjj](https://github.com/KnpLabs/snappy/security/advisories/GHSA-92rv-4j2h-8mjj)
* CI: add tests for PHP 8.3, bump dev tests to Symfony 6.4

## 1.4.2

* Fix: security issue [GHSA-gq6w-q6wh-jggc](https://github.com/KnpLabs/snappy/security/advisories/GHSA-gq6w-q6wh-jggc)
* Add `SECURITY.md`

## 1.4.1

* Fix: logger aware interface issue

## 1.4.0

* Feat: allow Symfony 6

## 1.3.1

* Fix: function signatures incompatibilities with previous version
* Build: migrate to GitHub Actions

## 1.3.0

* Allow newer versions of the `psr/log` package
* Add PHPStan level 8 CI check
* Add CS fixer CI check
* Add contributing guidelines and code of conduct

## 1.2.1

* Add `resetOption` method
* Explicitly implement `LoggerAwareInterface`

## 1.2.0

* Symfony 5 compatibility for the Process component
* Fix PDF options order
* Add missing documentation for `bypass-proxy-for` option
* Drop support for unmaintained Symfony versions (only supported versions)
* Fix documentation for displaying PDF in the browser

## 1.1.0

* Add bypass-proxy-for option added in 0.12.3 (see [#302](https://github.com/KnpLabs/snappy/pull/302))
* Fix symfony/process 4.2 deprecation notice (see [#331](https://github.com/KnpLabs/snappy/pull/331))
* Drop suppor for unmaintained PHP versions (5.6 and 7.0, see [#337](https://github.com/KnpLabs/snappy/pull/337)
* Drop support for unmaintained symfony/process versions (see [#337](https://github.com/KnpLabs/snappy/pull/337))
* Pass on error code in checkProcessStatus (see [#328](https://github.com/KnpLabs/snappy/pull/328))

Thanks to @joshpme, @drigani, @fbourigault, @NiR- and @leimd for their work.

## 1.0.4

* Support cache-dir for Image generation  (see [#297](https://github.com/KnpLabs/snappy/pull/297)).

Thank you @dimitrilahaye for their work.

## 1.0.3

* Add support to Symfony 4 ([#290](https://github.com/KnpLabs/snappy/pull/290))
* Use PHPUnit\Framework\TestCase instead of PHPUnit_Framework_TestCase ([#287](https://github.com/KnpLabs/snappy/pull/287))

Credits go to @michaelperrin and @carusogabriel.

## 1.0.2

*A BC break was introduced in v1.0.0: using objects castable to string with a cyclic dependency to the generator 
as option value would break `setOption()` / `setOptions()` methods.* 

* Use logger context rather than `var_export` to log option values (see [#283](https://github.com/KnpLabs/snappy/pull/283))

Credits go to: @barryvdh.

## 1.0.1

* Fix `Call to a member function debug() on null` logger (see [#270](https://github.com/KnpLabs/snappy/pull/270))

## 1.0.0

* Don't check if it's a file when the path is bigger than `PHP_MAXPATHLEN` (see [#224](https://github.com/KnpLabs/snappy/pull/224))
* Pass `image-dpi` and `image-quality` options as integer (see [#251](https://github.com/KnpLabs/snappy/pull/251))
* Improve documentation readability (see [#255](https://github.com/KnpLabs/snappy/pull/255))
* Add logging capabilities to generators (see [#264](https://github.com/KnpLabs/snappy/pull/264))
* Add some more frequent questions/issues to the FAQ (see [#263](https://github.com/KnpLabs/snappy/pull/263), [#265](https://github.com/KnpLabs/snappy/pull/265), [#266](https://github.com/KnpLabs/snappy/pull/266))

Credits go to: @wouterbulten, @martinssipenko, @Herz3h, @akovalyov, @NiR-.
