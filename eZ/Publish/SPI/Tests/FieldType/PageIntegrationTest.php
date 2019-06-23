<?php

/**
 * File contains: eZ\Publish\SPI\Tests\FieldType\PageIntegrationTest class.
 *
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license   For full copyright and license information view LICENSE file distributed with this source code.
 *
 * @version   //autogentag//
 */
namespace eZ\Publish\SPI\Tests\FieldType;

use eZ\Publish\Core\FieldType;
use eZ\Publish\Core\FieldType\FieldSettings;
use eZ\Publish\Core\Repository\ContentService;
use eZ\Publish\Core\FieldType\Page\Parts\Block;
use eZ\Publish\Core\FieldType\Page\Parts\Page;
use eZ\Publish\Core\FieldType\Page\Parts\Zone;
use eZ\Publish\Core\Persistence\Legacy;
use eZ\Publish\SPI\Persistence\Content;
use eZ\Publish\SPI\Persistence\Content\FieldTypeConstraints;

/**
 * Integration test for legacy storage field types.
 *
 * This abstract base test case is supposed to be the base for field type
 * integration tests. It basically calls all involved methods in the field type
 * ``Converter`` and ``Storage`` implementations. Fo get it working implement
 * the abstract methods in a sensible way.
 *
 * The following actions are performed by this test using the custom field
 * type:
 *
 * - Create a new content type with the given field type
 * - Load create content type
 * - Create content object of new content type
 * - Load created content
 * - Copy created content
 * - Remove copied content
 *
 * @group integration
 */
class PageIntegrationTest extends BaseIntegrationTest
{
    /**
     * Get name of tested field type.
     *
     * @return string
     */
    public function getTypeName()
    {
        return 'ezpage';
    }

    /**
     * Get handler with required custom field types registered.
     *
     * @return \eZ\Publish\SPI\Persistence\Handler
     */
    public function getCustomHandler()
    {
        $contentService = $this->createMock(ContentService::class);
        $pageService = new FieldType\Page\PageService($contentService);
        $hashConverter = new FieldType\Page\HashConverter();
        $fieldType = new FieldType\Page\Type($pageService, $hashConverter);
        $fieldType->setTransformationProcessor($this->getTransformationProcessor());

        return $this->getHandler('ezpage', $fieldType, new Legacy\Content\FieldValue\Converter\PageConverter(), new FieldType\NullStorage());
    }

    /**
     * Returns the FieldTypeConstraints to be used to create a field definition
     * of the FieldType under test.
     *
     * @return \eZ\Publish\SPI\Persistence\Content\FieldTypeConstraints
     */
    public function getTypeConstraints()
    {
        return new Content\FieldTypeConstraints();
    }

    /**
     * Get field definition data values.
     *
     * This is a PHPUnit data provider
     *
     * @return array
     */
    public function getFieldDefinitionData()
    {
        return [
            // properties
            ['fieldType', 'ezpage'],
            [
                'fieldTypeConstraints',
                new FieldTypeConstraints(
                    [
                        'fieldSettings' => new FieldSettings(
                            [
                                'defaultLayout' => '',
                            ]
                        ),
                    ]
                ),
            ],
        ];
    }

    /**
     * Get initial field value.
     *
     * @return \eZ\Publish\SPI\Persistence\Content\FieldValue
     */
    public function getInitialValue()
    {
        return new Content\FieldValue(
            [
                'data' => $this->getPage(),
                'externalData' => null,
                'sortKey' => null,
            ]
        );
    }

    /**
     * Get update field value.
     *
     * Use to update the field
     *
     * @return \eZ\Publish\SPI\Persistence\Content\FieldValue
     */
    public function getUpdatedValue()
    {
        return new Content\FieldValue(
            [
                'data' => $this->getPage(),
                'externalData' => null,
                'sortKey' => null,
            ]
        );
    }

    /**
     * Create a dummy page object.
     *
     * @return Page
     */
    protected function getPage()
    {
        $blockData = [
            'name' => 'Block 1',
            'id' => '50dc64c82efa83cfe53959240e159915',
        ];
        $block = new Block($blockData);
        $zoneData = [
            'id' => '8386907d951657e087507f49a92bb06c',
            'identifier' => 'Zone 1',
            'blocks' => [$block],
        ];
        $zone = new Zone($zoneData);
        $pageData = [
            'zones' => [$zone],
            'layout' => 'my_layout',
        ];

        return new Page($pageData);
    }
}
