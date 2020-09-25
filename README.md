Negotiation
===========

[![Build
Status](https://travis-ci.org/willdurand/Negotiation.svg?branch=master)](http://travis-ci.org/willdurand/Negotiation)
[![Build
status](https://ci.appveyor.com/api/projects/status/6tbe8j3gofdlfm4v?svg=true)](https://ci.appveyor.com/project/willdurand/negotiation)
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

> **Important:** You are browsing the documentation of Negotiation **2.x**+.
Documentation for version **1.x** is available here: [Negotiation 1.x
documentation](https://github.com/willdurand/Negotiation/blob/1.x/README.md#usage).
You might also be interested in this: [**What's new in Negotiation 2?**](https://github.com/willdurand/Negotiation/releases/tag/v2.0.0-alpha1)


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

### Stable Version

#### 2.x

Negotiation [2.0](https://github.com/willdurand/Negotiation/releases/tag/v2.0.0)
has been released on October 1st, 2015. It is the **current stable version**.
The [`2.x` branch](https://github.com/willdurand/Negotiation/tree/2.x) is used
to maintain this version.

### `dev-master`

#### 3.x

Version `3.x` is the next major version of Negotiation. This version lives in
the `master` branch, and should not be used in production yet (even if we try
to keep its state as stable as we can).


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

* William Durand <will+git@drnd.me>
* [@neural-wetware](https://github.com/neural-wetware)


License
-------

Negotiation is released under the MIT License. See the bundled LICENSE file for
details.
