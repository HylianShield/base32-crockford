#!/usr/bin/env php
<?php
use HylianShield\Encoding\Base32CrockfordEncoder;

require_once __DIR__ . '/../vendor/autoload.php';

$encoder = new Base32CrockfordEncoder();
$number  = 0;

while ($number < PHP_INT_MAX / 128) {
    $encoded = $encoder->encode($number);

    try {
        $decoded = $encoder->decode($encoded);
    } catch (UnexpectedValueException $e) {
        $decoded = PHP_EOL . "\t" . $e->getMessage();
    }

    echo sprintf(
        '#%d => %s => %s',
        $number,
        $encoded,
        $decoded
    ) . PHP_EOL;

    $number += 1;
    $number *= 2;
}
