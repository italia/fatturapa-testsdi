# fatturapa-testui

Testing UI for [fatturapa-testsdi](https://github.com/italia/fatturapa-testsdi).

With the **testui** you can connect to a test environment for the Exchange System (ES, Italian: SDI) for Electronic Invoices, and manually run a simulation of the complete process of invoice issue, transmission and receipt.

## Design

The UI reads and writes to the invoice and notification queues using the **testsdi** RPC interface.

The UI will have a general controls tab where you can:
- clear (reset the state of all actors)
- set the current time
- set factor for simulated time to wallclock time

Additionally there will be one tab for the exchange system, and one for each issuer/receiver.
The issuer/receiver tab will have separate sub-tabs for the two roles.

Each tab or sub-tab will show all queues for the role, and present buttons to trigger events (i.e. upload invoice for the issuer, accept / refuse invoice for the receiver etc).

**Exchange system, ES** (sistema di interscambio, SDI) view mock-up:

![SDI](/mockups/SDI.JPG)

**Issuer** (trasmittente) view mock-up:

![SDI](/mockups/TD_emissione.JPG)

**Recipent** (destinatario) view mock-up:

![SDI](/mockups/TD_ricezione.JPG)

## Getting Started

### Prerequisites

TODO

### Configuring and Installing

TODO

### Demo

TODO

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

Copyright (c) 2018, XXX

License: AGPL 3, see [LICENSE](LICENSE) file.
