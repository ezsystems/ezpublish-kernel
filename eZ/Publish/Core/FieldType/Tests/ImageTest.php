<?php
/**
 * File containing the ImageTest class
 *
 * @copyright Copyright (C) 1999-2013 eZ Systems AS. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License v2
 * @version //autogentag//
 */

namespace eZ\Publish\Core\FieldType\Tests;

use eZ\Publish\Core\FieldType\Image\Type as ImageType;
use eZ\Publish\Core\FieldType\Image\Value as ImageValue;
use ReflectionObject;

/**
 * @group fieldType
 * @group ezfloat
 */
class ImageTest extends FieldTypeTest
{
    public function getImageInputPath()
    {
        return __DIR__ . '/squirrel-developers.jpg';
    }

    /**
     * @return \eZ\Publish\SPI\FieldType\BinaryBase\MimeTypeDetector
     */
    protected function getMimeTypeDetectorMock()
    {
        if ( !isset( $this->mimeTypeDetectorMock ) )
        {
            $this->mimeTypeDetectorMock = $this->getMock(
                'eZ\\Publish\\SPI\\FieldType\\BinaryBase\\MimeTypeDetector',
                array(),
                array(),
                '',
                false
            );
        }
        return $this->mimeTypeDetectorMock;
    }

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
        return new ImageType();
    }

    /**
     * Returns the validator configuration schema expected from the field type.
     *
     * @return array
     */
    protected function getValidatorConfigurationSchemaExpectation()
    {
        return array(
            "FileSizeValidator" => array(
                'maxFileSize' => array(
                    'type' => 'int',
                    'default' => false,
                )
            )
        );
    }

    /**
     * Returns the settings schema expected from the field type.
     *
     * @return array
     */
    protected function getSettingsSchemaExpectation()
    {
        return array();
    }

    /**
     * Returns the empty value expected from the field type.
     *
     * @return void
     */
    protected function getEmptyValueExpectation()
    {
        return new ImageValue;
    }

    /**
     * Data provider for invalid input to acceptValue().
     *
     * Returns an array of data provider sets with 2 arguments: 1. The invalid
     * input to acceptValue(), 2. The expected exception type as a string. For
     * example:
     *
     * <code>
     *  return array(
     *      array(
     *          new \stdClass(),
     *          'eZ\\Publish\\Core\\Base\\Exceptions\\InvalidArgumentException',
     *      ),
     *      array(
     *          array(),
     *          'eZ\\Publish\\Core\\Base\\Exceptions\\InvalidArgumentException',
     *      ),
     *      // ...
     *  );
     * </code>
     *
     * @return array
     */
    public function provideInvalidInputForAcceptValue()
    {
        return array(
            array(
                'foo',
                'eZ\\Publish\\Core\\Base\\Exceptions\\InvalidArgumentException',
            ),
            array(
                new ImageValue(
                    array(
                        'id' => 'non/existent/path',
                    )
                ),
                'eZ\\Publish\\Core\\Base\\Exceptions\\InvalidArgumentException',
            ),
            array(
                new ImageValue(
                    array(
                        'id' => __FILE__,
                        'fileName' => array()
                    )
                ),
                'eZ\\Publish\\Core\\Base\\Exceptions\\InvalidArgumentException',
            ),
            array(
                new ImageValue(
                    array(
                        'id' => __FILE__,
                        'fileName' => 'ImageTest.php',
                        'fileSize' => 'truebar'
                    )
                ),
                'eZ\\Publish\\Core\\Base\\Exceptions\\InvalidArgumentException',
            ),
            array(
                new ImageValue(
                    array(
                        'id' => __FILE__,
                        'fileName' => 'ImageTest.php',
                        'fileSize' => 23,
                        'alternativeText' => array()
                    )
                ),
                'eZ\\Publish\\Core\\Base\\Exceptions\\InvalidArgumentException',
            ),
        );
    }

    /**
     * Data provider for valid input to acceptValue().
     *
     * Returns an array of data provider sets with 2 arguments: 1. The valid
     * input to acceptValue(), 2. The expected return value from acceptValue().
     * For example:
     *
     * <code>
     *  return array(
     *      array(
     *          null,
     *          null
     *      ),
     *      array(
     *          __FILE__,
     *          new BinaryFileValue( array(
     *              'id' => __FILE__,
     *              'fileName' => basename( __FILE__ ),
     *              'fileSize' => filesize( __FILE__ ),
     *              'downloadCount' => 0,
     *              'mimeType' => 'text/plain',
     *          ) )
     *      ),
     *      // ...
     *  );
     * </code>
     *
     * @return array
     */
    public function provideValidInputForAcceptValue()
    {
        return array(
            array(
                null,
                new ImageValue,
            ),
            array(
                array(),
                new ImageValue()
            ),
            array(
                new ImageValue(),
                new ImageValue()
            ),
            array(
                $this->getImageInputPath(),
                new ImageValue(
                    array(
                        'id' => $this->getImageInputPath(),
                        'fileName' => basename( $this->getImageInputPath() ),
                        'fileSize' => filesize( $this->getImageInputPath() ),
                        'alternativeText' => null,
                        'uri' => ''
                    )
                ),
            ),
            array(
                array(
                    'id' => $this->getImageInputPath(),
                    'fileName' => 'Sindelfingen-Squirrels.jpg',
                    'fileSize' => 23,
                    'alternativeText' => 'This is so Sindelfingen!',
                    'uri' => 'http://' . $this->getImageInputPath(),
                ),
                new ImageValue(
                    array(
                        'id' => $this->getImageInputPath(),
                        'fileName' => 'Sindelfingen-Squirrels.jpg',
                        'fileSize' => 23,
                        'alternativeText' => 'This is so Sindelfingen!',
                        'uri' => 'http://' . $this->getImageInputPath(),
                    )
                ),
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
     *              'path' => 'some/file/here',
     *              'fileName' => 'sindelfingen.jpg',
     *              'fileSize' => 2342,
     *              'downloadCount' => 0,
     *              'mimeType' => 'image/jpeg',
     *          ) ),
     *          array(
     *              'path' => 'some/file/here',
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
                new ImageValue(),
                null,
            ),
            array(
                new ImageValue(
                    array(
                        'id' => $this->getImageInputPath(),
                        'fileName' => 'Sindelfingen-Squirrels.jpg',
                        'fileSize' => 23,
                        'alternativeText' => 'This is so Sindelfingen!',
                        'imageId' => '123-12345',
                        'uri' => 'http://' . $this->getImageInputPath(),
                    )
                ),
                array(
                    'id' => $this->getImageInputPath(),
                    'path' => $this->getImageInputPath(),
                    'fileName' => 'Sindelfingen-Squirrels.jpg',
                    'fileSize' => 23,
                    'alternativeText' => 'This is so Sindelfingen!',
                    'imageId' => '123-12345',
                    'uri' => 'http://' . $this->getImageInputPath(),
                ),
            ),
            // BC with 5.0 (EZP-20948). Path can be used as input instead of ID.
            array(
                new ImageValue(
                    array(
                        'path' => $this->getImageInputPath(),
                        'fileName' => 'Sindelfingen-Squirrels.jpg',
                        'fileSize' => 23,
                        'alternativeText' => 'This is so Sindelfingen!',
                        'imageId' => '123-12345',
                        'uri' => 'http://' . $this->getImageInputPath(),
                    )
                ),
                array(
                    'id' => $this->getImageInputPath(),
                    'path' => $this->getImageInputPath(),
                    'fileName' => 'Sindelfingen-Squirrels.jpg',
                    'fileSize' => 23,
                    'alternativeText' => 'This is so Sindelfingen!',
                    'imageId' => '123-12345',
                    'uri' => 'http://' . $this->getImageInputPath(),
                ),
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
     *              'path' => 'some/file/here',
     *              'fileName' => 'sindelfingen.jpg',
     *              'fileSize' => 2342,
     *              'downloadCount' => 0,
     *              'mimeType' => 'image/jpeg',
     *          ),
     *          new BinaryFileValue( array(
     *              'path' => 'some/file/here',
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
                new ImageValue()
            ),
            array(
                array(
                    'id' => $this->getImageInputPath(),
                    'fileName' => 'Sindelfingen-Squirrels.jpg',
                    'fileSize' => 23,
                    'alternativeText' => 'This is so Sindelfingen!',
                    'uri' => 'http://' . $this->getImageInputPath(),
                ),
                new ImageValue(
                    array(
                        'id' => $this->getImageInputPath(),
                        'fileName' => 'Sindelfingen-Squirrels.jpg',
                        'fileSize' => 23,
                        'alternativeText' => 'This is so Sindelfingen!',
                        'uri' => 'http://' . $this->getImageInputPath(),
                    )
                ),
            ),
            // BC with 5.0 (EZP-20948). Path can be used as input instead of ID.
            array(
                array(
                    'path' => $this->getImageInputPath(),
                    'fileName' => 'Sindelfingen-Squirrels.jpg',
                    'fileSize' => 23,
                    'alternativeText' => 'This is so Sindelfingen!',
                    'uri' => 'http://' . $this->getImageInputPath(),
                ),
                new ImageValue(
                    array(
                        'id' => $this->getImageInputPath(),
                        'fileName' => 'Sindelfingen-Squirrels.jpg',
                        'fileSize' => 23,
                        'alternativeText' => 'This is so Sindelfingen!',
                        'uri' => 'http://' . $this->getImageInputPath(),
                    )
                ),
            )
            // @todo: Provide REST upload tests
        );
    }
}
