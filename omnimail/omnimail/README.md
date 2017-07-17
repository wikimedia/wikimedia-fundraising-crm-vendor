<p align="center"><img width="320"src="https://cdn.rawgit.com/gabrielbull/omnimail/master/omnimail-logo.svg"></p>

<p align="center">
  <a href="https://travis-ci.org/gabrielbull/omnimail"><img src="https://img.shields.io/travis/gabrielbull/omnimail.svg?style=flat-square" alt="Build Status"></a>
  <a href="https://styleci.io/repos/12901491"><img src="https://styleci.io/repos/12901491/shield" alt="StyleCI"></a>
  <a href="https://scrutinizer-ci.com/g/gabrielbull/omnimail/?branch=master"><img src="https://img.shields.io/scrutinizer/g/gabrielbull/omnimail.svg?style=flat-square" alt="Scrutinizer Code Quality"></a>
  <a href="https://scrutinizer-ci.com/g/gabrielbull/omnimail/?branch=master"><img src="https://img.shields.io/scrutinizer/coverage/g/gabrielbull/omnimail.svg?style=flat-square" alt="Code Coverage"></a>
  <a href="https://codeclimate.com/github/gabrielbull/omnimail"><img src="https://img.shields.io/codeclimate/github/gabrielbull/omnimail.svg?style=flat-square" alt="Code Climate"></a>
  <a href="https://packagist.org/packages/omnimail/omnimail"><img src="https://img.shields.io/packagist/v/omnimail/omnimail.svg?style=flat-square" alt="Latest Stable Version"></a>
</p>

## Send email across all platforms using one interface.

### Table Of Content

1. [Requirements](#requirements)
2. [Installation](#installation)
3. [Providers](#providers)
   - [AmazonSES](#amazon-ses)
   - [Mailgun](#mailgun)
   - [Mailjet](#mailjet)
   - [Mandrill](#mandrill)
   - [Postmark](#postmark)
   - [Sendgrid](#sendgrid)
   - [SendinBlue](#sendinblue)
4. [Email](#email)
   - [To](#email-to)
   - [From](#email-from)
   - [CC](#email-cc)
   - [BCC](#email-bcc)
   - [Reply to](#email-reply-to)
   - [Subject](#email-subject)
   - [Text Body](#email-text-body)
   - [HTML Body](#email-html-body)
   - [Attachments](#email-attachments)
5. [Factory](#factory)
6. [Exceptions](#exceptions)
7. [Logging](#logging)
8. [License](#license-section)

<a name="requirements"></a>
## Requirements

This library uses PHP 5.5+.

<a name="installation"></a>
## Installation

It is recommended that you install the Omnimail library [through composer](http://getcomposer.org/). To do so,
run the Composer command to install the latest stable version of Omnimail library.

```shell
composer require omnimail/omnimail
```

<a name="providers"></a>
## Providers

<a name="amazon-ses"></a>
### AmazonSES

#### Installation

To use the AmazonSES mailer class, you will need to install the `daniel-zahariev/php-aws-ses` library using composer.

```
composer require daniel-zahariev/php-aws-ses
```

#### Usage

```php
use Omnimail\Email;
use Omnimail\AmazonSES;

$mailer = new AmazonSES($accessKey, $secretKey, $region, $verifyPeer, $verifyHost);

$email = (new Email())
    ->addTo('example@email.com')
    ->setFrom('example@email.com')
    ->setSubject('Hello, world!')
    ->setTextBody('Hello World! How are you?');

$mailer->send($email);
```

<a name="mailgun"></a>
### Mailgun

#### Installation

To use the Mailgun mailer class, you will need to install the `mailgun/mailgun-php` library using composer. You do also 
need to install a HTTP client that sends messages. You can use any client that provided the virtual package
[php-http/client-implementation](https://packagist.org/providers/php-http/client-implementation)

```
composer require mailgun/mailgun-php php-http/guzzle6-adapter
```

#### Usage

```php
use Omnimail\Email;
use Omnimail\Mailgun;

$mailer = new Mailgun($apiKey, $domain);

$email = (new Email())
    ->addTo('example@email.com')
    ->setFrom('example@email.com')
    ->setSubject('Hello, world!')
    ->setTextBody('Hello World! How are you?');

$mailer->send($email);
```

<a name="mailjet"></a>
### Mailjet

#### Installation

To use the Mailjet mailer class, you will need to install the `mailjet/mailjet-apiv3-php` library using composer.

```
composer require mailjet/mailjet-apiv3-php
```

#### Usage

```php
use Omnimail\Email;
use Omnimail\Mailjet;

$mailer = new Mailjet($apikey, $apisecret);

$email = (new Email())
    ->addTo('example@email.com')
    ->setFrom('example@email.com')
    ->setSubject('Hello, world!')
    ->setTextBody('Hello World! How are you?');

$mailer->send($email);
```

<a name="mandrill"></a>
### Mandrill

#### Installation

To use the Mandrill mailer class, you will need to install the `mandrill/mandrill` library using composer.

```
composer require mandrill/mandrill
```

#### Usage

```php
use Omnimail\Email;
use Omnimail\Mandrill;

$mailer = new Mandrill($apiKey);

$email = (new Email())
    ->addTo('example@email.com')
    ->setFrom('example@email.com')
    ->setSubject('Hello, world!')
    ->setTextBody('Hello World! How are you?');

$mailer->send($email);
```

<a name="postmark"></a>
### Postmark

#### Installation

To use the Postmark mailer class, you will need to install the `wildbit/postmark-php` library using composer.

```
composer require wildbit/postmark-php
```

#### Usage

```php
use Omnimail\Email;
use Omnimail\Postmark;

$mailer = new Postmark($serverApiToken);

$email = (new Email())
    ->addTo('example@email.com')
    ->setFrom('example@email.com')
    ->setSubject('Hello, world!')
    ->setTextBody('Hello World! How are you?');

$mailer->send($email);
```

<a name="sendgrid"></a>
### Sendgrid

#### Installation

To use the Sendgrid mailer class, you will need to install the `sendgrid/sendgrid` library using composer.

```
composer require sendgrid/sendgrid
```

#### Usage

```php
use Omnimail\Email;
use Omnimail\Sendgrid;

$mailer = new Sendgrid($apiKey);

$email = (new Email())
    ->addTo('example@email.com')
    ->setFrom('example@email.com')
    ->setSubject('Hello, world!')
    ->setTextBody('Hello World! How are you?');

$mailer->send($email);
```

<a name="sendinblue"></a>
### SendinBlue

#### Installation

To use the SendinBlue mailer class, you will need to install the `mailin-api/mailin-api-php` library using composer.

```
composer require mailin-api/mailin-api-php
```

#### Usage

```php
use Omnimail\Email;
use Omnimail\SendinBlue;

$mailer = new SendinBlue($accessKey);

$email = (new Email())
    ->addTo('example@email.com')
    ->setFrom('example@email.com')
    ->setSubject('Hello, world!')
    ->setTextBody('Hello World! How are you?');

$mailer->send($email);
```

<a name="email"></a>
## Email

An `Email` object implements the `EmailInterface`  inteface. You can create your own `Email` class and send it to any
mailer if it implements the `EmailInterface` inteface.

<a name="email-to"></a>
### To

The `To` property of the email is for defining the recipients of the email. You can set multiple recipients.

```php
$email = new Email();
$email->addTo('recipent1@email.com', 'Recipient1 Name');
$email->addTo('recipent2@email.com', 'Recipient2 Name');
```

<a name="email-from"></a>
### From

The `From` property of the email is for defining the mailer of the email.

```php
$email = new Email();
$email->setFrom('sender@email.com', 'Sender Name');
```

<a name="email-cc"></a>
### CC

Like the `To` property, the `CC` property can have multiple recipients.

```php
$email = new Email();
$email->addCc('recipent1@email.com', 'Recipient1 Name');
$email->addCc('recipent2@email.com', 'Recipient2 Name');
```

<a name="email-bcc"></a>
### BCC

Like the `To` property, the `BCC` property can have multiple recipients.

```php
$email = new Email();
$email->addBcc('recipent1@email.com', 'Recipient1 Name');
$email->addBcc('recipent2@email.com', 'Recipient2 Name');
```

<a name="email-reply-to"></a>
### Reply To

The `Reply To` property of the email is for defining the email that should receive responses.

```php
$email = new Email();
$email->setReplyTo('sender@email.com', 'Sender Name');
```

<a name="email-subject"></a>
### Subject

The `Subject` property of the email is for defining the subject of the email.

```php
$email = new Email();
$email->setSubject('Hello, World!');
```

<a name="email-text-body"></a>
### Text Body

The `Text Body` property of the email is for defining the text body of the email.

```php
$email = new Email();
$email->setTextBody('This is plain text.');
```
<a name="email-html-body"></a>
### HTML Body

The `HTML Body` property of the email is for defining the HTML body of the email.

```php
$email = new Email();
$email->setHtmlBody('<h1>Hi!</h1><p>This is HTML!</p>');
```

<a name="email-attachments"></a>
### Attachments

The `Attachments` property of the email is for joining attachments to the email.

#### Example using string as content

```php
use Omnimail\Email;
use Omnimail\Attachment;

$attachment = new Attachment();
$attachment->setName('my_file.txt');
$attachment->setMimeType('text/plain');
$attachment->setContent('This is plain text');

$email = new Email();
$email->addAttachment($attachment);
```

#### Example using file path as content

```php
use Omnimail\Email;
use Omnimail\Attachment;

$attachment = new Attachment();
$attachment->setMimeType('text/plain');
$attachment->setPath(__DIR__ . '/my_file.txt');

$email = new Email();
$email->addAttachment($attachment);
```

### Inline attachments

```php
use Omnimail\Email;
use Omnimail\Attachment;

$attachment = new Attachment();
$attachment->setPath(__DIR__ . '/image.png');
$attachment->setContentId('image.png');

$email = new Email();
$email->setHtmlBody('<p>Hello!</p><img src="cid:image.png">');
$email->addAttachment($attachment);
```

<a name="factory"></a>
## Factory

Alternatively, you can use the factory method to create a mailer. Consider the following example to create a AmazonSES 
mailer:

```php
use Omnimail\Omnimail;

$mailer = Omnimail::create(Omnimail::AMAZONSES, ['accessKey' => $accessKey, 'secretKey' => $secretKey]);
```

<a name="exceptions"></a>
## Exceptions

Failures to send emails will throw exceptions. 

__Exceptions__

 * Omnimail\Exception\Exception
 * Omnimail\Exception\EmailDeliveryException
 * Omnimail\Exception\InvalidRequestException
 * Omnimail\Exception\UnauthorizedException
 * Omnimail\Exception\MailerNotFoundException

To catch all exception, consider the following.

```php
try {
    $mailer->send($email);
} catch (\Omnimail\Exception\Exception $e) {
    echo 'Something went wrong: ' . $e->getMessage();
}
```

To catch specific exceptions, consider the following. 

```php
try {
    $mailer->send($email);
} catch (\Omnimail\Exception\UnauthorizedException $e) {
    echo 'Your credentials must be incorrect';
} catch (\Omnimail\Exception\InvalidRequestException $e) {
    echo 'The request is badly formatted, check that all required fields are filled.';
} catch (\Omnimail\Exception\EmailDeliveryException $e) {
    echo 'The email did not go out.';
}
```

<a name="logging"></a>
## Logging

All mailers constructors take a PSR-3 compatible logger.

Email sent (including the email) are logged at INFO level. Errors (including the email) are reported at the ERROR level.

### Example using Monolog

```php
use Monolog\Logger;
use Monolog\Handler\StreamHandler;
use Omnimail\Mailgun;

$logger = new Logger('name');
$logger->pushHandler(new StreamHandler('path/to/your.log', Logger::INFO));

$mailer = new Mailgun($apiKey, $domain, $logger);
$mailer->send($email);
```

<a name="license-section"></a>
## License

Omnimail is licensed under [The MIT License (MIT)](LICENSE).
