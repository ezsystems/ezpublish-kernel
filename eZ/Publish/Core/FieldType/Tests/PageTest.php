<?php

/**
 * File containing the AuthorTest class.
 *
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
namespace eZ\Publish\Core\FieldType\Tests;

use eZ\Publish\Core\FieldType\Page\Type as PageType;
use eZ\Publish\Core\FieldType\Page\Value as PageValue;
use eZ\Publish\Core\FieldType\Page\Parts\Page;
use eZ\Publish\Core\FieldType\Page\Parts;
use eZ\Publish\Core\FieldType\Page\HashConverter;
use eZ\Publish\Core\FieldType\Page\PageService;
use eZ\Publish\Core\Base\Exceptions\InvalidArgumentException;
use DateTime;

/**
 * @group fieldType
 * @group ezpage
 */
class PageTest extends FieldTypeTest
{
    /**
     * Page service mock.
     *
     * @see getPageServiceMock()
     *
     * @var \PHPUnit\Framework\MockObject\MockObject
     */
    private $pageServiceMock;

    /** @var \eZ\Publish\Core\FieldType\Page\Parts\Page */
    protected $pageReference;

    protected $hashReference;

    private function getPageServiceMock()
    {
        if (!isset($this->pageServiceMock)) {
            $this->pageServiceMock = $this->createMock(PageService::class);
            $this->pageServiceMock->expects($this->any())
                ->method('getAvailableZoneLayouts')
                ->will($this->returnValue(['2ZonesLayout1', '2ZonesLayout2']));
        }

        return $this->pageServiceMock;
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
        $fieldType = new PageType(
            $this->getPageServiceMock(),
            new HashConverter()
        );
        $fieldType->setTransformationProcessor($this->getTransformationProcessorMock());

        return $fieldType;
    }

    protected function getPageReference()
    {
        return new Parts\Page(
            [
                'layout' => '2ZonesLayout1',
                'zones' => [
                    new Parts\Zone(
                        [
                            'id' => '6c7f907b831a819ed8562e3ddce5b264',
                            'identifier' => 'left',
                            'blocks' => [
                                new Parts\Block(
                                    [
                                        'id' => '1e1e355c8da3c92e80354f243c6dd37b',
                                        'name' => 'Campaign',
                                        'type' => 'Campaign',
                                        'view' => 'default',
                                        'overflowId' => '',
                                        'zoneId' => '6c7f907b831a819ed8562e3ddce5b264',
                                        'items' => [
                                            new Parts\Item(
                                                [
                                                    'contentId' => 10,
                                                    'locationId' => 20,
                                                    'priority' => 30,
                                                    'publicationDate' => new DateTime('@1'),
                                                    'visibilityDate' => new DateTime('@2'),
                                                    'hiddenDate' => new DateTime('@3'),
                                                    'rotationUntilDate' => new DateTime('@4'),
                                                    'movedTo' => '67dd4d9b898d89733e776c714039ae33',
                                                    'action' => 'modify',
                                                    'blockId' => '594491ab539125dc271807a83724e608',
                                                    'attributes' => ['name' => 'value'],
                                                ]
                                            ),
                                        ],
                                        'attributes' => ['name2' => 'value2'],
                                    ]
                                ),
                            ],
                            'attributes' => ['name3' => 'value3'],
                        ]
                    ),
                ],
                'attributes' => ['name4' => 'value4'],
            ]
        );
    }

    protected function getHashReference()
    {
        return [
            'layout' => '2ZonesLayout1',
            'zones' => [
                [
                    'id' => '6c7f907b831a819ed8562e3ddce5b264',
                    'identifier' => 'left',
                    'blocks' => [
                        [
                            'id' => '1e1e355c8da3c92e80354f243c6dd37b',
                            'name' => 'Campaign',
                            'type' => 'Campaign',
                            'view' => 'default',
                            'overflowId' => '',
                            'zoneId' => '6c7f907b831a819ed8562e3ddce5b264',
                            'items' => [
                                [
                                    'contentId' => 10,
                                    'locationId' => 20,
                                    'priority' => 30,
                                    'publicationDate' => 'Thursday, 01-Jan-70 00:00:01 GMT+0000',
                                    'visibilityDate' => 'Thursday, 01-Jan-70 00:00:02 GMT+0000',
                                    'hiddenDate' => 'Thursday, 01-Jan-70 00:00:03 GMT+0000',
                                    'rotationUntilDate' => 'Thursday, 01-Jan-70 00:00:04 GMT+0000',
                                    'movedTo' => '67dd4d9b898d89733e776c714039ae33',
                                    'action' => 'modify',
                                    'blockId' => '594491ab539125dc271807a83724e608',
                                    'attributes' => ['name' => 'value'],
                                ],
                            ],
                            'attributes' => ['name2' => 'value2'],
                        ],
                    ],
                    'attributes' => ['name3' => 'value3'],
                ],
            ],
            'attributes' => ['name4' => 'value4'],
        ];
    }

    /**
     * Returns the validator configuration schema expected from the field type.
     *
     * @return array
     */
    protected function getValidatorConfigurationSchemaExpectation()
    {
        return [];
    }

    /**
     * Returns the settings schema expected from the field type.
     *
     * @return array
     */
    protected function getSettingsSchemaExpectation()
    {
        return [
            'defaultLayout' => [
                'type' => 'string',
                'default' => '',
            ],
        ];
    }

    /**
     * Returns the empty value expected from the field type.
     */
    protected function getEmptyValueExpectation()
    {
        return new PageValue();
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
        return [
            [
                new \stdClass(),
                InvalidArgumentException::class,
            ],
        ];
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
     *              'path' => __FILE__,
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
        return [
            [
                null,
                new PageValue(),
            ],
            [
                new PageValue(),
                new PageValue(),
            ],
            [
                new PageValue(new Page()),
                new PageValue(),
            ],
        ];
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
        return [
            [
                new PageValue(),
                null,
            ],
            [
                new PageValue($this->getPageReference()),
                $this->getHashReference(),
            ],
        ];
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
        return [
            [
                null,
                new PageValue(),
            ],
            [
                $this->getHashReference(),
                new PageValue($this->getPageReference()),
            ],
        ];
    }

    /**
     * Provide data sets with field settings which are considered valid by the
     * {@link validateFieldSettings()} method.
     *
     * ATTENTION: This is a default implementation, which must be overwritten
     * if a FieldType supports field settings!
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
        return [
            [
                [],
            ],
            [
                ['defaultLayout' => '2ZonesLayout1'],
            ],
        ];
    }

    /**
     * Provide data sets with field settings which are considered invalid by the
     * {@link validateFieldSettings()} method. The method must return a
     * non-empty array of validation error when receiving such field settings.
     *
     * ATTENTION: This is a default implementation, which must be overwritten
     * if a FieldType supports field settings!
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
        return [
            [
                // non-existent setting
                ['isMultiple' => true],
            ],
            [
                // non-available layout
                ['defaultLayout' => '2ZonesLayout3'],
            ],
        ];
    }

    protected function provideFieldTypeIdentifier()
    {
        return 'ezpage';
    }

    public function provideDataForGetName()
    {
        return [
            [$this->getEmptyValueExpectation(), ''],
            [new PageValue($this->getPageReference()), '2ZonesLayout1'],
        ];
    }

    /**
     * Data provider for valid input to isEmptyValue().
     *
     * Returns an array of data provider sets with 2 arguments:
     *
     * 1. The valid input to isEmptyValue()
     * 2. The expected return value from isEmptyValue()
     *
     * For example:
     *
     * <code>
     *  return array(
     *      array(
     *          new PageValue(),
     *          true
     *      ),
     *      array(
     *          new PageValue( $this->getPageReference() ),
     *          false
     *      ),
     *      // ...
     *  );
     * </code>
     *
     * @return array
     */
    public function providerForTestIsEmptyValue()
    {
        return [
            [new PageValue(), true],
            [new PageValue($this->getPageReference()), false],
        ];
    }

    /**
     * @dataProvider providerForTestIsEmptyValue
     */
    public function testIsEmptyValue($value, $state)
    {
        $fieldType = $this->getFieldTypeUnderTest();

        $this->assertEquals(
            $state,
            $fieldType->isEmptyValue($value),
            'Value did not evaluate as ' . ($state ? '' : 'non-') . 'empty'
        );
    }
}
