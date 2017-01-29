# Introduction

This package holds an encoder / decoder that implements
[Crockford's Base 32](http://www.crockford.com/wrmg/base32.html)
implementation.

# Installation

```shell
composer require hylianshield/base32-crockford:^1.0
```

# Specification

| Attribute     | Value          |
|:--------------|:---------------|
| Padding       | `0` (required) |
| Partitioning  | `-` (optional) |

# Usage

```php
<?php
use HylianShield\Encoding\Base32CrockfordEncoder;

$encoder = new Base32CrockfordEncoder();

$encoded = $encoder->encode(1337);        // 0000019S5
$decoded = $encoder->decode('0000019S5'); // 1337
```

# Limitations

While big numbers can be properly encoded, the built-in multiply operation for PHP
fails to return the correct response if a number gets too high.

This can be solved by using
[BC Math](http://php.net/manual/en/book.bc.php),
however, since that is not installed by default, this implementation has an
upper-limit.

On the tested system, the upper limit lies between
`18.014.398.509.481.982` and `36.028.797.018.963.966`.
Read: **18 quadrillion** and **36 quadrillion** respectively.
This may differ when using a different CPU architecture.

See the [example script](examples/basic-range.php) to reproduce these findings.
Or simply run:

```shell
composer example
```

Which outputs:

```
... previous rows ...
#2251799813685246 => 000001ZZ-ZZZZZZZYN => 2251799813685246
#4503599627370494 => 000003ZZ-ZZZZZZZY7 => 4503599627370494
#9007199254740990 => 000007ZZ-ZZZZZZZYG => 9007199254740990
#18014398509481982 => 00000FZZ-ZZZZZZZY$ => 18014398509481982
#36028797018963966 => 00001000-0000000Y~ => 
	Check symbol "~" (33) mismatches "10000000000Y" (36028797018963998).
```

One can see that the number on the left no longer matches the number on the right.

