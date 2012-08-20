<?php
/**
 * File containing the BinaryBaseTest class
 *
 * @copyright Copyright (C) 1999-2012 eZ Systems AS. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License v2
 * @version //autogentag//
 */

namespace eZ\Publish\Core\FieldType\Tests;
use eZ\Publish\Core\FieldType\BinaryBase\Type as BinaryBaseType,
    eZ\Publish\Core\FieldType\BinaryBase\Value as BinaryBaseValue;

/**
 * Base class for binary field types
 *
 * @group fieldType
 */
abstract class BinaryBaseTest extends StandardizedFieldTypeTest
{
    private $mimeTypeDetectorMock;

    protected function getValidatorConfigurationSchemaExpectation()
    {
        return array(
            "FileSizeValidator" => array(
                "maxFileSize" => array(
                    "type" => "int",
                    "default" => false
                )
            )
        );
    }

    protected function getSettingsSchemaExpectation()
    {
        return array();
    }

    protected function getEmptyValueExpectation()
    {
        return null;
    }

    public function provideInvalidInputForAcceptValue()
    {
        return array(
            array(
                new \stdClass(),
                'eZ\\Publish\\Core\\Base\\Exceptions\\InvalidArgumentException',
            ),
            array(
                array(),
                'eZ\\Publish\\Core\\Base\\Exceptions\\InvalidArgumentException',
            ),
            array(
                array( 'path' => '/foo/bar' ),
                'eZ\\Publish\\Core\\Base\\Exceptions\\InvalidArgumentException',
            ),
        );
    }

    protected function getMimeTypeDetectorMock()
    {
        if ( !isset( $this->mimeTypeDetectorMock ) )
        {
            $this->mimeTypeDetectorMock = $this->getMock(
                'eZ\\Publish\\Core\\FieldType\\BinaryBase\\MimeTypeDetector',
                array(),
                array(),
                '',
                false
            );
        }
        return $this->mimeTypeDetectorMock;
    }
}
