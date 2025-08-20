Date
2025-08-15

Status
`ACCEPTED`

Context
Equisoft's PHP applications uses kronos/log library to record logs. This library allows us to use an output stream via Fluentd. We used the [fluent/fluent-logger-php](https://github.com/fluent/fluent-logger-php) library since the beginning, but now this library is not maintained and logs deprecation warning when used with PHP 8.4.

Decision
We now use our own fork of the library that we updated to be compatible with PHP 8.4.

Consequences
We will use this fork until the original library is updated to be compatible with PHP 8.4. We will also have to maintain this fork until the original library is updated.
