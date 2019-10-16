<?php

/**
 * File containing the BinaryFileTest class.
 *
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
namespace eZ\Publish\Core\FieldType\Tests;

use eZ\Publish\Core\FieldType\BinaryFile\Type as BinaryFileType;
use eZ\Publish\Core\FieldType\BinaryFile\Value as BinaryFileValue;
use eZ\Publish\Core\Base\Exceptions\InvalidArgumentValue;
use eZ\Publish\Core\FieldType\ValidationError;

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
        $fieldType = new BinaryFileType([$this->getBlackListValidatorMock()]);
        $fieldType->setTransformationProcessor($this->getTransformationProcessorMock());

        return $fieldType;
    }

    protected function getEmptyValueExpectation()
    {
        return new BinaryFileValue();
    }

    public function provideInvalidInputForAcceptValue()
    {
        $baseInput = parent::provideInvalidInputForAcceptValue();
        $binaryFileInput = array(
            array(
                new BinaryFileValue(array('id' => '/foo/bar')),
                InvalidArgumentValue::class,
            ),
        );

        return array_merge($baseInput, $binaryFileInput);
    }

    public function provideValidInputForAcceptValue()
    {
        return array(
            array(
                null,
                new BinaryFileValue(),
            ),
            array(
                new BinaryFileValue(),
                new BinaryFileValue(),
            ),
            array(
                array(),
                new BinaryFileValue(),
            ),
            array(
                __FILE__,
                new BinaryFileValue(
                    array(
                        'inputUri' => __FILE__,
                        'fileName' => basename(__FILE__),
                        'fileSize' => filesize(__FILE__),
                        'downloadCount' => 0,
                        'mimeType' => null,
                    )
                ),
                array(/* 'getFileSize' => filesize( __FILE__ ) */),
                array(/* 'getMimeType' => 'text/plain' */),
            ),
            array(
                array('inputUri' => __FILE__),
                new BinaryFileValue(
                    array(
                        'inputUri' => __FILE__,
                        'fileName' => basename(__FILE__),
                        'fileSize' => filesize(__FILE__),
                        'downloadCount' => 0,
                        'mimeType' => null,
                    )
                ),
                array(/*'getFileSize' => filesize( __FILE__ ) */),
                array(/* 'getMimeType' => 'text/plain' */),
            ),
            array(
                array(
                    'inputUri' => __FILE__,
                    'fileSize' => 23,
                ),
                new BinaryFileValue(
                    array(
                        'inputUri' => __FILE__,
                        'fileName' => basename(__FILE__),
                        'fileSize' => 23,
                        'downloadCount' => 0,
                        'mimeType' => null,
                    )
                ),
                array(),
                array(/* 'getMimeType' => 'text/plain' */),
            ),
            array(
                array(
                    'inputUri' => __FILE__,
                    'downloadCount' => 42,
                ),
                new BinaryFileValue(
                    array(
                        'inputUri' => __FILE__,
                        'fileName' => basename(__FILE__),
                        'fileSize' => filesize(__FILE__),
                        'downloadCount' => 42,
                        'mimeType' => null,
                    )
                ),
                array(/* 'getFileSize' => filesize( __FILE__ ) */),
                array(/* 'getMimeType' => 'text/plain' */),
            ),
            array(
                array(
                    'inputUri' => __FILE__,
                    'mimeType' => 'application/text+php',
                ),
                new BinaryFileValue(
                    array(
                        'inputUri' => __FILE__,
                        'fileName' => basename(__FILE__),
                        'fileSize' => filesize(__FILE__),
                        'downloadCount' => 0,
                        'mimeType' => 'application/text+php',
                    )
                ),
                array(/* 'getFileSize' => filesize( __FILE__ ) */),
            ),
            // BC with 5.2 (EZP-22808). Id can be used as input instead of inputUri.
            array(
                array('id' => __FILE__),
                new BinaryFileValue(
                    array(
                        'inputUri' => __FILE__,
                        'fileName' => basename(__FILE__),
                        'fileSize' => filesize(__FILE__),
                        'downloadCount' => 0,
                        'mimeType' => null,
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
                new BinaryFileValue(),
                null,
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
                    'inputUri' => null,
                    'path' => null,
                    'fileName' => 'sindelfingen.jpg',
                    'fileSize' => 2342,
                    'downloadCount' => 0,
                    'mimeType' => 'image/jpeg',
                    'uri' => 'http://some/file/here',
                ),
            ),
            array(
                new BinaryFileValue(
                    array(
                        'inputUri' => 'some/file/here',
                        'fileName' => 'sindelfingen.jpg',
                        'fileSize' => 2342,
                        'downloadCount' => 0,
                        'mimeType' => 'image/jpeg',
                        'uri' => 'http://some/file/here',
                    )
                ),
                array(
                    'id' => null,
                    'inputUri' => 'some/file/here',
                    // Used for BC with 5.0 (EZP-20948)
                    'path' => 'some/file/here',
                    'fileName' => 'sindelfingen.jpg',
                    'fileSize' => 2342,
                    'downloadCount' => 0,
                    'mimeType' => 'image/jpeg',
                    'uri' => 'http://some/file/here',
                ),
            ),
            // BC with 5.0 (EZP-20948). Path can be used as input instead of inputUri.
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
                    'inputUri' => null,
                    'path' => null,
                    'fileName' => 'sindelfingen.jpg',
                    'fileSize' => 2342,
                    'downloadCount' => 0,
                    'mimeType' => 'image/jpeg',
                    'uri' => 'http://some/file/here',
                ),
            ),
            // BC with 5.0 (EZP-20948). Path can be used as input instead of inputUri.
            array(
                new BinaryFileValue(
                    array(
                        'path' => __FILE__,
                        'fileName' => 'sindelfingen.jpg',
                        'fileSize' => 2342,
                        'downloadCount' => 0,
                        'mimeType' => 'image/jpeg',
                        'uri' => 'http://some/file/here',
                    )
                ),
                array(
                    'id' => null,
                    'inputUri' => __FILE__,
                    'path' => __FILE__,
                    'fileName' => 'sindelfingen.jpg',
                    'fileSize' => 2342,
                    'downloadCount' => 0,
                    'mimeType' => 'image/jpeg',
                    'uri' => 'http://some/file/here',
                ),
            ),
            // BC with 5.2 (EZP-22808). Id can be used as input instead of inputUri.
            array(
                new BinaryFileValue(
                    array(
                        'id' => __FILE__,
                        'fileName' => 'sindelfingen.jpg',
                        'fileSize' => 2342,
                        'downloadCount' => 0,
                        'mimeType' => 'image/jpeg',
                        'uri' => 'http://some/file/here',
                    )
                ),
                array(
                    'id' => null,
                    'inputUri' => __FILE__,
                    'path' => __FILE__,
                    'fileName' => 'sindelfingen.jpg',
                    'fileSize' => 2342,
                    'downloadCount' => 0,
                    'mimeType' => 'image/jpeg',
                    'uri' => 'http://some/file/here',
                ),
            ),
            // BC with 5.2 (EZP-22808). Id is recognized as such if not pointing to existing file.
            array(
                new BinaryFileValue(
                    array(
                        'id' => 'application/asdf1234.pdf',
                        'fileName' => 'asdf1234.pdf',
                        'fileSize' => 2342,
                        'downloadCount' => 0,
                        'mimeType' => 'application/pdf',
                        'uri' => 'http://some/file/here',
                    )
                ),
                array(
                    'id' => 'application/asdf1234.pdf',
                    'inputUri' => null,
                    'path' => null,
                    'fileName' => 'asdf1234.pdf',
                    'fileSize' => 2342,
                    'downloadCount' => 0,
                    'mimeType' => 'application/pdf',
                    'uri' => 'http://some/file/here',
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
                new BinaryFileValue(),
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
                ),
            ),
            // BC with 5.0 (EZP-20948). Path can be used as input instead of inputUri.
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
                ),
            ),
            // BC with 5.2 (EZP-22808). Id can be used as input instead of inputUri.
            array(
                array(
                    'id' => __FILE__,
                    'fileName' => 'sindelfingen.jpg',
                    'fileSize' => 2342,
                    'downloadCount' => 0,
                    'mimeType' => 'image/jpeg',
                ),
                new BinaryFileValue(
                    array(
                        'id' => null,
                        'inputUri' => __FILE__,
                        'path' => __FILE__,
                        'fileName' => 'sindelfingen.jpg',
                        'fileSize' => 2342,
                        'downloadCount' => 0,
                        'mimeType' => 'image/jpeg',
                    )
                ),
            ),
            // @todo: Provide upload struct (via REST)!
        );
    }

    protected function provideFieldTypeIdentifier()
    {
        return 'ezbinaryfile';
    }

    public function provideDataForGetName()
    {
        return array(
            array(
                new BinaryFileValue(),
                '',
            ),
            array(
                new BinaryFileValue(array('fileName' => 'sindelfingen.jpg')),
                'sindelfingen.jpg',
            ),
        );
    }

    public function provideValidDataForValidate()
    {
        return [
            [
                [
                    'validatorConfiguration' => [
                        'FileSizeValidator' => [
                            'maxFileSize' => 1,
                        ],
                    ],
                ],
                new BinaryFileValue(
                    [
                        'id' => 'some/file/here',
                        'fileName' => 'sindelfingen.jpg',
                        'fileSize' => 2342,
                        'downloadCount' => 0,
                        'mimeType' => 'image/jpeg',
                    ]
                ),
            ],
        ];
    }

    public function provideInvalidDataForValidate()
    {
        return [
            // File is too large
            [
                [
                    'validatorConfiguration' => [
                        'FileSizeValidator' => [
                            'maxFileSize' => 0.01,
                        ],
                    ],
                ],
                new BinaryFileValue(
                    [
                        'id' => 'some/file/here',
                        'fileName' => 'sindelfingen.jpg',
                        'fileSize' => 150000,
                        'downloadCount' => 0,
                        'mimeType' => 'image/jpeg',
                    ]
                ),
                [
                    new ValidationError(
                        'The file size cannot exceed %size% byte.',
                        'The file size cannot exceed %size% bytes.',
                        [
                            '%size%' => 0.01,
                        ],
                        'fileSize'
                    ),
                ],
            ],

            // file extension is in blacklist
            [
                [
                    'validatorConfiguration' => [
                        'FileSizeValidator' => [
                            'maxFileSize' => 1,
                        ],
                    ],
                ],
                new BinaryFileValue(
                    [
                        'id' => 'phppng.php',
                        'fileName' => 'phppng.php',
                        'fileSize' => 'phppng.php',
                        'downloadCount' => 0,
                        'mimeType' => 'image/jpeg',
                    ]
                ),
                [
                    new ValidationError(
                        'A valid file is required. Following file extensions are on the blacklist: %extensionsBlackList%',
                        null,
                        ['%extensionsBlackList%' => implode(', ', $this->blackListedExtensions)],
                        'fileExtensionBlackList'
                    ),
                ],
            ],

            // file is an image file but filename ends with .PHP (upper case)
            [
                [
                    'validatorConfiguration' => [
                        'FileSizeValidator' => [
                            'maxFileSize' => 1,
                        ],
                    ],
                ],
                new BinaryFileValue(
                    [
                        'id' => 'phppng.PHP',
                        'fileName' => 'phppng.PHP',
                        'fileSize' => 'phppng.PHP',
                        'downloadCount' => 0,
                        'mimeType' => 'image/jpeg',
                    ]
                ),
                [
                    new ValidationError(
                        'A valid file is required. Following file extensions are on the blacklist: %extensionsBlackList%',
                        null,
                        ['%extensionsBlackList%' => implode(', ', $this->blackListedExtensions)],
                        'fileExtensionBlackList'
                    ),
                ],
            ],
        ];
    }
}
