<?php
/**
 * File containing the MediaTest class
 *
 * @copyright Copyright (C) 1999-2012 eZ Systems AS. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License v2
 * @version //autogentag//
 */

namespace eZ\Publish\Core\FieldType\Tests;
use eZ\Publish\Core\FieldType\Media\Type as MediaType,
    eZ\Publish\Core\FieldType\Media\Value as MediaValue;

/**
 * @group fieldType
 * @group ezbinaryfile
 */
class MediaTest extends BinaryBaseTest
{
    /**
     * Returns the field type under test.
     *
     * This method is used by all test cases to retrieve the field type under
     * test. Just create the FieldType instance using mocks from the provided
     * get*Mock() methods and/or custom get*Mock() implementations. You MUST
     * NOT take care for test case wide caching of the field type, just return
     * a new instance from this method!
     *
     * @return FieldType
     */
    protected function createFieldTypeUnderTest()
    {
        return new MediaType(
            $this->getValidatorServiceMock(),
            $this->getFieldTypeToolsMock(),
            $this->getFileServiceMock(),
            $this->getMimeTypeDetectorMock()
        );
    }

    protected function getSettingsSchemaExpectation()
    {
        return array(
            'mediaType' => array(
                'type' => 'choice',
                'default' => MediaType::TYPE_HTML5_VIDEO,
            )
        );
    }

    public function provideInvalidInputForAcceptValue()
    {
        $baseInput = parent::provideInvalidInputForAcceptValue();
        $binaryFileInput = array(
            array(
                new MediaValue(),
                'eZ\\Publish\\Core\\Base\\Exceptions\\InvalidArgumentException',
            ),
            array(
                new MediaValue( array( 'path' => '/foo/bar' ) ),
                'eZ\\Publish\\Core\\Base\\Exceptions\\InvalidArgumentException',
            ),
            array(
                new MediaValue( array( 'hasController' => 'yes' ) ),
                'eZ\\Publish\\Core\\Base\\Exceptions\\InvalidArgumentException',
            ),
            array(
                new MediaValue( array( 'autoplay' => 'yes' ) ),
                'eZ\\Publish\\Core\\Base\\Exceptions\\InvalidArgumentException',
            ),
            array(
                new MediaValue( array( 'loop' => 'yes' ) ),
                'eZ\\Publish\\Core\\Base\\Exceptions\\InvalidArgumentException',
            ),
            array(
                new MediaValue( array( 'height' => array() ) ),
                'eZ\\Publish\\Core\\Base\\Exceptions\\InvalidArgumentException',
            ),
            array(
                new MediaValue( array( 'width' => new \stdClass() ) ),
                'eZ\\Publish\\Core\\Base\\Exceptions\\InvalidArgumentException',
            ),
        );
        return array_merge( $baseInput, $binaryFileInput );
    }

    public function provideValidInputForAcceptValue()
    {
        return array(
            array(
                null,
                null
            ),
            array(
                __FILE__,
                new MediaValue( array(
                    'path' => __FILE__,
                    'fileName' => basename( __FILE__ ),
                    'fileSize' => filesize( __FILE__ ),
                    'mimeType' => 'text/plain',
                    'hasController' => false,
                    'autoplay' => false,
                    'loop' => false,
                    'width' => 0,
                    'height' => 0,
                ) )
            ),
            array(
                array( 'path' => __FILE__ ),
                new MediaValue( array(
                    'path' => __FILE__,
                    'fileName' => basename( __FILE__ ),
                    'fileSize' => filesize( __FILE__ ),
                    'mimeType' => 'text/plain',
                    'hasController' => false,
                    'autoplay' => false,
                    'loop' => false,
                    'width' => 0,
                    'height' => 0,
                ) )
            ),
            array(
                array(
                    'path' => __FILE__,
                    'fileSize' => 23,
                ),
                new MediaValue( array(
                    'path' => __FILE__,
                    'fileName' => basename( __FILE__ ),
                    'fileSize' => 23,
                    'mimeType' => 'text/plain',
                    'hasController' => false,
                    'autoplay' => false,
                    'loop' => false,
                    'width' => 0,
                    'height' => 0,
                ) )
            ),
            array(
                array(
                    'path' => __FILE__,
                    'mimeType' => 'application/text+php',
                ),
                new MediaValue( array(
                    'path' => __FILE__,
                    'fileName' => basename( __FILE__ ),
                    'fileSize' => filesize( __FILE__ ),
                    'mimeType' => 'application/text+php',
                    'hasController' => false,
                    'autoplay' => false,
                    'loop' => false,
                    'width' => 0,
                    'height' => 0,
                ) )
            ),
            array(
                array(
                    'path' => __FILE__,
                    'hasController' => true,
                ),
                new MediaValue( array(
                    'path' => __FILE__,
                    'fileName' => basename( __FILE__ ),
                    'fileSize' => filesize( __FILE__ ),
                    'mimeType' => 'text/plain',
                    'hasController' => true,
                    'autoplay' => false,
                    'loop' => false,
                    'width' => 0,
                    'height' => 0,
                ) )
            ),
            array(
                array(
                    'path' => __FILE__,
                    'autoplay' => true,
                ),
                new MediaValue( array(
                    'path' => __FILE__,
                    'fileName' => basename( __FILE__ ),
                    'fileSize' => filesize( __FILE__ ),
                    'mimeType' => 'text/plain',
                    'hasController' => false,
                    'autoplay' => true,
                    'loop' => false,
                    'width' => 0,
                    'height' => 0,
                ) )
            ),
            array(
                array(
                    'path' => __FILE__,
                    'loop' => true,
                ),
                new MediaValue( array(
                    'path' => __FILE__,
                    'fileName' => basename( __FILE__ ),
                    'fileSize' => filesize( __FILE__ ),
                    'mimeType' => 'text/plain',
                    'hasController' => false,
                    'autoplay' => false,
                    'loop' => true,
                    'width' => 0,
                    'height' => 0,
                ) )
            ),
            array(
                array(
                    'path' => __FILE__,
                    'width' => 23,
                ),
                new MediaValue( array(
                    'path' => __FILE__,
                    'fileName' => basename( __FILE__ ),
                    'fileSize' => filesize( __FILE__ ),
                    'mimeType' => 'text/plain',
                    'hasController' => false,
                    'autoplay' => false,
                    'loop' => false,
                    'width' => 23,
                    'height' => 0,
                ) )
            ),
            array(
                array(
                    'path' => __FILE__,
                    'height' => 42,
                ),
                new MediaValue( array(
                    'path' => __FILE__,
                    'fileName' => basename( __FILE__ ),
                    'fileSize' => filesize( __FILE__ ),
                    'mimeType' => 'text/plain',
                    'hasController' => false,
                    'autoplay' => false,
                    'loop' => false,
                    'width' => 0,
                    'height' => 42,
                ) )
            ),
        );
    }
}
