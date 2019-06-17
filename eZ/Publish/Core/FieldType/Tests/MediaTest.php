<?php

/**
 * File containing the MediaTest class.
 *
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
namespace eZ\Publish\Core\FieldType\Tests;

use eZ\Publish\Core\FieldType\Media\Type as MediaType;
use eZ\Publish\Core\FieldType\Media\Value as MediaValue;
use eZ\Publish\Core\Base\Exceptions\InvalidArgumentException;

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
        $fieldType = new MediaType();
        $fieldType->setTransformationProcessor($this->getTransformationProcessorMock());

        return $fieldType;
    }

    protected function getEmptyValueExpectation()
    {
        return new MediaValue();
    }

    protected function getSettingsSchemaExpectation()
    {
        return array(
            'mediaType' => array(
                'type' => 'choice',
                'default' => MediaType::TYPE_HTML5_VIDEO,
            ),
        );
    }

    public function provideInvalidInputForAcceptValue()
    {
        $baseInput = parent::provideInvalidInputForAcceptValue();
        $binaryFileInput = array(
            array(
                new MediaValue(array('id' => '/foo/bar')),
                InvalidArgumentException::class,
            ),
            array(
                new MediaValue(array('hasController' => 'yes')),
                InvalidArgumentException::class,
            ),
            array(
                new MediaValue(array('autoplay' => 'yes')),
                InvalidArgumentException::class,
            ),
            array(
                new MediaValue(array('loop' => 'yes')),
                InvalidArgumentException::class,
            ),
            array(
                new MediaValue(array('height' => array())),
                InvalidArgumentException::class,
            ),
            array(
                new MediaValue(array('width' => array())),
                InvalidArgumentException::class,
            ),
        );

        return array_merge($baseInput, $binaryFileInput);
    }

    public function provideValidInputForAcceptValue()
    {
        return array(
            array(
                null,
                new MediaValue(),
            ),
            array(
                new MediaValue(),
                new MediaValue(),
            ),
            array(
                __FILE__,
                new MediaValue(
                    array(
                        'inputUri' => __FILE__,
                        'fileName' => basename(__FILE__),
                        'fileSize' => filesize(__FILE__),
                        'hasController' => false,
                        'autoplay' => false,
                        'loop' => false,
                        'width' => 0,
                        'height' => 0,
                        'uri' => '',
                    )
                ),
            ),
            array(
                array('inputUri' => __FILE__),
                new MediaValue(
                    array(
                        'inputUri' => __FILE__,
                        'fileName' => basename(__FILE__),
                        'fileSize' => filesize(__FILE__),
                        'hasController' => false,
                        'autoplay' => false,
                        'loop' => false,
                        'width' => 0,
                        'height' => 0,
                        'uri' => '',
                    )
                ),
            ),
            array(
                array(
                    'inputUri' => __FILE__,
                    'fileSize' => 23,
                ),
                new MediaValue(
                    array(
                        'inputUri' => __FILE__,
                        'fileName' => basename(__FILE__),
                        'fileSize' => 23,
                        'hasController' => false,
                        'autoplay' => false,
                        'loop' => false,
                        'width' => 0,
                        'height' => 0,
                        'uri' => '',
                    )
                ),
            ),
            array(
                array(
                    'inputUri' => __FILE__,
                    'mimeType' => 'application/text+php',
                ),
                new MediaValue(
                    array(
                        'inputUri' => __FILE__,
                        'fileName' => basename(__FILE__),
                        'fileSize' => filesize(__FILE__),
                        'mimeType' => 'application/text+php',
                        'hasController' => false,
                        'autoplay' => false,
                        'loop' => false,
                        'width' => 0,
                        'height' => 0,
                        'uri' => '',
                    )
                ),
            ),
            array(
                array(
                    'inputUri' => __FILE__,
                    'hasController' => true,
                ),
                new MediaValue(
                    array(
                        'inputUri' => __FILE__,
                        'fileName' => basename(__FILE__),
                        'fileSize' => filesize(__FILE__),
                        'hasController' => true,
                        'autoplay' => false,
                        'loop' => false,
                        'width' => 0,
                        'height' => 0,
                        'uri' => '',
                    )
                ),
            ),
            array(
                array(
                    'inputUri' => __FILE__,
                    'autoplay' => true,
                ),
                new MediaValue(
                    array(
                        'inputUri' => __FILE__,
                        'fileName' => basename(__FILE__),
                        'fileSize' => filesize(__FILE__),
                        'hasController' => false,
                        'autoplay' => true,
                        'loop' => false,
                        'width' => 0,
                        'height' => 0,
                        'uri' => '',
                    )
                ),
            ),
            array(
                array(
                    'inputUri' => __FILE__,
                    'loop' => true,
                ),
                new MediaValue(
                    array(
                        'inputUri' => __FILE__,
                        'fileName' => basename(__FILE__),
                        'fileSize' => filesize(__FILE__),
                        'hasController' => false,
                        'autoplay' => false,
                        'loop' => true,
                        'width' => 0,
                        'height' => 0,
                        'uri' => '',
                    )
                ),
            ),
            array(
                array(
                    'inputUri' => __FILE__,
                    'width' => 23,
                ),
                new MediaValue(
                    array(
                        'inputUri' => __FILE__,
                        'fileName' => basename(__FILE__),
                        'fileSize' => filesize(__FILE__),
                        'hasController' => false,
                        'autoplay' => false,
                        'loop' => false,
                        'width' => 23,
                        'height' => 0,
                        'uri' => '',
                    )
                ),
            ),
            array(
                array(
                    'inputUri' => __FILE__,
                    'height' => 42,
                ),
                new MediaValue(
                    array(
                        'inputUri' => __FILE__,
                        'fileName' => basename(__FILE__),
                        'fileSize' => filesize(__FILE__),
                        'hasController' => false,
                        'autoplay' => false,
                        'loop' => false,
                        'width' => 0,
                        'height' => 42,
                        'uri' => '',
                    )
                ),
            ),
            // BC with 5.2 (EZP-22808). Id can be used as input instead of inputUri.
            array(
                array('id' => __FILE__),
                new MediaValue(
                    array(
                        'inputUri' => __FILE__,
                        'fileName' => basename(__FILE__),
                        'fileSize' => filesize(__FILE__),
                        'hasController' => false,
                        'autoplay' => false,
                        'loop' => false,
                        'width' => 0,
                        'height' => 0,
                        'uri' => '',
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
     *          new BinaryFileValue(
     *                  array(
     *                  'id' => 'some/file/here',
     *                  'fileName' => 'sindelfingen.jpg',
     *                  'fileSize' => 2342,
     *                  'downloadCount' => 0,
     *                  'mimeType' => 'image/jpeg',
     *              )
     *          ),
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
                new MediaValue(),
                null,
            ),
            array(
                new MediaValue(
                    array(
                        'inputUri' => __FILE__,
                        'fileName' => basename(__FILE__),
                        'fileSize' => filesize(__FILE__),
                        'mimeType' => 'text/plain',
                        'hasController' => false,
                        'autoplay' => false,
                        'loop' => true,
                        'width' => 0,
                        'height' => 0,
                        'uri' => 'http://' . basename(__FILE__),
                    )
                ),
                array(
                    'id' => null,
                    'inputUri' => __FILE__,
                    'path' => __FILE__,
                    'fileName' => basename(__FILE__),
                    'fileSize' => filesize(__FILE__),
                    'mimeType' => 'text/plain',
                    'hasController' => false,
                    'autoplay' => false,
                    'loop' => true,
                    'width' => 0,
                    'height' => 0,
                    'uri' => 'http://' . basename(__FILE__),
                ),
            ),
            // BC with 5.0 (EZP-20948). Path can be used as input instead of inputUri.
            array(
                new MediaValue(
                    array(
                        'path' => __FILE__,
                        'fileName' => basename(__FILE__),
                        'fileSize' => filesize(__FILE__),
                        'mimeType' => 'text/plain',
                        'hasController' => false,
                        'autoplay' => false,
                        'loop' => true,
                        'width' => 0,
                        'height' => 0,
                        'uri' => 'http://' . basename(__FILE__),
                    )
                ),
                array(
                    'id' => null,
                    'inputUri' => __FILE__,
                    'path' => __FILE__,
                    'fileName' => basename(__FILE__),
                    'fileSize' => filesize(__FILE__),
                    'mimeType' => 'text/plain',
                    'hasController' => false,
                    'autoplay' => false,
                    'loop' => true,
                    'width' => 0,
                    'height' => 0,
                    'uri' => 'http://' . basename(__FILE__),
                ),
            ),
            // BC with 5.2 (EZP-22808). Id can be used as input instead of inputUri.
            array(
                new MediaValue(
                    array(
                        'id' => __FILE__,
                        'fileName' => basename(__FILE__),
                        'fileSize' => filesize(__FILE__),
                        'mimeType' => 'text/plain',
                        'hasController' => false,
                        'autoplay' => false,
                        'loop' => true,
                        'width' => 0,
                        'height' => 0,
                        'uri' => 'http://' . basename(__FILE__),
                    )
                ),
                array(
                    'id' => null,
                    'inputUri' => __FILE__,
                    'path' => __FILE__,
                    'fileName' => basename(__FILE__),
                    'fileSize' => filesize(__FILE__),
                    'mimeType' => 'text/plain',
                    'hasController' => false,
                    'autoplay' => false,
                    'loop' => true,
                    'width' => 0,
                    'height' => 0,
                    'uri' => 'http://' . basename(__FILE__),
                ),
            ),
            // BC with 5.2 (EZP-22808). Id is recognized as such if not pointing to existing file.
            array(
                new MediaValue(
                    array(
                        'id' => 'application/asdf1234.pdf',
                        'fileName' => 'asdf1234.pdf',
                        'fileSize' => 12345,
                        'mimeType' => 'text/plain',
                        'hasController' => false,
                        'autoplay' => false,
                        'loop' => true,
                        'width' => 0,
                        'height' => 0,
                        'uri' => 'http://asdf1234.pdf',
                    )
                ),
                array(
                    'id' => 'application/asdf1234.pdf',
                    'inputUri' => null,
                    'path' => null,
                    'fileName' => 'asdf1234.pdf',
                    'fileSize' => 12345,
                    'mimeType' => 'text/plain',
                    'hasController' => false,
                    'autoplay' => false,
                    'loop' => true,
                    'width' => 0,
                    'height' => 0,
                    'uri' => 'http://asdf1234.pdf',
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
     *          new BinaryFileValue(
     *                  array(
     *                  'id' => 'some/file/here',
     *                  'fileName' => 'sindelfingen.jpg',
     *                  'fileSize' => 2342,
     *                  'downloadCount' => 0,
     *                  'mimeType' => 'image/jpeg',
     *              )
     *          )
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
                new MediaValue(),
            ),
            array(
                array(
                    'id' => __FILE__,
                    'fileName' => basename(__FILE__),
                    'fileSize' => filesize(__FILE__),
                    'mimeType' => 'text/plain',
                    'hasController' => false,
                    'autoplay' => false,
                    'loop' => true,
                    'width' => 0,
                    'height' => 0,
                ),
                new MediaValue(
                    array(
                        'id' => __FILE__,
                        'fileName' => basename(__FILE__),
                        'fileSize' => filesize(__FILE__),
                        'mimeType' => 'text/plain',
                        'hasController' => false,
                        'autoplay' => false,
                        'loop' => true,
                        'width' => 0,
                        'height' => 0,
                    )
                ),
            ),
            // BC with 5.0 (EZP-20948). Path can be used as input instead of ID.
            array(
                array(
                    'path' => __FILE__,
                    'fileName' => basename(__FILE__),
                    'fileSize' => filesize(__FILE__),
                    'mimeType' => 'text/plain',
                    'hasController' => false,
                    'autoplay' => false,
                    'loop' => true,
                    'width' => 0,
                    'height' => 0,
                ),
                new MediaValue(
                    array(
                        'id' => __FILE__,
                        'fileName' => basename(__FILE__),
                        'fileSize' => filesize(__FILE__),
                        'mimeType' => 'text/plain',
                        'hasController' => false,
                        'autoplay' => false,
                        'loop' => true,
                        'width' => 0,
                        'height' => 0,
                    )
                ),
            ),
            // BC with 5.2 (EZP-22808). Id can be used as input instead of inputUri.
            array(
                array(
                    'id' => __FILE__,
                    'fileName' => basename(__FILE__),
                    'fileSize' => filesize(__FILE__),
                    'mimeType' => 'text/plain',
                    'hasController' => false,
                    'autoplay' => false,
                    'loop' => true,
                    'width' => 0,
                    'height' => 0,
                ),
                new MediaValue(
                    array(
                        'id' => null,
                        'inputUri' => __FILE__,
                        'path' => __FILE__,
                        'fileName' => basename(__FILE__),
                        'fileSize' => filesize(__FILE__),
                        'mimeType' => 'text/plain',
                        'hasController' => false,
                        'autoplay' => false,
                        'loop' => true,
                        'width' => 0,
                        'height' => 0,
                    )
                ),
            ),
            // @todo: Test for REST upload hash
        );
    }

    /**
     * Provide data sets with field settings which are considered valid by the
     * {@link validateFieldSettings()} method.
     *
     * Returns an array of data provider sets with a single argument: A valid
     * set of field settings.
     * For example:
     *
     * <code>
     *  return array(
     *      array(
     *          array(),
     *      ),
     *      array(
     *          array( 'rows' => 2 )
     *      ),
     *      // ...
     *  );
     * </code>
     *
     * @return array
     */
    public function provideValidFieldSettings()
    {
        return array(
            array(
                array(),
            ),
            array(
                array(
                    'mediaType' => MediaType::TYPE_FLASH,
                ),
            ),
            array(
                array(
                    'mediaType' => MediaType::TYPE_REALPLAYER,
                ),
            ),
        );
    }

    /**
     * Provide data sets with field settings which are considered invalid by the
     * {@link validateFieldSettings()} method. The method must return a
     * non-empty array of validation error when receiving such field settings.
     *
     * Returns an array of data provider sets with a single argument: A valid
     * set of field settings.
     * For example:
     *
     * <code>
     *  return array(
     *      array(
     *          true,
     *      ),
     *      array(
     *          array( 'nonExistentKey' => 2 )
     *      ),
     *      // ...
     *  );
     * </code>
     *
     * @return array
     */
    public function provideInValidFieldSettings()
    {
        return array(
            array(
                array(
                    'not-existing' => 23,
                ),
            ),
            array(
                // mediaType must be constant
                array(
                    'mediaType' => 23,
                ),
            ),
        );
    }

    protected function provideFieldTypeIdentifier()
    {
        return 'ezmedia';
    }

    public function provideDataForGetName(): array
    {
        return [
            [
                new MediaValue(),
                [],
                'en_GB',
                '',
            ],
            [
                new MediaValue(['fileName' => 'sindelfingen.jpg']),
                [],
                'en_GB',
                'sindelfingen.jpg',
            ],
        ];
    }
}
