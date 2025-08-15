# Fluent Logger PHP

**fluent-logger-php** is a PHP library to record events to fluentd from a PHP application.

## Docs
See [ADRs](./docs/adr)

## Requirements

- PHP 8.4 or higher
- fluentd v0.9.20 or higher

## Installation

### Using Composer

composer.json

```json
{

    "repositories": [
        {
          "name": "equisoft/fluent-logger-php",
          "type": "vcs",
          "url": "https://github.com/kronostechnologies/fluent-logger-php.git"
        }
    ],
    "require": {
        "equisoft/fluent-logger-php": "1.0.0"
    }
}
```

# Usage

## PHP side

```php
<?php

require_once __DIR__.'/vendor/autoload.php';

use Fluent\Logger\FluentLogger;
$logger = new FluentLogger("localhost","24224");
$logger->post("debug.test",array("hello"=>"world"));
```

## Fluentd side

Use `in_forward`.

```aconf
<source>
  @type forward
</source>
```

# Todos

* Stabilize method signatures.
* Improve performance and reliability.

# Restrictions

* Buffering and re-send support

PHP does not have threads. So, I strongly recommend you use fluentd as a local fluent proxy.

````
apache2(mod_php)
fluent-logger-php
                 `-----proxy-fluentd
                                    `------aggregator fluentd
````

# License
Apache License, Version 2.0


# Contributors

* Daniele Alessandri
* Hiro Yoshikawa
* Kazuki Ohta
* Shuhei Tanuma
* Sotaro KARASAWA
* edy
* kiyoto
* sasezaki
* satokoma
* DQNEO
