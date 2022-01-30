Negotiation
===========

[![GitHub Actions](https://github.com/willdurand/Negotiation/workflows/ci/badge.svg)](https://github.com/willdurand/Negotiation/actions?query=workflow%3A%22ci%22+branch%3Amaster)
[![Total
Downloads](https://poser.pugx.org/willdurand/Negotiation/downloads.png)](https://packagist.org/packages/willdurand/Negotiation)
[![Latest Stable
Version](https://poser.pugx.org/willdurand/Negotiation/v/stable.png)](https://packagist.org/packages/willdurand/Negotiation)

**Negotiation** is a standalone library without any dependencies that allows you
to implement [content
negotiation](https://tools.ietf.org/html/rfc7231#section-5.3) in your
application, whatever framework you use.  This library is based on [RFC
7231](https://tools.ietf.org/html/rfc7231). Negotiation is easy to use, and
extensively unit tested!

> **Important:** You are browsing the documentation of Negotiation **3.x**+.
>
> Documentation for version **1.x** is available here: [Negotiation 1.x
> documentation](https://github.com/willdurand/Negotiation/blob/1.x/README.md#usage).
>
> Documentation for version **2.x** is available here: [Negotiation 2.x
> documentation](https://github.com/willdurand/Negotiation/blob/2.x/README.md#usage).


Installation
------------

The recommended way to install Negotiation is through
[Composer](http://getcomposer.org/):

```bash
$ composer require willdurand/negotiation
```


Usage Examples
--------------

### Media Type Negotiation

``` php
$negotiator = new \Negotiation\Negotiator();

$acceptHeader = 'text/html, application/xhtml+xml, application/xml;q=0.9, */*;q=0.8';
$priorities   = array('text/html; charset=UTF-8', 'application/json', 'application/xml;q=0.5');

$mediaType = $negotiator->getBest($acceptHeader, $priorities);

$value = $mediaType->getValue();
// $value == 'text/html; charset=UTF-8'
```

The `Negotiator` returns an instance of `Accept`, or `null` if negotiating the
best media type has failed.

### Language Negotiation

``` php
<?php

$negotiator = new \Negotiation\LanguageNegotiator();

$acceptLanguageHeader = 'en; q=0.1, fr; q=0.4, fu; q=0.9, de; q=0.2';
$priorities          = array('de', 'fu', 'en');

$bestLanguage = $negotiator->getBest($acceptLanguageHeader, $priorities);

$type = $bestLanguage->getType();
// $type == 'fu';

$quality = $bestLanguage->getQuality();
// $quality == 0.9
```

The `LanguageNegotiator` returns an instance of `AcceptLanguage`.

### Encoding Negotiation

``` php
<?php

$negotiator = new \Negotiation\EncodingNegotiator();
$encoding   = $negotiator->getBest($acceptHeader, $priorities);
```

The `EncodingNegotiator` returns an instance of `AcceptEncoding`.

### Charset Negotiation

``` php
<?php

$negotiator = new \Negotiation\CharsetNegotiator();

$acceptCharsetHeader = 'ISO-8859-1, UTF-8; q=0.9';
$priorities          = array('iso-8859-1;q=0.3', 'utf-8;q=0.9', 'utf-16;q=1.0');

$bestCharset = $negotiator->getBest($acceptCharsetHeader, $priorities);

$type = $bestCharset->getType();
// $type == 'utf-8';

$quality = $bestCharset->getQuality();
// $quality == 0.81
```

The `CharsetNegotiator` returns an instance of `AcceptCharset`.

### `Accept*` Classes

`Accept` and `Accept*` classes share common methods such as:

* `getValue()` returns the accept value (e.g. `text/html; z=y; a=b; c=d`)
* `getNormalizedValue()` returns the value with parameters sorted (e.g.
  `text/html; a=b; c=d; z=y`)
* `getQuality()` returns the quality if available (`q` parameter)
* `getType()` returns the accept type (e.g. `text/html`)
* `getParameters()` returns the set of parameters (excluding the `q` parameter
  if provided)
* `getParameter()` allows to retrieve a given parameter by its name. Fallback to
  a `$default` (nullable) value otherwise.
* `hasParameter()` indicates whether a parameter exists.


Versioning
----------

Negotiation follows [Semantic Versioning](http://semver.org/).

### End Of Life

#### 1.x

As of October 2016, [branch
`1.x`](https://github.com/willdurand/Negotiation/tree/1.x) is not supported
anymore, meaning major version `1` reached end of life. Last version is:
[1.5.0](https://github.com/willdurand/Negotiation/releases/tag/1.5.0).

#### 2.x

As of November 2020, [branch
`2.x`](https://github.com/willdurand/Negotiation/tree/2.x) is not supported
anymore, meaning major version `2` reached end of life. Last version is:
[2.3.1](https://github.com/willdurand/Negotiation/releases/tag/v2.3.1).

### Stable Version

#### 3.x (and `dev-master`)

Negotiation [3.0](https://github.com/willdurand/Negotiation/releases/tag/3.0.0)
has been released on November 26th, 2020. This is the **current stable version**
and it is in sync with the main branch (a.k.a. `master`).

Unit Tests
----------

Setup the test suite using Composer:

    $ composer install --dev

Run it using PHPUnit:

    $ phpunit


Contributing
------------

See [CONTRIBUTING](CONTRIBUTING.md) file.


Credits
-------

* Some parts of this library are inspired by:

    * [Symfony](http://github.com/symfony/symfony) framework;
    * [FOSRest](http://github.com/FriendsOfSymfony/FOSRest);
    * [PEAR HTTP2](https://github.com/pear/HTTP2).

* [William Durand](https://github.com/willdurand)
* [@neural-wetware](https://github.com/neural-wetware)


License
-------

Negotiation is released under the MIT License. See the bundled LICENSE file for
details.
