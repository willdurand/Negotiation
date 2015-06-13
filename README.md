Negotiation
===========

[![Build Status](https://travis-ci.org/willdurand/Negotiation.png?branch=master)](http://travis-ci.org/willdurand/Negotiation)
[![Total Downloads](https://poser.pugx.org/willdurand/Negotiation/downloads.png)](https://packagist.org/packages/willdurand/Negotiation)
[![Latest Stable Version](https://poser.pugx.org/willdurand/Negotiation/v/stable.png)](https://packagist.org/packages/willdurand/Negotiation)

**Negotiation** is a standalone library without any dependencies that allows you
to implement [content negotiation](https://tools.ietf.org/html/rfc7231#section-5.3) in your application, whatever framework you use.
This library is based on [RFC 7231](https://tools.ietf.org/html/rfc7231). Negotiation is easy to use, and extensively unit tested.

TODO link to version 1

Installation
------------

The recommended way to install Negotiation is through
[Composer](http://getcomposer.org/):

``` json
{
    "require": {
        "willdurand/negotiation": "@stable"
    }
}
```

**Protip:** you should browse the
[`willdurand/negotiation`](https://packagist.org/packages/willdurand/negotiation)
page to choose a stable version to use, avoid the `@stable` meta constraint.


Usage Examples
--------------

Language negotiation:

``` php
<?php

$negotiator = new \Negotiation\LanguageNegotiator();
$priorities = array('de', 'fu', 'en');
$acceptLangageHeader = 'en; q=0.1, fr; q=0.4, fu; q=0.9, de; q=0.2';
$best = $negotiator->getBest($acceptLangageHeader, $priorities);

$type = $best->getType();
// $type == 'fu';
```

Media Type negotiation:

``` php
$negotiator = new \Negotiation\Negotiator();
$acceptHeader = 'text/html, application/xhtml+xml, application/xml;q=0.9, */*;q=0.8';
$priorities   = array('text/html; charset=UTF-8', 'application/json');

$mediaType = $negotiator->getBest($acceptHeader, $priorities);

$value = $mediaType->getValue();
// $value == 'text/html; charset=UTF-8'
```

The `getBest()` method, part of the `AbstractNegotiator` class, returns the best matching priority (`AcceptLanguage` instance) or `null` when no match is made.


### Class Hierarchy

  * `BaseAccept`

    - `Accept`
    - `AcceptLanguage`
    - `AcceptCharset`
    - `AcceptEncoding`


  * `AbstractNegotiator`

    - `Negotiator`
    - `LanguageNegotiator`
    - `CharsetNegotiator`
    - `EncodingNegotiator`


  * `Match`

TODO document methods


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

* William Durand <william.durand1@gmail.com>


License
-------

Negotiation is released under the MIT License. See the bundled LICENSE file for details.
