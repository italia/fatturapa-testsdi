# fatturapa-core

This repository contains a **composer package** with the core classes required to interact with the Exchange System (ES, Italian: **SDI**) for Electronic Invoices:
- state machine abstraction
- state persistency to database for invoices and notifications
- Actor classes:
  - `Base`: features common to all actors
  - `Issuer`: the actor which issues the invoice, Italian: trasmittente
  - `Exchange`: the Exchange System (ES), Italian: Sistema di Interscambio (SDI)
  - `Recipient`: the actor which receives the invoice, Italian: destinatario
- Model classes: `Invoice` and `Notification`

It relies on:
- [Illuminate Database](https://github.com/illuminate/database), a database toolkit for PHP with query builder, ORM, and schema builder, which is also used by [Laravel](https://laravel.com/)
- PHP's [SoapClient](http://php.net/manual/en/class.soapclient.php), the built-in client for SOAP WebServices.

For more details, refer to [fatturapa-testsdi](https://github.com/italia/fatturapa-testsdi), the test environment for the Italian B2B invoice system.

## Actor classes documentation

All `core` classes have a common `Base` class with:
- `Base::clear`: resets the state, called by `POST /clear`
- `Base::setDateTime(datetime)`: used to tweak the timestamp of all following events, called by `POST /timestamp/ timestamp: 2018-09-15T23:59Z`
- `Base::setSpeed(multiplier)`: between calls to `setTimeStamp`, time will flow at 1 x factor w.r.t. to wallclock time, called by `POST /speed/10`
- `Base::getDateTime`: retrieves current simulated date and time, called by `GET /datetime`
- SDICoop Transmit / TrasmissioneFatture notifications servers are implemented calling:
  - `Base::receive(invoice, type, notification_blob)` stores a N_RECEIVED inbound notification for the invoice+type, including the notification payload as binary blob
- all `Exchange` and `Recipient` methods that must notify call:
  - `Base::enqueue(invoice_id, type, notification_blob)` creates a N_PENDING outbound notification for the invoice_id+type and makes any previous N_PENDING record for the same invoice_id N_OBSOLETE
- `Base::dispatch` will attempt dispatching calling `SoapAdaptor::notify(type, invoice_blob, notification_blob)` for all N_PENDING notifications, on success makes the notification N_DELIVERED; called by `POST /dispatch`

The `Issuer` class is used to implement the **issuer** actor and has:
- `Issuer::Issuer(db)`: instantiate and point to a dedicated database instance
- `Issuer::upload(XML)` send to I_UPLOADED, called by `POST /upload {file XML}`
- `Issuer::transmit` will attempt transmission calling the `SoapAdaptor::transmit(invoice_blob)` for all invoices in I_UPLOADED status, on success they are moved to I_TRANSMITTED; called by `POST /transmit`
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
- `Exchange::checkValidity` will perform checks for all invoices in E_RECEIVED status and move them to E_INVALID or E_VALID, called by `POST /checkValidity`
- `Exchange::deliver`, called by `POST /deliver`; it will:
  - move all invoices that have been `E_VALID` for longer than 48 hours to `E_FAILED_DELIVERY`
  - move all invoices that have been in `E_FAILED_DELIVERY` for longer than 10 days to `E_IMPOSSIBLE_DELIVERY`
  - finally, for all invoices that are still in `E_VALID` or `E_FAILED_DELIVERY` it will attempt delivery to the recipient specified in the `FatturaElettronica.FatturaElettronicaHeader.DatiTrasmissione.CodiceDestinatario` field of the invoice XML with SDICoop Receive RicezioneFatture; on success they are moved to `E_DELIVERED`, on error nothing happens
- `Exchange::checkExpiration`: will move all invoices that have been E_DELIVERED for longer than 15 days to the E_EXPIRED, called by `POST /checkExpiration`
- `Exchange::accept(invoices)` send to E_ACCEPTED, called by SDICoop – Ricezione / SdiRiceviNotifica.NotificaEsito
- `Exchange::refuse(invoices)` send to E_REFUSED, called by SDICoop – Ricezione / SdiRiceviNotifica.NotificaEsito

The `Recipient` class is used to implement the **recipent** actor and has:
- `Recipient::Recipient(db)`: instantiate and point to a dedicated database instance
- `Recipient::receive(XML, metadata)` send to R_RECEIVED, called by SDICoop Ricezione / RicezioneFatture.RiceviFatture server implementation
- `Recipient::accept(invoices)`: move to R_ACCEPTED; called by `POST /accept {invoices}`
- `Recipient::refuse(invoices)`: move to R_REFUSED; called by `POST /refuse {invoices}`
- `Recipient::expire(invoices)` send to R_EXPIRED, called by SDICoop – Ricezione / RicezioneFatture.NotificaDecorrenzaTermini server implementation

## Getting Started

To use this package, you first need to set up the database.

This is best done using the database migrations found in the **fatturapa-control** Laravel package from [fatturapa-testsdi](https://github.com/italia/fatturapa-testsdi).

### Example

```php
require 'core/vendor/autoload.php';

use FatturaPa\Core\Actors\Base;
use FatturaPa\Core\Actors\Exchange;
use FatturaPa\Core\Actors\Issuer;
use FatturaPa\Core\Actors\Recipient;

// reset the state
Base::clear();
// perform checks for all invoices in E_RECEIVED state and move them to either E_INVALID or E_VALID
Exchange::checkValidity();
// refuse invoices 42 and 84
Recipient::refuse([42, 84]);
// store that invoices 42 and 84 have been refused by the recipient
Issuer::refused([42, 84]);
```

## Contributing

For your contributions please use the [git-flow workflow](https://danielkummer.github.io/git-flow-cheatsheet/).

## Authors

Paolo Greppi

## License

Copyright (c) 2018, simevo s.r.l.

License: AGPL 3, see [LICENSE](LICENSE) file.
