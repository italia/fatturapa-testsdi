# fatturapa-testsdi

> ⚠️ **WORK IN PROGRESS** ⚠️

This repository contains a complete test environment for the Exchange System (ES, Italian: **SDI**) for Electronic Invoices, including implementations for the ES itself and for the other participants.

The test environment can be used to:

1. simulate the complete process of invoice issue, transmission and receipt, including all notifications and handling of anomalous situations

2. during the development of SDICoop compliant services, simulate, debug and test locally their interaction with the ES and other actors

3. develop SDICoop-compliant services, forking the IR (Issuer/Recipient) implementation

4. develop higher-level applications that interact with a SDICoop compliant service, i.e. user interfaces, invoice/notification archiving ...

At this stage the testsdi is **WIP** and not fully implemented, most importantly these features are missing:
- message signing and signature verification (see [issue#21](https://github.com/italia/fatturapa-testsdi/issues/21))
- MTOM SOAP (see [issue #43](https://github.com/italia/fatturapa-testsdi/issues/43))

Some functionalities are also **excluded** from the initial design:
- receiving / transmitting ZIP archives (see [issue #25](https://github.com/italia/fatturapa-testsdi/issues/25))
- receiving / transmitting invoices with multiple `FatturaElettronicaBody` elements ("multi-invoices") (see [issue #22](https://github.com/italia/fatturapa-testsdi/issues/22))

**Index**:

* [Introduction](#introduction)
* [Architecture](#architecture)
* [Implementation](#implementation)
  + [State diagrams](#state-diagrams)
  + [Database schema](#database-schema)
  + [SOAP adaptor](#soap-adaptor)
* [Getting Started](#getting-started)
  + [Docker Compose](#docker-compose)
  + [Manual setup](#manual-setup)
  + [Channels and actors](#channels-and-actors)
  + [Demo](#demo)
* [Testing](#testing)
  + [Manual testing](#manual-testing)
  + [Unit tests](#unit-tests)
  + [Linting](#linting)
* [Troubleshooting](#troubleshooting)  
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

2. The **core** component ([fatturapa-core](/core/README.md)), has:
  - state machine abstraction
  - state persistency to database for each invoice and notification
  - `Base`, `Issuer`, `Exchange` and `Recipient` classes.
  - accesses the SOAP endpoints acting as SOAP client.

3. The **control** component ([fatturapa-control](/rpc/packages/fatturapa/control/README.md)), 
also uses _fatturapa-core_, and exposes a Remote Procedure Calls (RPC) API over the HTTP protocol. This API can be used to control the simulation / tests or to show status information in user interfaces.

4. The **ui** component [fatturapa-ui](/rpc/packages/fatturapa/ui/README.md) provides a basic **User Interface** to interact with the test environment.

This picture shows how the 4 layers stack up:

![Architecture](/images/architecture.png)

This screencast demonstrates the complete workflow (see [Demo](#demo) section below) as seen through the UI, i.e. how you can send an invoice from I/R 0000001 to I/R 0000002, and make sure that the various notifications are sent back and forth between the three involved actors until the invoice acceptance is confirmed for all the parties:

![img](images/screencast.gif)

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
| I_ACCEPTED | ES notified that it was accepted by the recipient |
| I_REFUSED | ES notified that it was refused by the recipient |
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

#### Recipient, Italian: destinatario

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

For each of the four SOAP Web Services, we started from the [Web Services Description Language, (**WSDL**)](https://en.wikipedia.org/wiki/Wsdl) and [XML Schema Definition, (**XSD**)](https://en.wikipedia.org/wiki/XML_Schema_(W3C)) files from fatturapa.gov.it, fed them to [wsdl2phpgenerator](https://github.com/wsdl2phpgenerator/wsdl2phpgenerator) which generated types and boilerplate for the endpoint in a directory **named as the endpoint**.

This code generation step has been performed once and for all by the [soap/bin/generate.php](/soap/bin/generate.php) script.

In each of the four resulting directory matching the endpoints, we place a `index.php` file similar to (this one is for the `SdIRiceviFile` endpoint):
```php
require_once("SdIRiceviFileHandler.php");

$srv = new \SoapServer('SdIRiceviFile_v1.0.wsdl');
$srv->setClass("SdIRiceviFileHandler");
$srv->handle();
```

which leverages the PHP [SoapServer class](http://php.net/manual/en/class.soapserver.php) and delegates the implementation to a handler class `SdIRiceviFileHandler`.

The handler class is implemented in [a file with the same name `SdIRiceviFileHandler.php` in the endpoint directory](/soap/SdIRiceviFile/SdIRiceviFileHandler.php), and uses robust type cheching thanks to **type hinting** and the [type declarations](http://php.net/manual/en/functions.arguments.php#functions.arguments.type-declaration) obtained from wsdl2phpgenerator.

## Getting Started

Tested on: amd64 Debian 9.5 (stretch, current stable) with PHP 7.0 and Laravel 5.5.44.

### Docker compose

The quickest way to start an instance of the testsdi is using [Docker Compose](https://docs.docker.com/compose/overview/).

The supplied [docker-compose.yml](/docker-compose.yml) file defines and runs a four-container Docker application that comprises:
- PHP 7.2 with PHP-FPM and the required modules
- Nginx
- PostgreSQL 10.5
- [Adminer](https://www.adminer.org/)

You will need:
* [Docker CE](https://docs.docker.com/engine/installation/)
* [Docker Compose](https://docs.docker.com/compose/install)

To start the application, run `docker-compose up --build` from the root of the project.

To install prerequisites, create tables with composer and set-up Laravel, issue the `make` command in the php container:
```sh
docker-compose exec php make
```

Nginx will serve on `http://localhost:8081` and [Adminer] will be available on `http://localhost:8082`.

Default connection to PostgreSQL:

`docker-compose exec db psql -U www-data testsdi`

Execute commands on the `php` container with:

`docker-compose exec php php -v`

### Manual setup

Install prerequisites:
```sh
sudo apt install php-cli php-fpm nginx php-soap php-mbstring php-dom php-zip composer nginx postgresql php-pgsql php-curl php-xml
```

Clone the repo into the `/var/www/html` directory on your webserver. 

```sh
cd /var/www/html
git clone https://github.com/italia/fatturapa-testsdi .
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

Configure database credentials, HOSTNAME and paths in `core/config.php` and in `soap/config.php`.

Configure nginx:
```sh
sudo rm /etc/nginx/sites-enabled/*
sudo vi /etc/nginx/sites-enabled/fatturapa
```

Set the contents of the `/etc/nginx/sites-enabled/fatturapa` file to something like:
```
server {
  listen 80 default_server;
  listen [::]:80 default_server;
  server_name testsdi.simevo.com;
  root /var/www/html;
  index index.html index.htm index.php;
  location ~ \.php$ {
    include snippets/fastcgi-php.conf;
    fastcgi_pass unix:/var/run/php/php7.0-fpm.sock;
    fastcgi_read_timeout 300;
    fastcgi_param SDI_DB_HOST localhost;
    fastcgi_param SDI_DB_NAME testsdi;
    fastcgi_param SDI_DB_USER www-data;
    fastcgi_param SDI_DB_PASS www-data;
    fastcgi_param SDI_HOST_MAIN https://teamdigitale3.simevo.com/;
  }
  location ^~ /sdi/rpc/js/ {
    alias /var/www/html/rpc/packages/fatturapa/ui/src/public/js/;
  }
  location ^~ /sdi/rpc/css/ {
    alias /var/www/html/rpc/packages/fatturapa/ui/src/public/css/;
  }
  location ^~ /sdi/rpc/webfonts/ {
    alias /var/www/html/rpc/packages/fatturapa/ui/src/public/webfonts/;
  }
  location ^~ /sdi/rpc/font/ {
    alias /var/www/html/rpc/packages/fatturapa/ui/src/public/font/;
  }
  location ~ /.*/rpc {
    try_files $uri $uri/ /rpc/index.php?$query_string;
  }
  location ~ /.*/soap {
    try_files $uri $uri/ /soap/index.php;
  }
}
```

Finally check the configuration and restart nginx:
```sh
sudo nginx -t
sudo systemctl restart nginx
```

Install prerequisites and create tables with composer, and set-up Laravel with:
```sh
make
```

At this point you should be able to access the UI at: https://testsdi.example.com/sdi/rpc/dashboard

Dynamic routing makes sure that the RPC endpoints for the actors will be reachable at:
- `/sdi` - the Exchange System (there's only one)
- `/tdxxxxxxx`, `/tdyyyyyyy`, ... - where td stands for trasmittente/destinatario (T/D), Italian for issuer/receiver (I/R) and `xxxxxxx`, `yyyyyyy` are the 7-characters I/R identification codes.

### Channels and actors

The number of simulated I/R (T/D) actors are autoconfigured based on the actors that appear in the `channels` table.

For example if you set the channels table like this so that invoices can be sent (needed for the tests):
```sql
INSERT INTO channels(cedente, issuer) VALUES ('IT-01234567890', '0000001');
INSERT INTO channels(cedente, issuer) VALUES ('IT-12345678901', '0000002');
INSERT INTO channels(cedente, issuer) VALUES ('IT-23456789012', '0000003');
```
your SOAP endpoints will be at:
- exchange
  - https://www.example.com/sdi/soap/SdIRiceviFile
  - https://www.example.com/sdi/soap/SdIRiceviNotifica
- issuer / recipient 0000001:
  - https://www.example.com/td0000001/soap/RicezioneFatture
  - https://www.example.com/td0000001/soap/TrasmissioneFatture
- issuer / recipient 0000002:
  - https://www.example.com/td0000002/soap/RicezioneFatture
  - https://www.example.com/td0000002/soap/TrasmissioneFatture
- issuer / recipient 0000003:
  - https://www.example.com/td0000003/soap/RicezioneFatture
  - https://www.example.com/td0000003/soap/TrasmissioneFatture

### Demo

Sample manual session to demonstrate the flow of one invoice from issuer 0000001 to recipient 0000002, and acceptance:

1. clear status
```
POST https://www.example.com/sdi/rpc/clear
POST https://www.example.com/td0000001/rpc/clear
POST https://www.example.com/td0000002/rpc/clear
```

2. create a valid sample invoice for TD 0000002 (`FatturaElettronica.FatturaElettronicaHeader.DatiTrasmissione.CodiceDestinatario` should be set to `0000002`) and upload it to TD 0000001, then check it is in the right queue

```
POST https://www.example.com/td0000001/rpc/upload {file XML}
GET https://www.example.com/td0000001/rpc/invoices?status=I_UPLOADED
```

3. force transmission to ES and check status:
```
POST https://www.example.com/td0000001/rpc/transmit
```

4. Check status with ES (the invoice should be in the E_RECEIVED queue):
```
GET https://www.example.com/sdi/rpc/invoices?status=E_RECEIVED
```

5. Check status with TD 0000001 (the invoice should be in the I_TRANSMITTED queue):
```
GET https://www.example.com/td0000001/rpc/invoices?status=I_TRANSMITTED
```

6. force validation by ES and check status:
```
POST https://www.example.com/sdi/rpc/checkValidity
GET https://www.example.com/sdi/rpc/invoices?status=E_VALID
```

7. force transmission from ES to recipient and check status:
```
POST https://www.example.com/sdi/rpc/deliver
GET https://www.example.com/sdi/rpc/invoices?status=E_DELIVERED
GET https://www.example.com/sdi/td0000002/invoices?status=R_RECEIVED
GET https://www.example.com/td0000001/rpc/invoices?status=I_DELIVERED (no response yet because ES has not notified to issuer)
```

8. force ES to dispatch back the notification to the issuer:
```
POST https://www.example.com/sdi/rpc/dispatch
```

9. check notification and status, now for the issuer TD 0000001 the invoice should be in the I_DELIVERED queue:
```
GET https://www.example.com/td0000001/rpc/notifications/id
GET https://www.example.com/td0000001/rpc/invoices?status=I_DELIVERED
```

10. make recipient accept invoice and check status:
```
POST https://www.example.com/td0000002/rpc/accept/id
GET https://www.example.com/td0000002/rpc/invoices?status=R_ACCEPTED
GET https://www.example.com/sdi/rpc/invoices?status=E_ACCEPTED (no response yet)
```

11. force receiver to dispatch back the notification to the ES:
```
POST https://www.example.com/td0000002/rpc/dispatch
```

12. check notification and status:
```
GET https://www.example.com/sdi/rpc/notifications/id
GET https://www.example.com/sdi/rpc/invoices?status=E_ACCEPTED
GET https://www.example.com/td0000002/rpc/invoices?status=I_ACCEPTED (no response yet)
```

13. force ES to dispatch back the acceptance notification to the issuer:
```
POST https://www.example.com/sdi/rpc/dispatch
```

14. check notification and status:
```
GET https://www.example.com/td0000001/rpc/notifications/id
GET https://www.example.com/td0000002/rpc/invoices?status=I_ACCEPTED
```

## Testing

### Manual testing

You can test manually by [making SOAP requests using Postman](http://blog.getpostman.com/2014/08/22/making-soap-requests-using-postman/).

You can import this collection into Postman, to test the `AttestazioneTrasmissioneFattura` operation of the `TrasmissioneFatture` web service (change the url to match that of your test server !):
```
{
  "variables": [],
  "info": {
    "name": "SOAP",
    "_postman_id": "0ee991f3-5203-a8ac-6b38-32c8bfabc05e",
    "description": "",
    "schema": "https://schema.getpostman.com/json/collection/v2.0.0/collection.json"
  },
  "item": [
    {
      "name": "SDICoop Transmit / TrasmissioneFatture service, AttestazioneTrasmissioneFattura operation",
      "request": {
        "url": "http://testsdi.simevo.com/td0000001/soap/TrasmissioneFatture/",
        "method": "POST",
        "header": [
          {
            "key": "Content-Type",
            "value": "text/xml;charset=\"utf-8\"",
            "description": ""
          },
          {
            "key": "SOAPAction",
            "value": "http://www.fatturapa.it/TrasmissioneFatture/AttestazioneTrasmissioneFattura",
            "description": ""
          }
        ],
        "body": {
          "mode": "raw",
          "raw": "<?xml version=\"1.0\" encoding=\"UTF-8\"?>\n<SOAP-ENV:Envelope\n  xmlns:SOAP-ENV=\"http://schemas.xmlsoap.org/soap/envelope/\"\n  xmlns:ns1=\"http://www.fatturapa.gov.it/sdi/ws/trasmissione/v1.0/types\">\n\t<SOAP-ENV:Body>\n\t\t<ns1:attestazioneTrasmissioneFattura>\n\t\t\t<IdentificativoSdI>104</IdentificativoSdI>\n\t\t\t<NomeFile>IT01234567890_11111_AT_001.xml</NomeFile>\n\t\t\t<File>UEQ5NGJXd2dkbVZ5YzJsdmJqMGlNUzR3SWlCbGJtTnZaR2x1WnowaVZWUkdMVGdpUHo0S1BEOTRiV3d0YzNSNWJHVnphR1ZsZENCMGVYQmxQU0owWlhoMEwzaHpiQ0lnYUhKbFpqMGlRVlJmZGpFdU1TNTRjMndpUHo0S1BIUjVjR1Z6T2tGMGRHVnpkR0Y2YVc5dVpWUnlZWE50YVhOemFXOXVaVVpoZEhSMWNtRWdlRzFzYm5NNmRIbHdaWE05SW1oMGRIQTZMeTkzZDNjdVptRjBkSFZ5WVhCaExtZHZkaTVwZEM5elpHa3ZiV1Z6YzJGbloya3ZkakV1TUNJZ2VHMXNibk02ZUhOcFBTSm9kSFJ3T2k4dmQzZDNMbmN6TG05eVp5OHlNREF4TDFoTlRGTmphR1Z0WVMxcGJuTjBZVzVqWlNJZ2RtVnljMmx2Ym1VOUlqRXVNQ0lnZUhOcE9uTmphR1Z0WVV4dlkyRjBhVzl1UFNKb2RIUndPaTh2ZDNkM0xtWmhkSFIxY21Gd1lTNW5iM1l1YVhRdmMyUnBMMjFsYzNOaFoyZHBMM1l4TGpBZ1RXVnpjMkZuWjJsVWVYQmxjMTkyTVM0eExuaHpaQ0FpUGdvOFNXUmxiblJwWm1sallYUnBkbTlUWkVrK01qRTBQQzlKWkdWdWRHbG1hV05oZEdsMmIxTmtTVDRLUEU1dmJXVkdhV3hsUGtsVU1ERXlNelExTmpjNE9UQmZNVEV4TVRFdWVHMXNMbkEzYlR3dlRtOXRaVVpwYkdVK0NqeEVZWFJoVDNKaFVtbGpaWHBwYjI1bFBqSXdNVFF0TURRdE1ERlVNVEk2TURBNk1EQThMMFJoZEdGUGNtRlNhV05sZW1sdmJtVStDanhFWlhOMGFXNWhkR0Z5YVc4K0NpQWdJQ0E4UTI5a2FXTmxQa0ZCUVVGQlFUd3ZRMjlrYVdObFBnb2dJQ0FnUEVSbGMyTnlhWHBwYjI1bFBsQjFZbUpzYVdOaElFRnRiV2x1YVhOMGNtRjZhVzl1WlNCa2FTQndjbTkyWVR3dlJHVnpZM0pwZW1sdmJtVStDand2UkdWemRHbHVZWFJoY21sdlBnbzhUV1Z6YzJGblpVbGtQakV5TXpRMU5qd3ZUV1Z6YzJGblpVbGtQZ284VG05MFpUNUJkSFJsYzNSaGVtbHZibVVnVkhKaGMyMXBjM05wYjI1bElFWmhkSFIxY21FZ1pHa2djSEp2ZG1FOEwwNXZkR1UrQ2p4SVlYTm9SbWxzWlU5eWFXZHBibUZzWlQ0eVl6Rm1NMkV5TkRCaE1EVTJaRGsxTXpkaE9EWXdPR1psWkRNeE1EZ3hNbVZtTjJJeFlqZGhOREV3WkRBeE5USm1OV001WXpsbE9UTTBPRFpoWlRRMFBDOUlZWE5vUm1sc1pVOXlhV2RwYm1Gc1pUNEtQQzkwZVhCbGN6cEJkSFJsYzNSaGVtbHZibVZVY21GemJXbHpjMmx2Ym1WR1lYUjBkWEpoUGc9PQ==</File>\n\t\t</ns1:attestazioneTrasmissioneFattura>\n\t</SOAP-ENV:Body>\n</SOAP-ENV:Envelope>"
        },
        "description": ""
      },
      "response": []
    }
  ]
}
```

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

## Troubleshooting

SOAP client/server interactions can be tricky to debug.

The issue is even more complicated when you perform a RPC calls (such as `POST /sdi/rpc/transmit`) which has to perform internally a SOAP call.

You'll then have: client -> 1st level server (RPC) | SOAP client -> SOAP server | 2nd level server.

It is easy to trace and debug the 1st level server:
- PHP `echo` is sent back to client
- PHP `error_log` statements get written to `/var/log/nginx/error.log`.

For the 2nd level server it's more complicated:
- you cannot use PHP `echo` because the body is used to return XML payloads
- PHP `error_log` statements get lost.

To make sure you get to see all messages written to log at the 2nd level server (SOAP server) edit `/etc/php/7.0/fpm/pool.d/www.conf` adding these lines at the end:
```
catch_workers_output = yes
php_flag[display_errors] = on
php_admin_value[error_log] = /var/log/fpm-php.www.log
php_admin_flag[log_errors] = on
```
then create the new log file and make sure it can be written by the webserver:
```sh
sudo touch /var/log/fpm-php.www.log
sudo chown www-data:www-data /var/log/fpm-php.www.log
```
and finally restart the servers:
```sh
systemctl restart nginx && systemctl restart php7.0-fpm
```

Another option you have is to use instrumented versions of the PHP `SoapClient` and `SoapServer` builtins.

To instrument a SOAP client, use `SoapClientDebug` instead of `SoapClient`, for example for `TrasmissioneFatture` add this to `soap/TrasmissioneFatture/autoload.php`:
```php
'SoapClientDebug' => __DIR__ .'/../SoapClientDebug.php',
```
then modify `TrasmissioneFatture/TrasmissioneFatture_service.php` like this:
```diff
- class TrasmissioneFatture_service extends \SoapClient
+ class TrasmissioneFatture_service extends \SoapClientDebug
```

To instrument a SOAP server, use `SoapServerDebug` instead of `SoapServer`, for example for `TrasmissioneFatture` make sure `soap/index.php` has:
```php
require_once("SoapServerDebug.php");
```
then modify `TrasmissioneFatture/index.php` like this:
```diff
-$srv = new \SoapServer(dirname(__FILE__) . '/TrasmissioneFatture_v1.1.wsdl');
+$srv = new SoapServerDebug(dirname(__FILE__) . '/TrasmissioneFatture_v1.1.wsdl');
 $srv->setClass("TrasmissioneFattureHandler");
 $srv->handle();
+foreach ($srv->getAllDebugValues() as $value) {
+    error_log('==== '. print_r($value, true));
+}
```

## Contributing

For your contributions please use the [git-flow workflow](https://danielkummer.github.io/git-flow-cheatsheet/).

## Authors

Emanuele Aina, Riccardo Mariani, Marco Peca and Paolo Greppi.

## License

Copyright (c) 2018, simevo s.r.l.

License: AGPL 3, see [LICENSE](LICENSE) file.
