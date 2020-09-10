<?php

/**
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
namespace eZ\Publish\Core\FieldType\Tests\Image;

use eZ\Publish\Core\FieldType\Image\Value as ImageValue;
use PHPUnit\Framework\TestCase;

/**
 * @group fieldType
 * @group ezfloat
 */
class ValueTest extends TestCase
{
    public function getImageInputPath()
    {
        return __DIR__ . '/squirrel-developers.jpg';
    }

    /**
     * @dataProvider provideInputForIsEqual
     */
    public function testIsEquals($inputValue, $correctValue, $incorrectValue)
    {
        $imageValue = new ImageValue();

        $this->assertTrue($imageValue->isEquals($inputValue, $correctValue));

        $this->assertFalse($imageValue->isEquals($inputValue, $incorrectValue));
    }

    public function provideInputForIsEqual()
    {
        return [
            [
                [
                    'id' => $this->getImageInputPath(),
                    'fileName' => 'Sindelfingen-Squirrels.jpg',
                    'fileSize' => 23,
                    'alternativeText' => 'This is so Sindelfingen!',
                    'imageId' => '123-12345',
                    'uri' => 'http://' . $this->getImageInputPath(),
                    'width' => 123,
                    'height' => 456,
                ],
                [
                    'id' => $this->getImageInputPath(),
                    'path' => $this->getImageInputPath(),
                    'fileName' => 'Sindelfingen-Squirrels.jpg',
                    'fileSize' => 23,
                    'alternativeText' => 'This is so Sindelfingen!',
                    'imageId' => '123-12346',
                    'uri' => 'http://' . $this->getImageInputPath(),
                    'inputUri' => null,
                    'width' => 123,
                    'height' => 456,
                ],
                [
                    'id' => $this->getImageInputPath(),
                    'path' => $this->getImageInputPath(),
                    'fileName' => 'Sindelfingen-Squirrels.jpg',
                    'fileSize' => 23,
                    'alternativeText' => 'This is so Sindelfingen!',
                    'imageId' => '123-12345',
                    'uri' => 'http://' . $this->getImageInputPath(),
                    'inputUri' => null,
                    'width' => 124,
                    'height' => 456,
                ],
            ],
        ];
    }
}
