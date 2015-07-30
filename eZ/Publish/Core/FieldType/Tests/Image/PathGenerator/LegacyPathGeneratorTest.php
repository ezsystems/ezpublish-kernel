<?php

/**
 * File containing the LegacyPathGeneratorTest class.
 *
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 *
 * @version //autogentag//
 */
namespace eZ\Publish\Core\FieldType\Tests\Image\PathGenerator;

use eZ\Publish\Core\FieldType\Image\PathGenerator\LegacyPathGenerator;
use PHPUnit_Framework_TestCase;

/**
 * @group fieldType
 * @group ezimage
 */
class LegacyPathGeneratorTest extends PHPUnit_Framework_TestCase
{
    /**
     * @param mixed $data
     * @param mixed $expectedPath
     *
     * @dataProvider provideStoragePathForFieldData
     */
    public function testGetStoragePathForField($data, $expectedPath)
    {
        $pathGenerator = new LegacyPathGenerator();

        $this->assertEquals(
            $expectedPath,
            $pathGenerator->getStoragePathForField(
                $data['fieldId'],
                $data['versionNo'],
                $data['languageCode']
            )
        );
    }

    public function provideStoragePathForFieldData()
    {
        return array(
            array(
                array(
                    'fieldId' => 42,
                    'versionNo' => 1,
                    'languageCode' => 'eng-US',
                ),
                '2/4/0/0/42-1-eng-US',
            ),
            array(
                array(
                    'fieldId' => 23,
                    'versionNo' => 42,
                    'languageCode' => 'ger-DE',
                ),
                '3/2/0/0/23-42-ger-DE',
            ),
            array(
                array(
                    'fieldId' => 123456,
                    'versionNo' => 2,
                    'languageCode' => 'eng-GB',
                ),
                '6/5/4/3/123456-2-eng-GB',
            ),
        );
    }
}
