<?php
/**
 * File containing the LegacyPathGeneratorTest class.
 *
 * @copyright Copyright (C) 1999-2013 eZ Systems AS. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License v2
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
     *
     * @return void
     */
    public function testGetStoragePathForField( $data, $expectedPath )
    {
        $pathGenerator = $this->getPathGenerator();

        $this->assertEquals(
            $expectedPath,
            $pathGenerator->getStoragePathForField(
                $data['status'],
                $data['fieldId'],
                $data['versionNo'],
                $data['languageCode'],
                $data['nodePathString']
            )
        );
    }

    public function provideStoragePathForFieldData()
    {
        return array(
            array(
                array(
                    'status' => 1, // VersionInfo::STATUS_PUBLISHED
                    'fieldId' => 23,
                    'versionNo' => 1,
                    'languageCode' => 'eng-US',
                    'nodePathString' => 'sindelfingen/bielefeld',
                ),
                'images/sindelfingen/bielefeld/23-1-eng-US',
            ),
            array(
                array(
                    'status' => 1, // VersionInfo::STATUS_PUBLISHED
                    'fieldId' => 23,
                    'versionNo' => 42,
                    'languageCode' => 'ger-DE',
                    'nodePathString' => 'sindelfingen',
                ),
                'images/sindelfingen/23-42-ger-DE',
            ),
            array(
                array(
                    'status' => 0, // VersionInf::STATUS_DRAFT
                    'fieldId' => 23,
                    'versionNo' => 2,
                    'languageCode' => 'eng-GB',
                    'nodePathString' => null,
                ),
                'images-versioned/23/2-eng-GB',
            ),
        );
    }

    /**
     * @covers LegacyPathGenerator::isPathForDraft()
     * @dataProvider providePathForTestIsPathForDraft
     */
    public function testIsPathForDraft( $path, $isPathForDraft )
    {
        self::assertEquals(
            $isPathForDraft,
            $this->getPathGenerator()->isPathForDraft( $path )
        );
    }

    public function providePathForTestIsPathForDraft()
    {
        return array(
            array( 'var/dir/storage/images/sindelfingen/bielefeld/23-1-eng-US', false ),
            array( 'var/dir/storage/images/sindelfingen/23-42-ger-DE', false ),
            array( 'var/dir/storage/images-versioned/23/2-eng-GB', true )
        );
    }

    /**
     * @return LegacyPathGenerator
     */
    protected function getPathGenerator()
    {
        return new LegacyPathGenerator( 'images-versioned', 'images' );
    }
}
