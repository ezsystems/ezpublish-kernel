<?php

/**
 * File containing the ImageTest class.
 *
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
namespace eZ\Publish\Core\FieldType\Tests;

use eZ\Publish\Core\FieldType\Image\Type as ImageType;
use eZ\Publish\Core\FieldType\Image\Value as ImageValue;
use eZ\Publish\Core\FieldType\ValidationError;
use eZ\Publish\SPI\FieldType\BinaryBase\MimeTypeDetector;
use eZ\Publish\Core\Base\Exceptions\InvalidArgumentException;

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
        if (!isset($this->mimeTypeDetectorMock)) {
            $this->mimeTypeDetectorMock = $this->createMock(MimeTypeDetector::class);
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
        $fieldType = new ImageType();
        $fieldType->setTransformationProcessor($this->getTransformationProcessorMock());

        return $fieldType;
    }

    /**
     * Returns the validator configuration schema expected from the field type.
     *
     * @return array
     */
    protected function getValidatorConfigurationSchemaExpectation()
    {
        return array(
            'FileSizeValidator' => array(
                'maxFileSize' => array(
                    'type' => 'int',
                    'default' => null,
                ),
            ),
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
     * @return ImageValue
     */
    protected function getEmptyValueExpectation()
    {
        return new ImageValue();
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
                InvalidArgumentException::class,
            ),
            array(
                new ImageValue(
                    array(
                        'id' => 'non/existent/path',
                    )
                ),
                InvalidArgumentException::class,
            ),
            array(
                new ImageValue(
                    array(
                        'id' => __FILE__,
                        'fileName' => array(),
                    )
                ),
                InvalidArgumentException::class,
            ),
            array(
                new ImageValue(
                    array(
                        'id' => __FILE__,
                        'fileName' => 'ImageTest.php',
                        'fileSize' => 'truebar',
                    )
                ),
                InvalidArgumentException::class,
            ),
            array(
                new ImageValue(
                    array(
                        'id' => __FILE__,
                        'fileName' => 'ImageTest.php',
                        'fileSize' => 23,
                        'alternativeText' => array(),
                    )
                ),
                InvalidArgumentException::class,
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
                new ImageValue(),
            ),
            array(
                array(),
                new ImageValue(),
            ),
            array(
                new ImageValue(),
                new ImageValue(),
            ),
            array(
                $this->getImageInputPath(),
                new ImageValue(
                    array(
                        'inputUri' => $this->getImageInputPath(),
                        'fileName' => basename($this->getImageInputPath()),
                        'fileSize' => filesize($this->getImageInputPath()),
                        'alternativeText' => null,
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
            array(
                array(
                    'inputUri' => $this->getImageInputPath(),
                    'fileName' => 'My Fancy Filename',
                    'fileSize' => 123,
                ),
                new ImageValue(
                    array(
                        'inputUri' => $this->getImageInputPath(),
                        'fileName' => 'My Fancy Filename',
                        'fileSize' => filesize($this->getImageInputPath()),
                    )
                ),
            ),
        );
    }

    /**
     * Provide input for the toHash() method.
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
                        'width' => 123,
                        'height' => 456,
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
                    'inputUri' => null,
                    'width' => 123,
                    'height' => 456,
                ),
            ),
            // BC with 5.0 (EZP-20948). Path can be used as input instead of $inputUri.
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
                    'id' => null,
                    'path' => $this->getImageInputPath(),
                    'fileName' => 'Sindelfingen-Squirrels.jpg',
                    'fileSize' => 23,
                    'alternativeText' => 'This is so Sindelfingen!',
                    'imageId' => '123-12345',
                    'uri' => 'http://' . $this->getImageInputPath(),
                    'inputUri' => $this->getImageInputPath(),
                    'width' => null,
                    'height' => null,
                ),
            ),
        );
    }

    /**
     * Provide input to fromHash() method.
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
                new ImageValue(),
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
                        'inputUri' => $this->getImageInputPath(),
                        'fileName' => 'Sindelfingen-Squirrels.jpg',
                        'fileSize' => 23,
                        'alternativeText' => 'This is so Sindelfingen!',
                        'uri' => 'http://' . $this->getImageInputPath(),
                    )
                ),
            ),
            // @todo: Provide REST upload tests
        );
    }

    protected function provideFieldTypeIdentifier()
    {
        return 'ezimage';
    }

    public function provideDataForGetName(): array
    {
        return [
            [$this->getEmptyValueExpectation(), [], 'en_GB', ''],
            [
                new ImageValue(['fileName' => 'Sindelfingen-Squirrels.jpg']),
                [],
                'en_GB',
                'Sindelfingen-Squirrels.jpg',
            ],
            // Alternative text has priority
            [
                new ImageValue(
                    [
                        'fileName' => 'Sindelfingen-Squirrels.jpg',
                        'alternativeText' => 'This is so Sindelfingen!',
                    ]
                ),
                [],
                'en_GB',
                'This is so Sindelfingen!',
            ],
            [
                new ImageValue(
                    [
                        'fileName' => 'Sindelfingen-Squirrels.jpg',
                        'alternativeText' => 'This is so Sindelfingen!',
                    ]
                ),
                [],
                'en_GB',
                'This is so Sindelfingen!',
            ],
        ];
    }

    /**
     * Provides data sets with validator configuration and/or field settings and
     * field value which are considered valid by the {@link validate()} method.
     *
     * ATTENTION: This is a default implementation, which must be overwritten if
     * a FieldType supports validation!
     *
     * For example:
     *
     * <code>
     *  return array(
     *      array(
     *          array(
     *              "validatorConfiguration" => array(
     *                  "StringLengthValidator" => array(
     *                      "minStringLength" => 2,
     *                      "maxStringLength" => 10,
     *                  ),
     *              ),
     *          ),
     *          new TextLineValue( "lalalala" ),
     *      ),
     *      array(
     *          array(
     *              "fieldSettings" => array(
     *                  'isMultiple' => true
     *              ),
     *          ),
     *          new CountryValue(
     *              array(
     *                  "BE" => array(
     *                      "Name" => "Belgium",
     *                      "Alpha2" => "BE",
     *                      "Alpha3" => "BEL",
     *                      "IDC" => 32,
     *                  ),
     *              ),
     *          ),
     *      ),
     *      // ...
     *  );
     * </code>
     *
     * @return array
     */
    public function provideValidDataForValidate()
    {
        return array(
            array(
                array(
                    'validatorConfiguration' => array(
                        'FileSizeValidator' => array(
                            'maxFileSize' => 1,
                        ),
                    ),
                ),
                new ImageValue(
                    array(
                        'id' => $this->getImageInputPath(),
                        'fileName' => basename($this->getImageInputPath()),
                        'fileSize' => filesize($this->getImageInputPath()),
                        'alternativeText' => null,
                        'uri' => '',
                    )
                ),
            ),
        );
    }

    /**
     * Provides data sets with validator configuration and/or field settings,
     * field value and corresponding validation errors returned by
     * the {@link validate()} method.
     *
     * ATTENTION: This is a default implementation, which must be overwritten
     * if a FieldType supports validation!
     *
     * For example:
     *
     * <code>
     *  return array(
     *      array(
     *          array(
     *              "validatorConfiguration" => array(
     *                  "IntegerValueValidator" => array(
     *                      "minIntegerValue" => 5,
     *                      "maxIntegerValue" => 10
     *                  ),
     *              ),
     *          ),
     *          new IntegerValue( 3 ),
     *          array(
     *              new ValidationError(
     *                  "The value can not be lower than %size%.",
     *                  null,
     *                  array(
     *                      "size" => 5
     *                  ),
     *              ),
     *          ),
     *      ),
     *      array(
     *          array(
     *              "fieldSettings" => array(
     *                  "isMultiple" => false
     *              ),
     *          ),
     *          new CountryValue(
     *              "BE" => array(
     *                  "Name" => "Belgium",
     *                  "Alpha2" => "BE",
     *                  "Alpha3" => "BEL",
     *                  "IDC" => 32,
     *              ),
     *              "FR" => array(
     *                  "Name" => "France",
     *                  "Alpha2" => "FR",
     *                  "Alpha3" => "FRA",
     *                  "IDC" => 33,
     *              ),
     *          )
     *      ),
     *      array(
     *          new ValidationError(
     *              "Field definition does not allow multiple countries to be selected."
     *          ),
     *      ),
     *      // ...
     *  );
     * </code>
     *
     * @return array
     */
    public function provideInvalidDataForValidate()
    {
        return array(
            // File is too large
            array(
                array(
                    'validatorConfiguration' => array(
                        'FileSizeValidator' => array(
                            'maxFileSize' => 0.01,
                        ),
                    ),
                ),
                new ImageValue(
                    array(
                        'id' => $this->getImageInputPath(),
                        'fileName' => basename($this->getImageInputPath()),
                        'fileSize' => filesize($this->getImageInputPath()),
                        'alternativeText' => null,
                        'uri' => '',
                    )
                ),
                array(
                    new ValidationError(
                        'The file size cannot exceed %size% byte.',
                        'The file size cannot exceed %size% bytes.',
                        array(
                            '%size%' => 0.01,
                        ),
                        'fileSize'
                    ),
                ),
            ),

            // file is not an image file
            array(
                array(
                    'validatorConfiguration' => array(
                        'FileSizeValidator' => array(
                            'maxFileSize' => 1,
                        ),
                    ),
                ),
                new ImageValue(
                    array(
                        'id' => __FILE__,
                        'fileName' => basename(__FILE__),
                        'fileSize' => filesize(__FILE__),
                        'alternativeText' => null,
                        'uri' => '',
                    )
                ),
                array(
                    new ValidationError('A valid image file is required.', null, array(), 'id'),
                ),
            ),

            // file is too large and invalid
            array(
                array(
                    'validatorConfiguration' => array(
                        'FileSizeValidator' => array(
                            'maxFileSize' => 0.01,
                        ),
                    ),
                ),
                new ImageValue(
                    array(
                        'id' => __FILE__,
                        'fileName' => basename(__FILE__),
                        'fileSize' => filesize(__FILE__),
                        'alternativeText' => null,
                        'uri' => '',
                    )
                ),
                array(
                    new ValidationError('A valid image file is required.', null, array(), 'id'),
                    new ValidationError(
                        'The file size cannot exceed %size% byte.',
                        'The file size cannot exceed %size% bytes.',
                        array(
                            '%size%' => 0.01,
                        ),
                        'fileSize'
                    ),
                ),
            ),

            // file is an image file but filename ends with .php
            array(
                array(
                    'validatorConfiguration' => array(
                        'FileSizeValidator' => array(
                            'maxFileSize' => 1,
                        ),
                    ),
                ),
                new ImageValue(
                    array(
                        'id' => __DIR__ . '/phppng.php',
                        'fileName' => basename(__DIR__ . '/phppng.php'),
                        'fileSize' => filesize(__DIR__ . '/phppng.php'),
                        'alternativeText' => null,
                        'uri' => '',
                    )
                ),
                array(
                    new ValidationError(
                        'A valid image file is required.', null, [], 'id'
                    ),
                ),
            ),

            // file is an image file but filename ends with .PHP (upper case)
            array(
                array(
                    'validatorConfiguration' => array(
                        'FileSizeValidator' => array(
                            'maxFileSize' => 1,
                        ),
                    ),
                ),
                new ImageValue(
                    array(
                        'id' => __DIR__ . '/phppng2.PHP',
                        'fileName' => basename(__DIR__ . '/phppng2.PHP'),
                        'fileSize' => filesize(__DIR__ . '/phppng2.PHP'),
                        'alternativeText' => null,
                        'uri' => '',
                    )
                ),
                array(
                    new ValidationError(
                        'A valid image file is required.', null, [], 'id'
                    ),
                ),
            ),
        );
    }
}
