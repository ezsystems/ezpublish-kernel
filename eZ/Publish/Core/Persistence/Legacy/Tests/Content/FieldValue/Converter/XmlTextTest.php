<?php
/**
 * File containing the XmlTextTest class
 *
 * @copyright Copyright (C) 1999-2013 eZ Systems AS. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License v2
 * @version //autogentag//
 */

namespace eZ\Publish\Core\Persistence\Legacy\Tests\Content\FieldValue\Converter;

use eZ\Publish\SPI\Persistence\Content\FieldValue;
use eZ\Publish\Core\Persistence\Legacy\Content\StorageFieldValue;
use eZ\Publish\Core\Persistence\Legacy\Content\FieldValue\Converter\XmlText as XmlTextConverter;
use PHPUnit_Framework_TestCase;
use DOMDocument;

/**
 * Test case for XmlText converter in Legacy storage
 *
 * @group fieldType
 * @group ezxmltext
 */
class XmlTextTest extends PHPUnit_Framework_TestCase
{
    /**
     * @var \eZ\Publish\Core\Persistence\Legacy\Content\FieldValue\Converter\XmlText
     */
    protected $converter;

    /**
     * @var string
     */
    private $xmlText;

    protected function setUp()
    {
        parent::setUp();
        $this->converter = new XmlTextConverter;
        $this->xmlText = <<<EOT
<?xml version="1.0" encoding="utf-8"?>
<section xmlns:image="http://ez.no/namespaces/ezpublish3/image/" xmlns:xhtml="http://ez.no/namespaces/ezpublish3/xhtml/" xmlns:custom="http://ez.no/namespaces/ezpublish3/custom/"><paragraph>Some paragraph content</paragraph></section>

EOT;
    }

    protected function tearDown()
    {
        unset( $this->xmlText );
        parent::tearDown();
    }

    /**
     * @covers \eZ\Publish\Core\Persistence\Legacy\Content\FieldValue\Converter\XmlText::toStorageValue
     */
    public function testToStorageValue()
    {
        $value = new FieldValue;
        $value->data = new DOMDocument;
        $value->data->loadXML( $this->xmlText );
        $storageFieldValue = new StorageFieldValue;

        $this->converter->toStorageValue( $value, $storageFieldValue );
        self::assertSame( $value->data->saveXML(), $storageFieldValue->dataText );
    }

    /**
     * @covers \eZ\Publish\Core\Persistence\Legacy\Content\FieldValue\Converter\XmlText::toFieldValue
     */
    public function testToFieldValue()
    {
        $storageFieldValue = new StorageFieldValue;
        $storageFieldValue->dataText = $this->xmlText;
        $fieldValue = new FieldValue;

        $this->converter->toFieldValue( $storageFieldValue, $fieldValue );
        self::assertSame( $storageFieldValue->dataText, $fieldValue->data->saveXML() );
    }
}
