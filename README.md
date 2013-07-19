Negotiation
===========

[![Build Status](https://travis-ci.org/willdurand/Negotiation.png?branch=master)](http://travis-ci.org/willdurand/Negotiation)

Yet another missing PHP library... about Content Negotiation!
**Negotiation** is a standalone library without any dependencies that allows you
to implement [content
negotiation](http://www.w3.org/Protocols/rfc2616/rfc2616-sec12.html) in your
application, whatever framework you use.


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


Usage
-----

``` php
<?php

$negotiator = new \Negotiation\Negotiator();

$bestHeader = $negotiator->getBest('en; q=0.1, fr; q=0.4, fu; q=0.9, de; q=0.2');
// $bestHeader = 'fu';
```

The `getBest()` method, part of the `NegotiatorInterface`, returns either `null`
or `AcceptHeader` instances.


### Charset Negotiation

Charset negotiation works out of the box using the `Negotiator` class:

``` php
<?php

$negotiator = new \Negotiation\Negotiator();
$priorities = array(
    'utf-8',
    'big5',
    'shift-jis',
);

$bestHeader = $negotiator->getBest('ISO-8859-1, Big5;q=0.6,utf-8;q=0.7, *;q=0.5', $priorities);
// $bestHeader = 'utf-8'
```


### Format Negotiation

Basically, you can call the `getBest()` method in order to retrieve the best
**mime type**:

``` php
<?php

$negotiator   = new \Negotiation\FormatNegotiator();

$acceptHeader = 'text/html,application/xhtml+xml,application/xml;q=0.9,*/*;q=0.8';
$priorities   = array('text/html', 'application/json', '*/*');

$format = $negotiator->getBest($acceptHeader, $priorities);
// $format->getValue() = text/html
```

The `FormatNegotiator` class also provides a `getBestFormat()` method that
returns the best format given an accept header string, and a set of
preferred/allowed formats:

``` php
<?php

$negotiator   = new \Negotiation\FormatNegotiator();

$acceptHeader = 'text/html,application/xhtml+xml,application/xml;q=0.9,*/*;q=0.8';
$priorities   = array('html', 'json', '*/*');

$format = $negotiator->getBestFormat($acceptHeader, $priorities);
// $format = html
```


Unit Tests
----------

Setup the test suite using Composer:

    $ composer install --dev

Run it using PHPUnit:

    $ phpunit


Contributing
------------

See CONTRIBUTING file.


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
