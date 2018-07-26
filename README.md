# fatturapa-testsdi

This repository contains a test Exchange System (ES, Italian: **SDI**) for Electronic Invoices.

The **testsdi** can be used during development of SDICoop compliant services to simulate and test locally the interaction with the actual ES system run by Agenzia delle Entrate.

## Introduction

The ES system run by the Italian government (SDICoop Service - Transmit / Receive) is used to transmit and receive invoices, receipts and notifications.

There are three actors:

- **issuer** (Italian: trasmittente), implements 1 SOAP Web Service (WS):

    - SDICoop Transmit / **TrasmissioneFatture**: to receive receipts and notifications from the ES

  and uses the ES SdIRiceviFile endpoint to issue invoices

- **ES** (forwards invoice from issuer to recipient Italian: SDI), implements 2 SOAP WS:

    - SDICoop Transmit / **SdIRiceviFile**, gets invoices from the issuer
    - SDICoop Receive / **SdIRiceviNotifica**: gets receipts from the recipient and forwards it to the issuer

  and uses:

    - the recipent RicezioneFatture endpoint to send invoices
    - and the issuer TrasmissioneFatture endpoint to send receipts notifications

- and **recipient** (Italian: destinatario), implements 1 SOAP WS:

    - SDICoop Receive / RicezioneFatture: to get invoices and notifications from the SDI

  and uses the ES SdIRiceviNotifica WS to send notifications

This can also bee see grouping separately the two services:

**SDICoop Transmit**

![Transmit](/images/trasmissione.png)

**SDICoop Receive**

![Receive](/images/ricezione.png)

There is some [English documentation](http://fatturapa.gov.it/export/fatturazione/en/normativa/f-3.htm?l=en) but it's outdated. The [Italian documentation](http://fatturapa.gov.it/export/fatturazione/it/normativa/f-3.htm?l=it) is more up-to-date.

## How it works

For each of the four SOAP Web Services, we start from the [Web Services Description Language, (**WSDL**)](https://en.wikipedia.org/wiki/Wsdl) and [XML Schema Definition, (**XSD**)](https://en.wikipedia.org/wiki/XML_Schema_(W3C) files from fatturapa.gov.it, feed them to [wsdl2phpgenerator](https://github.com/wsdl2phpgenerator/wsdl2phpgenerator) which generates types and boilerplate for the endpoint in a directory **named as the endpoint**.

This code generation step is performed by the [index.php](/index.php) script.

In each of the four resulting directory matching the endpoints, we place a `index.php` file similar to (this one is for the `SdIRiceviFile` endpoint):
```php
<?php

require_once("../config.php");
require_once("SdIRiceviFileHandler.php");

$srv = new SoapServer('SdIRiceviFile_v1.0.wsdl');
$srv->setClass("SdIRiceviFileHandler");
$srv->handle();
```

which leverages the PHP [SoapServer class](http://php.net/manual/en/class.soapserver.php) and delegates the implementation to a handler class `SdIRiceviFileHandler`.


The handler class is implemented in [a file with the same name `SdIRiceviFileHandler.php` in the endpoint directory, and uses robust type cheching thanks to **type hinting** and the [type declarations](http://php.net/manual/en/functions.arguments.php#functions.arguments.type-declaration) obtained from wsdl2phpgenerator.

## Getting Started

### Prerequisites

Install prerequisites:
```
sudo apt install php-cli php-fpm composer nginx php-soap
```
then PHP packages:
```
composer install
```

### Configuring and Installing

Install the repo content to a directory (i.e. `testsdi`) of your webserver with nginx and fpm properly configured, or start locally with:
```sh
php -S localhost:8000
```

### Demo

Visit https://example.com/testsdi/test.php or http://localhost:8000/test.php and get a trace of a simulated call to `SdIRiceviFile.RiceviFile`:
```
identificativo SDI = 1
data ora ricezione = DateTime Object (
  [date] => 2018-07-26 12:38:45.000000
  [timezone_type] => 1
  [timezone] => +02:00 )
errore = EI01
```
## Testing

### Unit tests

TODO

### Linting

This project complies with the [PSR-2: Coding Style Guide](https://www.php-fig.org/psr/psr-2/).

Lint the code with:
```
./vendor/bin/phpcs --standard=PSR2 xxx.php
```

## Contributing

For your contributions please use the [git-flow workflow](https://danielkummer.github.io/git-flow-cheatsheet/).

## Authors

TODO

## License

Copyright (c) 2018, Marco Peca and Paolo Greppi, simevo s.r.l.

License: AGPL 3, see [LICENSE](LICENSE) file.
