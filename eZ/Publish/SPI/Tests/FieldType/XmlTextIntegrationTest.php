<?php
/**
 * File contains: eZ\Publish\SPI\Tests\FieldType\XmlTextIntegrationTest class
 *
 * @copyright Copyright (C) 1999-2013 eZ Systems AS. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License v2
 * @version //autogentag//
 */

namespace eZ\Publish\SPI\Tests\FieldType;

use eZ\Publish\Core\Persistence\Legacy\Content\FieldValue\Converter\XmlText as XmlTextConverter;
use eZ\Publish\Core\FieldType;
use eZ\Publish\Core\FieldType\FieldSettings;
use eZ\Publish\SPI\Persistence\Content\FieldValue;
use eZ\Publish\SPI\Persistence\Content\FieldTypeConstraints;
use DOMDocument;
use eZ\Publish\Core\FieldType\XmlText\XmlTextStorage\Gateway\LegacyStorage;

/**
 * Integration test for legacy storage field types
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
     * Get name of tested field type
     *
     * @return string
     */
    public function getTypeName()
    {
        return 'ezxmltext';
    }

    /**
     * Get handler with required custom field types registered
     *
     * @return Handler
     */
    public function getCustomHandler()
    {
        $handler = $this->getHandler();

        $handler->getFieldTypeRegistry()->register(
            'ezxmltext',
            new FieldType\XmlText\Type(
                new FieldType\XmlText\ConverterDispatcher( array() ),
                new FieldType\XmlText\ValidatorDispatcher(
                    array(
                        "http://docbook.org/ns/docbook" => new FieldType\XmlText\Validator(
                            $this->getAbsolutePath( "eZ/Publish/Core/FieldType/XmlText/Resources/schemas/docbook/docbook.xsd" )
                        )
                    )
                )
            )
        );
        $handler->getStorageRegistry()->register(
            'ezxmltext',
            new FieldType\XmlText\XmlTextStorage(
                array(
                    'LegacyStorage' => new LegacyStorage()
                )
            )
        );
        $handler->getFieldValueConverterRegistry()->register(
            'ezxmltext',
            new XmlTextConverter(
                new XmlTextConverter\XsltConverter(
                    $this->getAbsolutePath( "eZ/Publish/Core/Persistence/Legacy/Content/FieldValue/Converter/XmlText/Resources/stylesheets/docbook_ezxml.xsl" )
                ),
                new XmlTextConverter\XsltConverter(
                    $this->getAbsolutePath( "eZ/Publish/Core/Persistence/Legacy/Content/FieldValue/Converter/XmlText/Resources/stylesheets/ezxml_docbook.xsl" )
                ),
                new XmlTextConverter\XsdValidator(
                    $this->getAbsolutePath( "eZ/Publish/Core/Persistence/Legacy/Content/FieldValue/Converter/XmlText/Resources/schemas/ezxml.xsd" )
                )
            )
        );

        return $handler;
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
     * Get field definition data values
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
            array( 'fieldType', 'ezxmltext' ),
            array(
                'fieldTypeConstraints',
                new FieldTypeConstraints(
                    array(
                        'fieldSettings' => new FieldSettings(
                            array(
                                'numRows' => 0,
                                'tagPreset' => null,
                            )
                        )
                    )
                )
            ),
        );
    }

    /**
     * Get initial field value
     *
     * @return \eZ\Publish\SPI\Persistence\Content\FieldValue
     */
    public function getInitialValue()
    {
        $xml = new DOMDocument;
        $xml->loadXML( '<?xml version="1.0" encoding="UTF-8"?>
<article xmlns="http://docbook.org/ns/docbook" xmlns:xlink="http://www.w3.org/1999/xlink" version="5.0">
  <title>This is a heading.</title>
  <para>This is a paragraph.</para>
</article>' );
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
        $xml = new DOMDocument;
        $xml->loadXML( '<?xml version="1.0" encoding="UTF-8"?>
<article xmlns="http://docbook.org/ns/docbook" xmlns:xlink="http://www.w3.org/1999/xlink" version="5.0">
  <title>This is an updated heading.</title>
  <para>This is an updated paragraph.</para>
</article>' );
        return new FieldValue(
            array(
                'data' => $xml,
                'externalData' => null,
                'sortKey' => null,
            )
        );
    }

    /**
     * @param string $relativePath
     *
     * @return string
     */
    protected function getAbsolutePath( $relativePath )
    {
        return self::getInstallationDir() . "/" . $relativePath;
    }

    /**
     * @return string
     */
    static protected function getInstallationDir()
    {
        static $installDir = null;
        if ( $installDir === null )
        {
            $config = require 'config.php';
            $installDir = $config['service']['parameters']['install_dir'];
        }
        return $installDir;
    }
}

