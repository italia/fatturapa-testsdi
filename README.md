# fatturapa-testsdi

This repository will contain a complete test environment for the Exchange System (ES, Italian: **SDI**) for Electronic Invoices, including implementations for the ES itself and for the other participants.

The test environment can be used to:

1. simulate the complete process of invoice issue, transmission and receipt, including all notifications and handling of anomalous situations

2. during the development of SDICoop compliant services, simulate, debug and test locally their interaction with the ES and other actors

3. develop SDICoop-compliant services, forking the IR (Issuer/Recipient) implementation

4. develop higher-level applications that interact with a SDICoop compliant service, i.e. user interfaces, invoice/notification archiving ...

At this stage the testsdi in **WIP** and not fully implemented.

Some functionalities are also **excluded** from the initial design:
- receiving / transmitting ZIP archives
- receiving / transmitting invoices with multiple `FatturaElettronicaBody` elements ("multi-invoices")
- signature verification

**Index**:

* [Introduction](#introduction)
* [Architecture](#architecture)
* [Implementation](#implementation)
  + [State machines](#state-machines)
  + [Database schema](#database-schema)
  + [APIs](#apis)
* [Getting Started](#getting-started)
  + [Prerequisites](#prerequisites)
  + [Configuring and Installing](#configuring-and-installing)
  + [Demo](#demo)
* [Testing](#testing)
  + [Unit tests](#unit-tests)
  + [Linting](#linting)
* [Contributing](#contributing)
* [Authors](#authors)
* [License](#license)

## Introduction

The ES system run by the Italian government (SDICoop Service - Transmit / Receive) is used to transmit and receive invoices, receipts and notifications.

There are three actors:

- **issuer** (Italian: trasmittente), implements 1 SOAP Web Service (WS):

    - SDICoop Transmit / **TrasmissioneFatture**: to receive receipts and notifications from the ES

  and uses the ES SDICoop Transmit / SdIRiceviFile endpoint to issue invoices

- **ES** (forwards invoice from issuer to recipient Italian: SDI), implements 2 SOAP WS:

    - SDICoop Transmit / **SdIRiceviFile**, gets invoices from the issuer
    - SDICoop Receive / **SdIRiceviNotifica**: gets receipts from the recipient and forwards it to the issuer

  and uses:

    - and the issuer SDICoop Transmit / TrasmissioneFatture endpoint to send receipts notifications
    - the recipent SDICoop Receive / RicezioneFatture endpoint to send invoices

- and **recipient** (Italian: destinatario), implements 1 SOAP WS:

    - SDICoop Receive / **RicezioneFatture**: to get invoices and notifications from the SDI

  and uses the ES SdIRiceviNotifica WS to send notifications

This can also be seen grouping separately the two services:

**SDICoop Transmit**

![Transmit](/images/trasmissione.png)

**SDICoop Receive**

![Receive](/images/ricezione.png)

There is some [English documentation](http://fatturapa.gov.it/export/fatturazione/en/normativa/f-3.htm?l=en) but it's outdated. The [Italian documentation](http://fatturapa.gov.it/export/fatturazione/it/normativa/f-3.htm?l=it) is more up-to-date.

## Architecture

The testsdi is monolithic but modular, so that specific functionalities can be easily extracted.

There is a core `libsdi` component, with:
- state machine abstraction
- state persistency to database for each invoice and notification
- `Issuer`, `Exchange` and `Recipient` classes

The `libsdi` API is used by the **SOAP adaptor**, which exposes client and server interfaces, plus a `SoapAdaptor` class used by `libsdi` to perform calls to SOAP servers.

The `libsdi` is also used by the **RPC adaptor**, which exposes a subset of methods as Remote Procedure Calls over the HTTP protocol.
This interface can be used to control the simulation / tests or to show status information in user interfaces.

![Architecture](/images/architecture.png)

## Implementation

### State machines

Legend for all state machine diagrams:

![Legend](/images/legend.png)

**issuer** (trasmittente)

| Status | Description |
| ------------- | ------------- |
| I_UPLOADED | ready to be transmitted  |
| I_TRANSMITTED | transmitted to ES |
| I_DELIVERED | ES notified that it was delivered to Recipient |
| I_FAILED_DELIVERY | failed delivery within 48 hours |
| I_INVALID | ES notified it was invalid |
| I_IMPOSSIBLE_DELIVERY | ES notified that it was not delivered to the recipient within 48 hours + 10 days |
| I_ACCEPTED | ES notified that it was not accepted by the recipient |
| I_REFUSED | ES notified that it was not refused by the recipient |
| I_EXPIRED | ES notified that it was not accepted / refused by the recipient for more than 15 days |

![issuer finite state machine](/images/issuer.png)

**exchange system, ES** (sistema di interscambio, SDI)

| Status | Description |
| ------------- | ------------- |
| E_RECEIVED = I_TRANSMITTED | received from transmitter |
| E_VALID | passed checks |
| E_FAILED_DELIVERY | failed delivery within 48 hours |
| E_DELIVERED | delivered to recipient |
| E_INVALID | did not pass test |
| E_IMPOSSIBLE_DELIVERY | failed delivery within 48 hours + 10 days |
| E_ACCEPTED | Recipient notified that it accepted the invoice |
| E_REFUSED | Recipient notified that it refused the invoice |
| E_EXPIRED |  not accepted / refused by the recipient for more than 15 days |

![exchange system finite state machine](/images/exchange.png)

**recipent** (destinatario):

| Status | Description |
| ------------- | ------------- |
| R_RECEIVED = E_DELIVERED |received from ES |
| R_ACCEPTED | Accepted |
| R_REFUSED | Refused |
| R_EXPIRED | ES notified that it was not accepted / refused by the recipient for more than 15 days |

![recipient finite state machine](/images/recipient.png)

**notifier**

| Status | Description |
| ------------- | ------------- |
| N_RECEIVED | event has been triggered and must be processed |
| N_PENDING | event has been triggered and must be processed |
| N_OBSOLETE | event has been triggered but must not be processed because another event has been triggered that makes notifcation of this one useless |
| N_DELIVERED | event has been delivered |

### Database schema

There is a common database schema for all actors, consisting in two tables:

**invoices**:
- uuid
- invoice reference based on file and position:
  - nomefile
  - posizione
- invoice reference based on uniqueness of the mandatory invoice fields:
  - cedente
  - anno
  - numero
- status
- blob

**notifications**:
- uuid
- invoice_uuid
- type
- state (N_RECEIVED, N_PENDING, N_OBSOLETE, N_DELIVERED)
- blob

When an instance of one of the three actors is created, it is assigned a separate database instance.

The only difference is what states will be stored in the invoices.status column.

### APIs

**SOAP adaptor**

`SoapAdaptor` class exposes the API necessary for the `libsdi` to interact with SOAP servers:
- `SoapAdaptor::notify(type, invoice_blob, notification_blob)` connects to SDICoop Transmit / TrasmissioneFatture, SDICoop Receive / SdIRiceviNotifica and RicezioneFatture
- `SoapAdaptor::transmit(invoice_blob)` connects to SDICoop Transmit / SdiRiceviFile.RiceviFile

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

The handler class is implemented in [a file with the same name `SdIRiceviFileHandler.php` in the endpoint directory](/SdIRiceviFile/SdIRiceviFileHandler.php), and uses robust type cheching thanks to **type hinting** and the [type declarations](http://php.net/manual/en/functions.arguments.php#functions.arguments.type-declaration) obtained from wsdl2phpgenerator.

**libsdi**

All `libsdi` classes have a common `Base` class with:
- `Base::clear`: resets the state, called by `POST /clear`
- `Base::setTimeStamp(timestamp)`: used to tweak the timestamp of all following events, called by `POST /timestamp/ {iso8601_UTC_timetamp: 2018-09-15T23:59Z}`
- `Base::setSpeed(multiplier)`: between calls to `setTimeStamp`, time will flow at 1 x factor w.r.t. to wallclock time, called by `POST /speed/10`
- `Base::getTimestamp`: retrieves current simulated date and time, called by `GET /timestamp`
- SDICoop Transmit / TrasmissioneFatture notifications servers are implemented calling:
  - `Base::receive(invoice, type, notification_blob)` stores a N_RECEIVED notification for the invoice+type, including the notification payload as binary blob
- all `Exchange` and `Recipient` methods that must notify call:
  - `Base::enqueue(invoice, type, notification_blob)` creates a N_PENDING notification for the invoice+type and makes any previous N_PENDING record for the same invoice+type N_OBSOLETE
- `Base::dispatch` will attempt dispatching calling `SoapAdaptor::notify(type, invoice_blob, notification_blob)` for all N_PENDING notifications, on success makes the notification N_DELIVERED; called by `POST /dispatch`

The `Issuer` class is used to implement the **issuer** actor and has:
- `Issuer::Issuer(db)`: instantiate and point to a dedicated database instance
- `Issuer::upload(XML)` send to I_UPLOADED, called by `POST /upload {file XML}`
- `Issuer::transmit` will attempt transmission calling the `SoapAdaptor::transmit(invoice_blob)` for all invoices in I_UPLOADED state, on success they are moved to I_TRANSMITTED; called by `POST /transmit`
- SDICoop Transmit / TrasmissioneFatture notifications services are implemented with:
  - `Issuer::invalid(invoices)` send to I_INVALID
  - `Issuer::failed(invoices)` send to I_FAILED_DELIVERY
  - `Issuer::delivered(invoices)` send to I_DELIVERED
  - `Issuer::accepted(invoices)` send to I_ACCEPTED, via I_DELIVERED if necessary
  - `Issuer::refused(invoices)` send to I_REFUSED, via I_DELIVERED if necessary
  - `Issuer::expired(invoices)` send to I_EXPIRED, via I_DELIVERED if necessary

The `Exchange` class is used to implement the **exchange system** actor and has:
- `Exchange::Exchange(db)`: instantiate and point to a dedicated database instance
- `Exchange::receive(XML)` send to E_RECEIVED, called by SDICoop Transmit / SdiRiceviFile.RiceviFile server implementation
- `Exchange::checkValidity` will perform checks for all invoices in E_RECEIVED state and move them to E_INVALID or E_VALID, called by `POST /checkValidity`
- `Exchange::deliver`: will move all invoices that have been E_VALID for longer than 48 hours to the E_FAILED_DELIVERY, will move all invoices that have been E_FAILED_DELIVERY for longer than 10 days to E_IMPOSSIBLE_DELIVERY; finally, will attempt delivery to recipient with SDICoop Receive
RicezioneFatture for all the others, on success they are moved to E_DELIVERED; called by `POST /deliver`
- `Exchange::checkExpiration`: will move all invoices that have been E_DELIVERED for longer than 15 days to the E_EXPIRED, called by `POST /checkExpiration`
- `Exchange::accept(invoices)` send to E_ACCEPTED, called by SDICoop – Ricezione / SdiRiceviNotifica.NotificaEsito
- `Exchange::refuse(invoices)` send to E_REFUSED, called by SDICoop – Ricezione / SdiRiceviNotifica.NotificaEsito

The `Recipient` class is used to implement the **recipent** actor and has:
- `Recipient::Recipient(db)`: instantiate and point to a dedicated database instance
- `Recipient::receive(XML, metadata)` send to R_RECEIVED, called by SDICoop Ricezione / RicezioneFatture.RiceviFatture server implementation
- `Recipient::accept(invoices)`: move to R_ACCEPTED; called by `POST /accept {invoices}`
- `Recipient::refuse(invoices)`: move to R_REFUSED; called by `POST /refuse {invoices}`
- `Recipient::expire(invoices)` send to R_EXPIRED, called by SDICoop – Ricezione / RicezioneFatture.NotificaDecorrenzaTermini server implementation

**RPC adaptor**

- general simulation control:
  - `POST /clear`: resets the state
  - `GET /timestamp`: retrieves current simulated date and time
  - `POST /timestamp/ {iso8601_UTC_timetamp: 2018-09-15T23:59Z}`: tweak the timestamp of all following events
  - `POST /speed/10`: set factor for simulated time to wallclock time

- notifications:
  - `GET /notifications/uuid`: retrieves notification with uuid
  - `POST /dispatch`: attempt dispatching for all N_PENDING notifications, on success makes the notification N_DELIVERED

- invoices:
  - `GET /invoices?state=R_ACCEPTED`: retrieves array of uuids in R_ACCEPTED state
  - `GET /invoices/uuid`: retrieves invoice with uuid

- issuer-specific:
  - `POST /upload {file XML}` call `Issuer::upload(XML)`
  - `POST /transmit`: call `Issuer::transmit`

- exchange-specific
  - `POST /checkValidity`: call `Exchange::checkValidity`
  - `POST /deliver`: call `Exchange::deliver`
  - `POST /checkExpiration`: call `Exchange::checkExpiration`

- recipient-specific
  - `POST /accept {invoices}`: call `Recipient::accept(invoices)`
  - `POST /refuse {invoices}`: call `Recipient::refuse(invoices)`

## Getting Started

Tested on: amd64 Debian 9.5 (stretch, current stable) with PHP 7.0 and Laravel 5.1.46.

### Prerequisites

Install prerequisites:
```sh
sudo apt install php-cli php-fpm composer nginx php-soap php-mbstring php-dom php-zip composer nginx postgresql php-dev
```

### Configuring and Installing

**TODO**: In a future release you'll be able to configure the number of simulated issuer/receiver (IR) actors in `config.php` and dynamic routing will make sure that the actors will be reachable at `/sdi` (there's only one exchange system), `/td000001`, `/td000002`  ... (td stands for trasmittente/destinatario, Italian for issuer/receiver).

For example if you configure with three I/R actors, your SOAP endpoints will be at:
- exchange
  - https://www.example.com/sdi/soap/SdIRiceviFile
  - https://www.example.com/sdi/soap/SdIRiceviNotifica
- issuer / recipient 1:
  - https://www.example.com/td000001/soap/RicezioneFatture
  - https://www.example.com/td000001/soap/TrasmissioneFatture
- issuer / recipient 2:
  - https://www.example.com/td000002/soap/RicezioneFatture
  - https://www.example.com/td000002/soap/TrasmissioneFatture
- issuer / recipient 3:
  - https://www.example.com/td000003/soap/RicezioneFatture
  - https://www.example.com/td000003/soap/TrasmissioneFatture

For the moment being **only one actor** is supported (sdi), so clone the repo to the `/var/www/html/sdi` directory on your webserver.

Install [php-timecop](https://github.com/hnw/php-timecop) extension using `phpize`:
```
git clone https://github.com/hnw/php-timecop.git
cd php-timecop
phpize
./configure
make
make test
sudo make install
```
add a file `99-timecop.ini` in `/etc/php/7.0/cli/conf.d/` and in `/etc/php/7.0/fpm/conf.d/`:
```
; configuration for https://github.com/hnw/php-timecop
; priority=99
extension=timecop.so
```
and restart php-fpm: `sudo systemctl restart php7.0-fpm.service`

Install prerequisites with composer:

```sh
cd /var/www/html/sdi/illuminate
composer install
composer dumpautoload
composer dumpautoload -o
cd ../soap
composer install
cd ../rpc
composer install
composer dumpautoload
composer dumpautoload -o
```

Set up Laravel:
```sh
cd rpc
sudo chown -R www-data:www-data storage/logs/
sudo chown -R www-data:www-data storage/framework/
sudo chown -R www-data:www-data bootstrap/cache/
cp .env.example .env
php artisan key:generate
sudo su -s /bin/bash www-data
php artisan migrate
^d
```

Configure nginx:
```
sudo rm /etc/nginx/sites-enabled/*
sudo vi /etc/nginx/sites-enabled/fatturapa
server {
  listen 80 default_server;
  listen [::]:80 default_server;
  server_name teamdigitale3.simevo.com;
  root /var/www/html;
  index index.html index.htm index.php;
  location /sdi/rpc {
    try_files $uri $uri/ /sdi/rpc/index.php$is_args$args;
  }
  location ~ \.php$ {
    include snippets/fastcgi-php.conf;
    fastcgi_pass unix:/var/run/php/php7.0-fpm.sock;
    fastcgi_read_timeout 300;
  }
}
sudo nginx -t
sudo systemctl restart nginx
```

Configure the database:

1. add this line:
```
    local   testsdi     www-data                                   md5
```
  before this one:
```
    # "local" is for Unix domain socket connections only
    local   all             all                                     peer
```
  in `/etc/postgresql/9.6/main/pg_hba.conf`

2. restart postgresql with: `sudo systemctl restart postgresql`

3. Create the database:
```sh
sudo su - postgres
psql
CREATE DATABASE testsdi OWNER "www-data";
ALTER USER "www-data" WITH PASSWORD 'www-data';
^d
^d
```

You'll be able to access the database with:
```sh
PGPASSWORD="www-data" psql -U www-data testsdi
```

Configure database credentials in `illuminate/config.php` and in `rpc/config/database.php`.

Configure `HOSTNAME` in `soap/config.php`.

### Simple demo

Send a test invoice to the exchange system with:
```
./soap/test.php
```
or via this form:
http://teamdigitale3.simevo.com/sdi/receiveForm.php
which will POST to `https://www.example.com/sdi/soap/SdIRiceviFile/test_RiceviFile.php`.

Both `test.php` and `test_RiceviFile.php` in turn pose as SOAP clients and forward to the SOAP server which listens at: 
http://www.example.com/sdi/soap/SdIRiceviFile/
The SOAP server will insert the invoice entry in the database.

Sample RPC endpoint:
http://www.example.com/sdi/rpc/invoices?state=E_RECEIVED
According to `rpc/packages/fatturapa/libsdi/src/routes/web.php`:
```php
Route::get('invoices', 'fatturapa\libsdi\InvoicesController@index');
```
this route is handled by the `InvoicesController` class defined in `rpc/packages/fatturapa/libsdi/src/InvoicesController.php`

### Full demo

Sample manual session to demonstrate the flow of one invoice from issuer 0000001 to recipient 0000002, and acceptance:

1. clear status
```
POST https://test.example.com/sdi/rpc/clear
POST https://test.example.com/td000001/rpc/clear
POST https://test.example.com/td000002/rpc/clear
```

2. create a valid sample invoice for IR 000002 and upload it to IT 0000001, then check it is in the right queue
```
POST https://test.example.com/td000001/rpc/upload {file XML}
GET https://test.example.com/td000001/rpc/invoices?state=I_UPLOADED
```

3. force transmission to ES and check status:
```
POST https://test.example.com/td000001/rpc/transmit
GET https://test.example.com/sdi/rpc/invoices?state=E_RECEIVED
GET https://test.example.com/td000001/rpc/invoices?state=I_TRANSMITTED (no response yet)
```

4. force ES to dispatch back the notification to the issuer:
```
POST https://test.example.com/sdi/rpc/dispatch
```

5. check notification and status:
```
GET https://test.example.com/td000001/rpc/notifications/uuid
GET https://test.example.com/td000001/rpc/invoices?state=I_TRANSMITTED
```

6. force validation by ES and check state
```
POST https://test.example.com/sdi/rpc/checkValidity
GET https://test.example.com/sdi/rpc/invoices?state=E_VALID
```

7. force transmission from ES to recipient and check state
```
POST https://test.example.com/sdi/rpc/deliver
GET https://test.example.com/sdi/rpc/invoices?state=E_DELIVERED
GET https://test.example.com/sdi/td000002/invoices?state=R_RECEIVED
GET https://test.example.com/td000001/rpc/invoices?state=I_DELIVERED (no response yet)
```

8. force ES to dispatch back the notification to the issuer:
```
POST https://test.example.com/sdi/rpc/dispatch
```

9. check notification and status:
```
GET https://test.example.com/td000001/rpc/notifications/uuid
GET https://test.example.com/td000001/rpc/invoices?state=I_DELIVERED
```

10. make recipient accept invoice and check status:
```
POST https://test.example.com/td000002/rpc/accept/uuid
GET https://test.example.com/td000002/rpc/invoices?state=R_ACCEPTED
GET https://test.example.com/sdi/rpc/invoices?state=E_ACCEPTED (no response yet)
```

11. force receiver to dispatch back the notification to the ES:
```
POST https://test.example.com/td000002/rpc/dispatch
```

12. check notification and status:
```
GET https://test.example.com/sdi/rpc/notifications/uuid
GET https://test.example.com/sdi/rpc/invoices?state=E_ACCEPTED
GET https://test.example.com/td000002/rpc/invoices?state=I_ACCEPTED (no response yet)
```

13. force ES to dispatch back the acceptance notification to the issuer:
```
POST https://test.example.com/sdi/rpc/dispatch
```

14. check notification and status:
```
GET https://test.example.com/td000001/rpc/notifications/uuid
GET https://test.example.com/td000002/rpc/invoices?state=I_ACCEPTED
```

## Testing

### Unit tests

Test the PHP classes with:
```
phpunit --testdox tests
```

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
