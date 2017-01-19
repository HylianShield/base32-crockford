<?php
/**
 * Copyright MediaCT. All rights reserved.
 * https://www.mediact.nl
 */

namespace HylianShield\Encoding;

use OutOfRangeException;
use UnexpectedValueException;

/**
 * @see http://www.crockford.com/wrmg/base32.html
 */
class Base32CrockfordEncoder
{
    /** @var string[] */
    const ALPHABET = [
        // Encoding alphabet.
        '0', '1', '2', '3', '4', '5', '6', '7', '8', '9',
        'A', 'B', 'C', 'D', 'E', 'F', 'G', 'H', 'J', 'K',
        'M', 'N', 'P', 'Q', 'R', 'S', 'T', 'V', 'W', 'X',
        'Y', 'Z',

        // Extended alphabet for check symbols.
        '*', '~', '$', '=', 'U'
    ];

    /** @var string */
    const PARTITION_SYMBOL = '-';

    /** @var string */
    const PADDING_SYMBOL = '0';

    /** @var int */
    const PADDING_DIRECTION = STR_PAD_LEFT;

    /** @var int */
    const GROUP_SIZE = 8;

    /**
     * Normalize the given string.
     *
     * @param string $deNormalized
     *
     * @return string
     */
    public static function normalize(string $deNormalized): string
    {
        return strtr(
            strtoupper($deNormalized),
            [
                // Translate mis-pronunciations.
                'I' => '1',
                'L' => '1',
                'O' => '0',
                // Remove partitioning.
                static::PARTITION_SYMBOL => ''
            ]
        );
    }

    /**
     * Encode the given number.
     *
     * @param int $number
     * @param int $partitionSize
     *
     * @return string
     */
    public function encode(
        int $number,
        int $partitionSize = self::GROUP_SIZE
    ): string {
        if ($number < 0) {
            throw new OutOfRangeException(
                'The given input must be greater than or equal to zero.'
            );
        }

        $checkSymbol = static::ALPHABET[$number % 37];
        $encoded     = '';

        if ($number > 0) {
            $remainder = $number;

            while ($remainder > 0) {
                $encoded   = static::ALPHABET[$remainder % 32] . $encoded;
                $remainder = intval($remainder / 32);
            }

            $encoded = str_pad(
                $encoded,
                (
                    intval(strlen($encoded) / static::GROUP_SIZE)
                    + 1
                ) * static::GROUP_SIZE,
                static::PADDING_SYMBOL,
                static::PADDING_DIRECTION
            );
        }

        if ($partitionSize > 0 && $number > 0) {
            $encoded = implode(
                static::PARTITION_SYMBOL,
                str_split(
                    $encoded,
                    $partitionSize
                )
            );
        }

        return $encoded . $checkSymbol;
    }

    /**
     * Decode the given string back to a number.
     *
     * @param string $encoded
     *
     * @return int
     */
    public function decode(string $encoded): int
    {
        static $pattern;

        if ($pattern === null) {
            $pattern = sprintf(
                '/^(([%s]{%d})*)([%s]?)$/',
                preg_quote(
                    implode('', array_slice(static::ALPHABET, 0, 32)),
                    '/'
                ),
                static::GROUP_SIZE,
                preg_quote(
                    implode('', static::ALPHABET),
                    '/'
                )
            );
        }

        $normalized  = static::normalize($encoded);
        $characters  = [];
        $decoded     = 0;
        $check       = 0;
        $checkSymbol = '0';

        if (preg_match($pattern, $normalized, $matches)) {
            $checkSymbol = array_pop($matches);
            $check       = array_search($checkSymbol, static::ALPHABET);
            $characters  = str_split(
                ltrim(
                    next($matches),
                    static::PADDING_SYMBOL
                )
            );

            foreach ($characters as $character) {
                $decoded = $decoded * 32 + static::decodeCharacter($character);
            }
        }

        if ($check > 0 && $decoded % 37 !== $check) {
            throw new UnexpectedValueException(
                sprintf(
                    'Check symbol "%s" (%d) mismatches "%s" (%d).',
                    $checkSymbol,
                    $check,
                    implode('', $characters),
                    $decoded
                )
            );
        }

        return $decoded;
    }

    /**
     * Decode the given character.
     *
     * @param string $character
     *
     * @return int
     */
    public static function decodeCharacter(string $character): int
    {
        return array_search($character, static::ALPHABET);
    }
}
