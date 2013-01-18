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

The recommended way to install Negotiation is through composer:

``` json
{
    "require": {
        "willdurand/negotiation": "*"
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

### Format Negotiation

``` php
<?php

$negotiator   = new \Negotiation\FormatNegotiator();

$acceptHeader = 'text/html,application/xhtml+xml,application/xml;q=0.9,*/*;q=0.8';
$priorities   = array('html', 'json', '*/*');

$format = $negotiator->getBest($acceptHeader, $priorities);
// $format = html
```


Unit Tests
----------

Setup the test suite using Composer:

    $ composer install --dev

And, run it with PHPUnit:

    $ phpunit


Credits
-------

* Some parts of this library come from the
[Symfony](http://github.com/symfony/symfony) framework and
[FOSRest](http://github.com/FriendsOfSymfony/FOSRest).

* William Durand <william.durand1@gmail.com>


License
-------

Negotiation is released under the MIT License. See the bundled LICENSE file for details.
