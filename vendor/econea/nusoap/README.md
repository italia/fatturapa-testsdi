# NuSOAP for PHP 5.4 - 7.1

Fork of NuSOAP fixed for PHP 5.4, 5.5, 5.6, 7.0 and 7.1 (tested).

All credits belongs to official author(s): http://nusoap.sourceforge.net.
----


[![Build Status](https://img.shields.io/travis/econea/nusoap.svg?style=flat-square)](https://travis-ci.org/econea/nusoap)
[![Code coverage](https://img.shields.io/coveralls/econea/nusoap.svg?style=flat-square)](https://coveralls.io/r/econea/nusoap)
[![Licence](https://img.shields.io/packagist/l/econea/nusoap.svg?style=flat-square)](https://packagist.org/packages/econea/nusoap)

[![Downloads this Month](https://img.shields.io/packagist/dm/econea/nusoap.svg?style=flat-square)](https://packagist.org/packages/econea/nusoap)
[![Downloads total](https://img.shields.io/packagist/dt/econea/nusoap.svg?style=flat-square)](https://packagist.org/packages/econea/nusoap)
[![Latest stable](https://img.shields.io/packagist/v/econea/nusoap.svg?style=flat-square)](https://packagist.org/packages/econea/nusoap)

## Discussion / Help

[![Join the chat](https://img.shields.io/gitter/room/econea/econea.svg?style=flat-square)](http://bit.ly/ecogitter)

## Install

### `Stable`

Solid rock version is `~0.9.5.x`.

```sh
composer require econea/nusoap: ~0.9.5.1
```

### `Next`

Just bug fixing for `>=0.9.6`. Update your composer.json.

```json
{
  "minimum-stability": "dev",
  "prefer-stable": true
}
```

And install next version.

```sh
composer require econea/nusoap: ^0.9.6
```

### `Development`

Total refactored version (split into more files, CI, etc).

```sh
composer require econea/nusoap: dev-develop
```

## Version

| State       | Version       | Branch    | PHP      | Composer                                        |
|-------------|---------------|-----------|----------|-------------------------------------------------|
| development | `dev-develop` | `develop` | `>= 5.6` |                                                 |
| next        | `^0.9.6`      | `0.9.x`   | `>= 5.4` |                                                 |
| stable      | `~0.9.5.1`    | `master`  | `>= 5.4` |                                                 |

## Usage

```php
// Config
$client = new nusoap_client('example.com/api/v1', 'wsdl');
$client->soap_defencoding = 'UTF-8';
$client->decode_utf8 = FALSE;

// Calls
$result = $client->call($action, $data);
```

## Experimental

Take a look at `develop` branch. There will be new features and modernizations.

Minimal version is set to PHP 5.6.

```sh
composer require econea/nusoap:dev-master
```

## Maintainers

<table>
  <tbody>
    <tr>
      <td align="center">
        <a href="https://github.com/f3l1x">
            <img width="150" height="150" src="https://avatars2.githubusercontent.com/u/538058?v=3&s=150">
        </a>
        </br>
        <a href="https://github.com/f3l1x">Milan Felix Šulc</a>
      </td>
    </tr>
  <tbody>
</table>

-----

Thank you for testing, reporting and contributing.
