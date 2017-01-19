<?php
/**
 * Copyright MediaCT. All rights reserved.
 * https://www.mediact.nl
 */

namespace HylianShield\Encoding\Tests;

use HylianShield\Encoding\Base32CrockfordEncoder;
use HylianShield\Validator\BaseEncoding\Base32CrockfordValidator;
use HylianShield\Validator\ValidatorInterface;

/**
 * @coversDefaultClass \HylianShield\Encoding\Base32CrockfordEncoder
 */
class Base32CrockfordEncoderTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @return Base32CrockfordEncoder
     */
    private function getEncoder(): Base32CrockfordEncoder
    {
        static $encoder;

        if ($encoder === null) {
            $encoder = new Base32CrockfordEncoder();
        }

        return $encoder;
    }

    /**
     * @return ValidatorInterface
     */
    private function getValidator(): ValidatorInterface
    {
        static $validator;

        if ($validator === null) {
            $validator = new Base32CrockfordValidator();
        }

        return $validator;
    }

    /**
     * @return int[][]
     */
    public function numberProvider(): array
    {
        $calls          = [];
        $partitionSizes = [0, 4, 5, 8];
        $range          = range(0, 37 * 4);

        foreach ($partitionSizes as $partitionSize) {
            foreach ($range as $number) {
                array_push($calls, [$number, $partitionSize]);
            }
        }

        return $calls;
    }

    /**
     * @dataProvider numberProvider
     *
     * @param int $number
     * @param int $partitionSize
     *
     * @return void
     * @covers ::encode
     * @covers ::decode
     */
    public function testEncodeDecode(int $number, int $partitionSize = 0)
    {
        $validator = $this->getValidator();
        $encoder   = $this->getEncoder();
        $encoded   = $encoder->encode($number, $partitionSize);
        $decoded   = $encoder->decode($encoded);

        $this->assertTrue(
            $validator->validate($encoded),
            sprintf('Invalid encoded string: "%s".', $encoded)
        );

        $this->assertEquals(
            $number,
            $decoded,
            sprintf(
                'Decoded number (%d) does not match original input (%d).',
                $decoded,
                $number
            )
        );
    }
}
