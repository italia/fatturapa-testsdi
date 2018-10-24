# fatturapa-control

This repository contains a **Laravel package** that can be used to control the test environment for the Exchange System (ES, Italian: **SDI**) for Electronic Invoices, via a Remote Procedure Call (RPC) API over the HTTP protocol.

Use cases:
- perform manual simulations
- run automated tests
- display status information in User Interfaces.

To perform the actual operations, it relies on **fatturapa-core**, a **composer package** with the core classes required to interact with the Exchange System.

For more details, refer to [fatturapa-testsdi](https://github.com/italia/fatturapa-testsdi), the test environment for the Italian B2B invoice system.

## API documentation

- general simulation control:
  - `POST /clear`: resets the state
  - `GET /datetime`: retrieves current simulated date and time
  - `POST /timestamp/ timetamp: 2018-09-15T23:59Z`: tweak the timestamp of all following events
  - `POST /speed/10`: set factor for simulated time to wallclock time

- notifications:
  - `GET /notifications/id`: retrieves notification with id
  - `POST /dispatch`: attempt dispatching for all N_PENDING notifications, on success makes the notification N_DELIVERED

- invoices:
  - `GET /invoices?status=R_ACCEPTED`: retrieves array of ids in R_ACCEPTED status
  - `GET /invoices/id`: retrieves invoice with id

- issuer-specific:
  - `POST /upload {file XML}` call `Issuer::upload(XML)`
  - `POST /transmit`: call `Issuer::transmit`

- exchange-specific
  - `POST /checkValidity`: call `Exchange::checkValidity`
  - `POST /deliver`: call `Exchange::deliver`
  - `POST /checkExpiration`: call `Exchange::checkExpiration`
  - `GET /actors`: calls `Base::getActors`
  - `GET /issuers`: calls `Base::getIssuers`

- recipient-specific
  - `POST /accept {invoices}`: call `Recipient::accept(invoices)`
  - `POST /refuse {invoices}`: call `Recipient::refuse(invoices)`

## Example

```
// get list of actors
GET sdi/rpc/actors

// reset state for all actors
POST sdi/rpc/clear
POST td0000001/rpc/clear
POST td0000002/rpc/clear

// upload invoice to issuer
POST td0000001/rpc/upload {name: 'File', contents: '<xml></xml>', filename: 'aaa.xml'}

// force issuer to attempt transmission to ES
POST td0000001/rpc/transmit

// force ES to check validity of received invoices
POST sdi/rpc/checkValidity

// force ES to deliver the invoice to the recipient
POST sdi/rpc/deliver

// force ES to notify back to the issuer the delivery
POST sdi/rpc/dispatch
```

## Contributing

For your contributions please use the [git-flow workflow](https://danielkummer.github.io/git-flow-cheatsheet/).

## Authors

Emanuele Aina, Paolo Greppi

## License

Copyright (c) 2018, simevo s.r.l.

License: AGPL 3, see [LICENSE](LICENSE) file.
