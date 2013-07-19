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

The `getBest()` method, part of the `NegotiatorInterface` returns either `null`
or `AcceptHeader` instances.


### Format Negotiation

``` php
<?php

$negotiator   = new \Negotiation\FormatNegotiator();

$acceptHeader = 'text/html,application/xhtml+xml,application/xml;q=0.9,*/*;q=0.8';
$priorities   = array('text/html', 'application/json', '*/*');

$format = $negotiator->getBest($acceptHeader, $priorities);
// $format->getValue() = text/html
```


Unit Tests
----------

Setup the test suite using Composer:

    $ composer install --dev

And, run it with PHPUnit:

    $ phpunit


Contributing
------------

See CONTRIBUTING file.


Credits
-------

* Some parts of this library come from the

    * [Symfony](http://github.com/symfony/symfony) framework;
    * [FOSRest](http://github.com/FriendsOfSymfony/FOSRest);
    * [PEAR HTTP2](https://github.com/pear/HTTP2).

* William Durand <william.durand1@gmail.com>


License
-------

Negotiation is released under the MIT License. See the bundled LICENSE file for details.
