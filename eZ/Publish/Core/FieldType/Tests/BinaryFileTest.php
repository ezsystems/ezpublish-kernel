<?php
/**
 * File containing the BinaryFileTest class
 *
 * @copyright Copyright (C) 1999-2013 eZ Systems AS. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License v2
 * @version //autogentag//
 */

namespace eZ\Publish\Core\FieldType\Tests;

use eZ\Publish\Core\FieldType\BinaryFile\Type as BinaryFileType;
use eZ\Publish\Core\FieldType\BinaryFile\Value as BinaryFileValue;

/**
 * @group fieldType
 * @group ezbinaryfile
 */
class BinaryFileTest extends BinaryBaseTest
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
        return new BinaryFileType(
            $this->getIOServiceMock(),
            $this->getMimeTypeDetectorMock()
        );
    }

    protected function getEmptyValueExpectation()
    {
        return new BinaryFileValue;
    }

    public function provideInvalidInputForAcceptValue()
    {
        $baseInput = parent::provideInvalidInputForAcceptValue();
        $binaryFileInput = array(
            array(
                new BinaryFileValue(),
                'eZ\\Publish\\Core\\Base\\Exceptions\\InvalidArgumentException',
            ),
            array(
                new BinaryFileValue( array( 'id' => '/foo/bar' ) ),
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
                new BinaryFileValue
            ),
            array(
                new BinaryFileValue(),
                new BinaryFileValue()
            ),
            array(
                array(),
                new BinaryFileValue()
            ),
            array(
                __FILE__,
                new BinaryFileValue(
                    array(
                        'id' => __FILE__,
                        'fileName' => basename( __FILE__ ),
                        'fileSize' => filesize( __FILE__ ),
                        'downloadCount' => 0,
                        'mimeType' => null,
                    )
                ),
                array( /* 'getFileSize' => filesize( __FILE__ ) */ ),
                array( /* 'getMimeType' => 'text/plain' */ )
            ),
            array(
                array( 'id' => __FILE__ ),
                new BinaryFileValue(
                    array(
                        'id' => __FILE__,
                        'fileName' => basename( __FILE__ ),
                        'fileSize' => filesize( __FILE__ ),
                        'downloadCount' => 0,
                        'mimeType' => null,
                    )
                ),
                array( /*'getFileSize' => filesize( __FILE__ ) */ ),
                array( /* 'getMimeType' => 'text/plain' */ )
            ),
            array(
                array(
                    'id' => __FILE__,
                    'fileSize' => 23,
                ),
                new BinaryFileValue(
                    array(
                        'id' => __FILE__,
                        'fileName' => basename( __FILE__ ),
                        'fileSize' => 23,
                        'downloadCount' => 0,
                        'mimeType' => null,
                    )
                ),
                array(),
                array( /* 'getMimeType' => 'text/plain' */ )
            ),
            array(
                array(
                    'id' => __FILE__,
                    'downloadCount' => 42,
                ),
                new BinaryFileValue(
                    array(
                        'id' => __FILE__,
                        'fileName' => basename( __FILE__ ),
                        'fileSize' => filesize( __FILE__ ),
                        'downloadCount' => 42,
                        'mimeType' => null,
                    )
                ),
                array( /* 'getFileSize' => filesize( __FILE__ ) */ ),
                array( /* 'getMimeType' => 'text/plain' */ )
            ),
            array(
                array(
                    'id' => __FILE__,
                    'mimeType' => 'application/text+php',
                ),
                new BinaryFileValue(
                    array(
                        'id' => __FILE__,
                        'fileName' => basename( __FILE__ ),
                        'fileSize' => filesize( __FILE__ ),
                        'downloadCount' => 0,
                        'mimeType' => 'application/text+php',
                    )
                ),
                array( /* 'getFileSize' => filesize( __FILE__ ) */ )
            ),
        );
    }

    /**
     * Provide input for the toHash() method
     *
     * Returns an array of data provider sets with 2 arguments: 1. The valid
     * input to toHash(), 2. The expected return value from toHash().
     * For example:
     *
     * <code>
     *  return array(
     *      array(
     *          null,
     *          null
     *      ),
     *      array(
     *          new BinaryFileValue( array(
     *              'id' => 'some/file/here',
     *              'fileName' => 'sindelfingen.jpg',
     *              'fileSize' => 2342,
     *              'downloadCount' => 0,
     *              'mimeType' => 'image/jpeg',
     *          ) ),
     *          array(
     *              'id' => 'some/file/here',
     *              'fileName' => 'sindelfingen.jpg',
     *              'fileSize' => 2342,
     *              'downloadCount' => 0,
     *              'mimeType' => 'image/jpeg',
     *          )
     *      ),
     *      // ...
     *  );
     * </code>
     *
     * @return array
     */
    public function provideInputForToHash()
    {
        return array(
            array(
                new BinaryFileValue,
                null
            ),
            array(
                new BinaryFileValue(
                    array(
                        'id' => 'some/file/here',
                        'fileName' => 'sindelfingen.jpg',
                        'fileSize' => 2342,
                        'downloadCount' => 0,
                        'mimeType' => 'image/jpeg',
                        'uri' => 'http://some/file/here',
                    )
                ),
                array(
                    'id' => 'some/file/here',
                    // Used for BC with 5.0 (EZP-20948)
                    'path' => 'some/file/here',
                    'fileName' => 'sindelfingen.jpg',
                    'fileSize' => 2342,
                    'downloadCount' => 0,
                    'mimeType' => 'image/jpeg',
                    'uri' => 'http://some/file/here',
                )
            ),
            // BC with 5.0 (EZP-20948). Path can be used as input instead of ID.
            array(
                new BinaryFileValue(
                    array(
                        'path' => 'some/file/here',
                        'fileName' => 'sindelfingen.jpg',
                        'fileSize' => 2342,
                        'downloadCount' => 0,
                        'mimeType' => 'image/jpeg',
                        'uri' => 'http://some/file/here',
                    )
                ),
                array(
                    'id' => 'some/file/here',
                    'path' => 'some/file/here',
                    'fileName' => 'sindelfingen.jpg',
                    'fileSize' => 2342,
                    'downloadCount' => 0,
                    'mimeType' => 'image/jpeg',
                    'uri' => 'http://some/file/here',
                )
            )
        );
    }

    /**
     * Provide input to fromHash() method
     *
     * Returns an array of data provider sets with 2 arguments: 1. The valid
     * input to fromHash(), 2. The expected return value from fromHash().
     * For example:
     *
     * <code>
     *  return array(
     *      array(
     *          null,
     *          null
     *      ),
     *      array(
     *          array(
     *              'id' => 'some/file/here',
     *              'fileName' => 'sindelfingen.jpg',
     *              'fileSize' => 2342,
     *              'downloadCount' => 0,
     *              'mimeType' => 'image/jpeg',
     *          ),
     *          new BinaryFileValue( array(
     *              'id' => 'some/file/here',
     *              'fileName' => 'sindelfingen.jpg',
     *              'fileSize' => 2342,
     *              'downloadCount' => 0,
     *              'mimeType' => 'image/jpeg',
     *          ) )
     *      ),
     *      // ...
     *  );
     * </code>
     *
     * @return array
     */
    public function provideInputForFromHash()
    {
        return array(
            array(
                null,
                new BinaryFileValue
            ),
            array(
                array(
                    'id' => 'some/file/here',
                    'fileName' => 'sindelfingen.jpg',
                    'fileSize' => 2342,
                    'downloadCount' => 0,
                    'mimeType' => 'image/jpeg',
                ),
                new BinaryFileValue(
                    array(
                        'id' => 'some/file/here',
                        'fileName' => 'sindelfingen.jpg',
                        'fileSize' => 2342,
                        'downloadCount' => 0,
                        'mimeType' => 'image/jpeg',
                    )
                )
            ),
            // BC with 5.0 (EZP-20948). Path can be used as input instead of ID.
            array(
                array(
                    'path' => 'some/file/here',
                    'fileName' => 'sindelfingen.jpg',
                    'fileSize' => 2342,
                    'downloadCount' => 0,
                    'mimeType' => 'image/jpeg',
                ),
                new BinaryFileValue(
                    array(
                        'id' => 'some/file/here',
                        'fileName' => 'sindelfingen.jpg',
                        'fileSize' => 2342,
                        'downloadCount' => 0,
                        'mimeType' => 'image/jpeg',
                    )
                )
            )
            // @todo: Provide upload struct (via REST)!
        );
    }
}
