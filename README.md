# fatturapa-testsdi

> ⚠️ **WORK IN PROGRESS** ⚠️

This repository contains a complete test environment for the Exchange System (ES, Italian: **SDI**) for Electronic Invoices, including implementations for the ES itself and for the other participants.

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

The following animation shows the minimal **workflow** from invoice issue to receipt of acceptance:

![Workflow](/images/workflow.gif)

This can also be seen grouping separately the two services:

**SDICoop Transmit**

![Transmit](/images/trasmissione.png)

**SDICoop Receive**

![Receive](/images/ricezione.png)

There is some [English documentation](http://fatturapa.gov.it/export/fatturazione/en/normativa/f-3.htm?l=en) but it's outdated. The [Italian documentation](http://fatturapa.gov.it/export/fatturazione/it/normativa/f-3.htm?l=it) is more up-to-date.

## Architecture

The testsdi is monolithic but modular, so that specific functionalities can be easily extracted.

A distinctive design choice has been to use the same database schema, API and structure for all actors. Rather than breaking it down in components based on the actors, it has been broken down in layers:

1. The **SOAP server** component exposes the interfaces required to communicate in accordance with the FatturaPA specification; it uses the _fatturapa-core_ classes

2. The **core** component ([fatturapa-core]((/core/README.md)), has:
  - state machine abstraction
  - state persistency to database for each invoice and notification
  - `Base`, `Issuer`, `Exchange` and `Recipient` classes.
  - accesses the SOAP endpoints acting as SOAP client.

3. The **control** component ([fatturapa-control](/rpc/packages/fatturapa/control/README.md)), 
also uses _fatturapa-core_, and exposes a Remote Procedure Calls (RPC) API over the HTTP protocol. This API can be used to control the simulation / tests or to show status information in user interfaces.

4. The _fatturapa-control_ is used by the basic **User Interface** [fatturapa-testui](https://github.com/simevo/fatturapa-testui).

![Architecture](/images/architecture.png)

## Implementation

## State diagrams

The invoices change state during the workflow; certain state changes trigger notifications that have to be sent to specific actors.

The states are represented with strings starting with `E_` for exchanger states, `I_` for issuer states, `R_` for recipient states and `N_` for notification states.

The possible states, state changes and triggers are shown in the following [state diagrams](https://en.wikipedia.org/wiki/State_diagram).

Legend for all state diagrams:

![Legend](/images/legend.png)

#### Issuer, Italian: trasmittente

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

#### Exchange System (ES), Italian: Sistema di Interscambio (SDI)

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

#### Recipent, Italian: destinatario

| Status | Description |
| ------------- | ------------- |
| R_RECEIVED = E_DELIVERED |received from ES |
| R_ACCEPTED | Accepted |
| R_REFUSED | Refused |
| R_EXPIRED | ES notified that it was not accepted / refused by the recipient for more than 15 days |

![recipient finite state machine](/images/recipient.png)

#### Notifier

| Status | Description |
| ------------- | ------------- |
| N_RECEIVED | inbound notification has been received |
| N_PENDING | outbound notification has been generated and must be processed |
| N_OBSOLETE | outbound notification has been generated but must not be processed because another notification has been generated that makes notifcation of this one useless |
| N_DELIVERED | outbound notification has beensuccessfully delivered |

### Database schema

There is a common database for all actors, consisting in three tables:

**invoices**:

- id: integer, primary key
- remote_id: the id of this invoice for the sdi actor
- invoice reference based on file and position:
  - nomefile
  - posizione
- invoice reference based on uniqueness of the mandatory invoice fields:
  - cedente
  - anno
  - numero
- status: state machine status
- blob: base64-encoded blob of the invoice
- ctime: record creation time in database
- actor: the actor on behalf on whom we are storing the invoice, one of: sdi, td + 7-characters code
- issuer: 7-characters code of the original issuer of the invoice

**notifications**:

- id: integer, primary key
- invoice_id
- type: one of AttestazioneTrasmissioneFattura, NotificaDecorrenzaTermini, RicevutaConsegna, ...
- status: one of N_RECEIVED, N_PENDING, N_OBSOLETE, N_DELIVERED
- blob: base64-encoded blob of the notification
- actor: the actor on behalf on whom we are storing the notification, one of: sdi, td + 7-characters code
- nomefile
- ctime: record creation time in database

**channels** (lookup table between `cedente` and `issuer`):

- cedente: primary key
- issuer: 7-characters code for issuer which transmits invoices on behalf of cedente

### SOAP adaptor

For each of the four SOAP Web Services, we start from the [Web Services Description Language, (**WSDL**)](https://en.wikipedia.org/wiki/Wsdl) and [XML Schema Definition, (**XSD**)](https://en.wikipedia.org/wiki/XML_Schema_(W3C) files from fatturapa.gov.it, feed them to [wsdl2phpgenerator](https://github.com/wsdl2phpgenerator/wsdl2phpgenerator) which generates types and boilerplate for the endpoint in a directory **named as the endpoint**.

This code generation step has been performed once and for all by the [soap/bin/generate.php](/soap/bin/generate.php) script.

In each of the four resulting directory matching the endpoints, we place a `index.php` file similar to (this one is for the `SdIRiceviFile` endpoint):
```php
require_once("../config.php");
require_once("SdIRiceviFileHandler.php");
require_once("../SoapServerDebug.php");

$srv = new \SoapServer('SdIRiceviFile_v1.0.wsdl');
$srv->setClass("SdIRiceviFileHandler");
$srv->handle();
```

which leverages the PHP [SoapServer class](http://php.net/manual/en/class.soapserver.php) and delegates the implementation to a handler class `SdIRiceviFileHandler`.

The handler class is implemented in [a file with the same name `SdIRiceviFileHandler.php` in the endpoint directory](/SdIRiceviFile/SdIRiceviFileHandler.php), and uses robust type cheching thanks to **type hinting** and the [type declarations](http://php.net/manual/en/functions.arguments.php#functions.arguments.type-declaration) obtained from wsdl2phpgenerator.

## Getting Started

Tested on: amd64 Debian 9.5 (stretch, current stable) with PHP 7.0 and Laravel 5.1.46.

### Prerequisites

Install prerequisites:
```sh
sudo apt install php-cli php-fpm nginx php-soap php-mbstring php-dom php-zip composer nginx postgresql php-pgsql php-curl php-xml
```

### Configuring and Installing

**TODO**: In a future release you'll be able to configure the number of simulated issuer/receiver (TD = trasmittente / destinatario) actors in `config.php` and dynamic routing will make sure that the actors will be reachable at `/sdi` (there's only one exchange system), `/td0000001`, `/td0000002`  ... (td stands for trasmittente/destinatario, Italian for issuer/receiver).

For example if you configure with three I/R actors, your SOAP endpoints will be at:
- exchange
  - https://www.example.com/sdi/soap/SdIRiceviFile
  - https://www.example.com/sdi/soap/SdIRiceviNotifica
- issuer / recipient 1:
  - https://www.example.com/td0000001/soap/RicezioneFatture
  - https://www.example.com/td0000001/soap/TrasmissioneFatture
- issuer / recipient 2:
  - https://www.example.com/td0000002/soap/RicezioneFatture
  - https://www.example.com/td0000002/soap/TrasmissioneFatture
- issuer / recipient 3:
  - https://www.example.com/td000003/soap/RicezioneFatture
  - https://www.example.com/td000003/soap/TrasmissioneFatture

For the moment being **only three actors** are supported (sdi, td0000001 and td0000002), so clone the repo to the `/var/www/html/sdi` directory on your webserver then manually create symlinks and storage dirs as in:
```sh
cd /var/www/html
mkdir td0000001
mkdir td0000002
cd td0000001
ln -s ../sdi/soap/ soap
mkdir core rpc
cd core
ln -s ../../sdi/core/app app
ln -s ../../sdi/core/config.php config.php
ln -s ../../sdi/core/storage storage
ln -s ../../sdi/core/vendor vendor
cd ../rpc
ln -s ../../sdi/rpc/app app
ln -s ../../sdi/rpc/bootstrap bootstrap
ln -s ../../sdi/rpc/config config
ln -s ../../sdi/rpc/database database
ln -s ../../sdi/rpc/index.php index.php
ln -s ../../sdi/rpc/packages packages
ln -s ../../sdi/rpc/resources resources
ln -s ../../sdi/rpc/vendor vendor
cd ../../td0000002
...
```

Install prerequisites with composer:

```sh
cd /var/www/html/sdi
composer install
cd core
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

Configure the database:

1. in `/etc/postgresql/9.6/main/pg_hba.conf` add this line:
```
local   testsdi         www-data                                md5
```
  **before** this one:
```
# "local" is for Unix domain socket connections only
local   all             all                                     peer
```

2. restart postgresql with: `sudo systemctl restart postgresql`

3. Create the database:
```sh
sudo su - postgres
psql
CREATE USER "www-data" WITH PASSWORD 'www-data';
CREATE DATABASE testsdi OWNER "www-data";
^d
^d
```

You'll be able to access the database with:
```sh
PGPASSWORD="www-data" psql -U www-data testsdi
```

Configure database credentials in `core/config.php` and in `rpc/config/database.php`.

Configure `HOSTNAME` in `soap/config.php` and in `core/config.php`.

Set up Laravel:
```sh
sudo chown www-data storage/time_travel.json
cd ../rpc
touch storage/logs/laravel.log
sudo chown -R www-data storage/logs
sudo chmod g+w storage/logs/laravel.log
sudo chown -R www-data storage/framework
sudo chown -R www-data bootstrap/cache
cp .env.example .env
php artisan key:generate
sudo su -s /bin/bash www-data
php artisan migrate
^d
```

Fill in channels table so that invoices can be sent (needed for the tests):
```sql
INSERT INTO channels(cedente, issuer) VALUES ('IT-01234567890', '0000001');
INSERT INTO channels(cedente, issuer) VALUES ('IT-12345678901', '0000002');
```

Configure nginx:
```
sudo rm /etc/nginx/sites-enabled/*
sudo vi /etc/nginx/sites-enabled/fatturapa
server {
  listen 80 default_server;
  listen [::]:80 default_server;
  server_name testsdi.simevo.com;
  root /var/www/html;
  index index.html index.htm index.php;
  location /sdi/rpc {
    try_files $uri $uri/ /sdi/rpc/index.php$is_args$args;
  }
  location /td0000001/rpc {
    try_files $uri $uri/ /td0000001/rpc/index.php$is_args$args;
  }
  location /td0000002/rpc {
    try_files $uri $uri/ /td0000002/rpc/index.php$is_args$args;
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

### Demo

Sample manual session to demonstrate the flow of one invoice from issuer 0000001 to recipient 0000002, and acceptance:

1. clear status
```
POST https://test.example.com/sdi/rpc/clear
POST https://test.example.com/td0000001/rpc/clear
POST https://test.example.com/td0000002/rpc/clear
```

2. create a valid sample invoice for TD 0000002 (`FatturaElettronica.FatturaElettronicaHeader.DatiTrasmissione.CodiceDestinatario` should be set to `0000002`) and upload it to TD 0000001, then check it is in the right queue

```
POST https://test.example.com/td0000001/rpc/upload {file XML}
GET https://test.example.com/td0000001/rpc/invoices?status=I_UPLOADED
```

3. force transmission to ES and check status:
```
POST https://test.example.com/td0000001/rpc/transmit
```

4. Check status with ES (the invoice should be in the E_RECEIVED queue):
```
GET https://test.example.com/sdi/rpc/invoices?status=E_RECEIVED
```

5. Check status with TD 0000001 (the invoice should be in the I_TRANSMITTED queue):
```
GET https://test.example.com/td0000001/rpc/invoices?status=I_TRANSMITTED
```

6. force validation by ES and check status:
```
POST https://test.example.com/sdi/rpc/checkValidity
GET https://test.example.com/sdi/rpc/invoices?status=E_VALID
```

7. force transmission from ES to recipient and check status:
```
POST https://test.example.com/sdi/rpc/deliver
GET https://test.example.com/sdi/rpc/invoices?status=E_DELIVERED
GET https://test.example.com/sdi/td0000002/invoices?status=R_RECEIVED
GET https://test.example.com/td0000001/rpc/invoices?status=I_DELIVERED (no response yet because ES has not notified to issuer)
```

8. force ES to dispatch back the notification to the issuer:
```
POST https://test.example.com/sdi/rpc/dispatch
```

9. check notification and status, now for the issuer TD 0000001 the invoice should be in the I_DELIVERED queue:
```
GET https://test.example.com/td0000001/rpc/notifications/id
GET https://test.example.com/td0000001/rpc/invoices?status=I_DELIVERED
```

10. make recipient accept invoice and check status:
```
POST https://test.example.com/td0000002/rpc/accept/id
GET https://test.example.com/td0000002/rpc/invoices?status=R_ACCEPTED
GET https://test.example.com/sdi/rpc/invoices?status=E_ACCEPTED (no response yet)
```

11. force receiver to dispatch back the notification to the ES:
```
POST https://test.example.com/td0000002/rpc/dispatch
```

12. check notification and status:
```
GET https://test.example.com/sdi/rpc/notifications/id
GET https://test.example.com/sdi/rpc/invoices?status=E_ACCEPTED
GET https://test.example.com/td0000002/rpc/invoices?status=I_ACCEPTED (no response yet)
```

13. force ES to dispatch back the acceptance notification to the issuer:
```
POST https://test.example.com/sdi/rpc/dispatch
```

14. check notification and status:
```
GET https://test.example.com/td0000001/rpc/notifications/id
GET https://test.example.com/td0000002/rpc/invoices?status=I_ACCEPTED
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
