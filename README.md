Negotiation
===========

[![Build Status](https://travis-ci.org/willdurand/Negotiation.png?branch=master)](http://travis-ci.org/willdurand/Negotiation)
[![Total Downloads](https://poser.pugx.org/willdurand/Negotiation/downloads.png)](https://packagist.org/packages/willdurand/Negotiation)
[![Latest Stable Version](https://poser.pugx.org/willdurand/Negotiation/v/stable.png)](https://packagist.org/packages/willdurand/Negotiation)

**Negotiation** is a standalone library without any dependencies that allows you
to implement [content
negotiation](http://www.w3.org/Protocols/rfc2616/rfc2616-sec12.html) in your
application, whatever framework you use.
This library is based on [RFC
2616](http://www.w3.org/Protocols/rfc2616/rfc2616-sec14.html). Negotiation is
easy to use, and extensively unit tested.


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


Usage
-----

In a nutshell:

``` php
<?php

$negotiator = new \Negotiation\Negotiator();
$bestHeader = $negotiator->getBest('en; q=0.1, fr; q=0.4, fu; q=0.9, de; q=0.2');
// $bestHeader = 'fu';
```

The `getBest()` method, part of the `NegotiatorInterface`, returns either `null`
or `AcceptHeader` instances. An `AcceptHeader` object owns a `value` and a
`quality`.


### Format Negotiation

The **Format Negotiation** is handled by the `FormatNegotiator` class.
Basically, pass an `Accept` header and optionally a set of preferred media types
to the `getBest()` method in order to retrieve the best **media type**:

``` php
<?php

$negotiator   = new \Negotiation\FormatNegotiator();

$acceptHeader = 'text/html,application/xhtml+xml,application/xml;q=0.9,*/*;q=0.8';
$priorities   = array('text/html', 'application/json', '*/*');

$format = $negotiator->getBest($acceptHeader, $priorities);
// $format->getValue() = text/html
```

The `FormatNegotiator` class also provides a `getBestFormat()` method that
returns the best format given an `Accept` header string and a set of
preferred/allowed formats or mime types:

``` php
<?php

$negotiator   = new \Negotiation\FormatNegotiator();

$acceptHeader = 'text/html,application/xhtml+xml,application/xml;q=0.9,*/*;q=0.8';
$priorities   = array('html', 'application/json', '*/*');

$format = $negotiator->getBestFormat($acceptHeader, $priorities);
// $format = html
```

#### Other Methods

* `registerFormat($format, array $mimeTypes, $override = false)`: registers a new
  format with its mime types;
* `getFormat($mimeType)`: returns the format for a given mime type, or null if
not found;
* `getMimeTypes(array $formats)`: returns an array of mime types for the given
  set of formats;
* `normalizePriorities($priorities)`: ensures that any formats are converted to
  mime types.

### API Format Negotiation

This negotiation takes possible version preferences of the client into account.
The priorities can be used to specify which particular media/types are supported
in which API versions:

``` php
<?php

$negotiator   = new \Negotiation\ApiFormatNegotiator();

$acceptHeader = 'application/xml;version=1,application/json;version=2';
$priorities   = array('application/json', 'application/xml');

$format = $negotiator->getBest($acceptHeader, $priorities);
// $format->getValue() = application/xml
// $format->getVersion() = 1
```

A more realistic scenario would be:

``` php
<?php

$negotiator   = new \Negotiation\ApiFormatNegotiator();

// Client wants to use version 1.0, no matter the response format
$acceptHeader =  'application/json;version=1.0,application/xml;version=1.0';
$priorities   = array(
    'application/json' => array('1.0', '1.1', '1.2', '1.3', '2.0'),
    'application/xml' => array('1.0', '1.1', '1.2', '1.3', '2.0')
);

$format = $negotiator->getBest($acceptHeader, $priorities);
// $format->getValue() = application/json
// $format->getVersion() = 1.0
```

Let's say a bug was found in a particular API version, but only the JSON part.
If the client also accepts XML, this will be used for the particular version.
 ``` php
$negotiator   = new \Negotiation\ApiFormatNegotiator();

// Client wants to use version 1.1, no matter the response format
$acceptHeader =  'application/json;version=1.1,application/xml;version=1.1';
// Note that version 1.1 is removed!
$priorities   = array(
    'application/json' => array('1.0', '1.2', '1.3', '2.0'),
    'application/xml' => array('1.0', '1.1', '1.2', '1.3', '2.0')
);

$format = $negotiator->getBest($acceptHeader, $priorities);
// $format->getValue() = application/xml
// $format->getVersion() = 1.1.
 ```

### Language Negotiation

Language negotiation is handled by the `LanguageNegotiator` class:

``` php
<?php

$negotiator = new \Negotiation\LanguageNegotiator();
$language   = $negotiator->getBest('da, en-gb;q=0.8, en;q=0.7');
// $language = da
```


### Charset/Encoding Negotiation

Charset/Encoding negotiation works out of the box using the `Negotiator` class:

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
