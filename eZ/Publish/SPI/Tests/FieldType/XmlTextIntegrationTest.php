<?php

/**
 * File contains: eZ\Publish\SPI\Tests\FieldType\XmlTextIntegrationTest class.
 *
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 *
 * @version //autogentag//
 */
namespace eZ\Publish\SPI\Tests\FieldType;

use eZ\Publish\Core\Persistence\Legacy\Content\FieldValue\Converter\XmlTextConverter;
use eZ\Publish\Core\FieldType;
use eZ\Publish\Core\FieldType\FieldSettings;
use eZ\Publish\SPI\Persistence\Content\FieldValue;
use eZ\Publish\SPI\Persistence\Content\FieldTypeConstraints;
use eZ\Publish\Core\FieldType\XmlText\XmlTextStorage\Gateway\LegacyStorage;
use eZ\Publish\Core\FieldType\Url\UrlStorage\Gateway\LegacyStorage as UrlGateway;

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
class XmlTextIntegrationTest extends BaseIntegrationTest
{
    /**
     * Get name of tested field type.
     *
     * @return string
     */
    public function getTypeName()
    {
        return 'ezxmltext';
    }

    /**
     * Get handler with required custom field types registered.
     *
     * @return Handler
     */
    public function getCustomHandler()
    {
        $fieldType = new FieldType\XmlText\Type();
        $fieldType->setTransformationProcessor($this->getTransformationProcessor());

        return $this->getHandler(
            'ezxmltext',
            $fieldType,
            new XmlTextConverter(),
            new FieldType\XmlText\XmlTextStorage(
                array(
                    'LegacyStorage' => new LegacyStorage(new UrlGateway()),
                )
            )
        );
    }

    /**
     * Returns the FieldTypeConstraints to be used to create a field definition
     * of the FieldType under test.
     *
     * @return \eZ\Publish\SPI\Persistence\Content\FieldTypeConstraints
     */
    public function getTypeConstraints()
    {
        return new FieldTypeConstraints();
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
        return array(
            // The ezxmltext field type does not have any special field definition
            // properties
            array('fieldType', 'ezxmltext'),
            array(
                'fieldTypeConstraints',
                new FieldTypeConstraints(
                    array(
                        'fieldSettings' => new FieldSettings(
                            array(
                                'numRows' => 0,
                                'tagPreset' => null,
                            )
                        ),
                    )
                ),
            ),
        );
    }

    /**
     * Get initial field value.
     *
     * @return \eZ\Publish\SPI\Persistence\Content\FieldValue
     */
    public function getInitialValue()
    {
        $xml = '<?xml version="1.0" encoding="utf-8"?><section xmlns:image="http://ez.no/namespaces/ezpublish3/image/" xmlns:xhtml="http://ez.no/namespaces/ezpublish3/xhtml/" xmlns:custom="http://ez.no/namespaces/ezpublish3/custom/"><paragraph>Paragraph content…</paragraph></section>';

        return new FieldValue(
            array(
                'data' => $xml,
                'externalData' => null,
                'sortKey' => null,
            )
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
        $xml = '<?xml version="1.0" encoding="utf-8"?><section xmlns:image="http://ez.no/namespaces/ezpublish3/image/" xmlns:xhtml="http://ez.no/namespaces/ezpublish3/xhtml/" xmlns:custom="http://ez.no/namespaces/ezpublish3/custom/"><paragraph>Some different content…</paragraph></section>';

        return new FieldValue(
            array(
                'data' => $xml,
                'externalData' => null,
                'sortKey' => null,
            )
        );
    }
}
