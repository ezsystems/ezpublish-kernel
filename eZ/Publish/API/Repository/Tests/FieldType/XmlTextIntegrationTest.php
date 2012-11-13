<?php
/**
 * File contains: eZ\Publish\API\Repository\Tests\FieldType\XmlTextIntegrationTest class
 *
 * @copyright Copyright (C) 1999-2012 eZ Systems AS. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License v2
 * @version //autogentag//
 */

namespace eZ\Publish\API\Repository\Tests\FieldType;
use eZ\Publish\Core\FieldType\XmlText\Value as XmlTextValue,
    eZ\Publish\Core\FieldType\XmlText\Type as XmlTextType,
    eZ\Publish\API\Repository\Values\Content\Field,
    DOMDocument;

/**
 * Integration test for use field type
 *
 * @group integration
 * @group field-type
 */
class XmlTextIntegrationTest extends BaseIntegrationTest
{
    /**
     * @var \DOMDocument
     */
    private $createdDOMValue;

    private $updatedDOMValue;

    protected function setUp()
    {
        parent::setUp();
        $this->createdDOMValue = new DOMDocument;
        $this->createdDOMValue->loadXML(
<<<EOT
<?xml version="1.0" encoding="utf-8"?>
<section xmlns:image="http://ez.no/namespaces/ezpublish3/image/" xmlns:xhtml="http://ez.no/namespaces/ezpublish3/xhtml/" xmlns:custom="http://ez.no/namespaces/ezpublish3/custom/">
<paragraph>Example</paragraph>
</section>
EOT
        );

        $this->updatedDOMValue = new DOMDocument;
        $this->updatedDOMValue->loadXML(
<<<EOT
<?xml version="1.0" encoding="utf-8"?>
<section xmlns:image="http://ez.no/namespaces/ezpublish3/image/" xmlns:xhtml="http://ez.no/namespaces/ezpublish3/xhtml/" xmlns:custom="http://ez.no/namespaces/ezpublish3/custom/">
<paragraph>Example 2</paragraph>
</section>
EOT
        );
    }

    /**
     * Get name of tested field tyoe
     *
     * @return string
     */
    public function getTypeName()
    {
        return 'ezxmltext';
    }

    /**
     * Get expected settings schema
     *
     * @return array
     */
    public function getSettingsSchema()
    {
        return array(
            'numRows' => array(
                'type' => 'int',
                'default' => 10,
            ),
            'tagPreset' => array(
                'type' => 'choice',
                'default' => XmlTextType::TAG_PRESET_DEFAULT,
            )
        );
    }

    /**
     * Get a valid $fieldSettings value
     *
     * @return mixed
     */
    public function getValidFieldSettings()
    {
        return array(
            'numRows' => 0,
            'tagPreset' => XmlTextType::TAG_PRESET_DEFAULT,
        );
    }

    /**
     * Get $fieldSettings value not accepted by the field type
     *
     * @return mixed
     */
    public function getInvalidFieldSettings()
    {
        return array(
            'somethingUnknown' => 0,
        );
    }

    /**
     * Get expected validator schema
     *
     * @return array
     */
    public function getValidatorSchema()
    {
        return array();
    }

    /**
     * Get a valid $validatorConfiguration
     *
     * @return mixed
     */
    public function getValidValidatorConfiguration()
    {
        return array();
    }

    /**
     * Get $validatorConfiguration not accepted by the field type
     *
     * @return mixed
     */
    public function getInvalidValidatorConfiguration()
    {
        return array(
            'unkknown' => array( 'value' => 23 )
        );
    }

    /**
     * Get initial field data for valid object creation
     *
     * @return mixed
     */
    public function getValidCreationFieldData()
    {
        $doc = new DOMDocument;
        $doc->loadXML(
<<<EOT
<?xml version="1.0" encoding="utf-8"?>
<section xmlns:image="http://ez.no/namespaces/ezpublish3/image/" xmlns:xhtml="http://ez.no/namespaces/ezpublish3/xhtml/" xmlns:custom="http://ez.no/namespaces/ezpublish3/custom/">
<paragraph>Example</paragraph>
</section>
EOT
        );
        return new XmlTextValue( $doc );
    }

    /**
     * Asserts that the field data was loaded correctly.
     *
     * Asserts that the data provided by {@link getValidCreationFieldData()}
     * was stored and loaded correctly.
     *
     * @param Field $field
     * @return void
     */
    public function assertFieldDataLoadedCorrect( Field $field)
    {
        $this->assertInstanceOf(
            'eZ\\Publish\\Core\\FieldType\\XmlText\\Value',
            $field->value
        );

        $this->assertPropertiesCorrect(
            array(
                'xml' => $this->createdDOMValue
            ),
            $field->value
        );
    }

    /**
     * Get field data which will result in errors during creation
     *
     * This is a PHPUnit data provider.
     *
     * The returned records must contain of an error producing data value and
     * the expected exception class (from the API or SPI, not implementation
     * specific!) as the second element. For example:
     *
     * <code>
     * array(
     *      array(
     *          new DoomedValue( true ),
     *          'eZ\\Publish\\API\\Repository\\Exceptions\\ContentValidationException'
     *      ),
     *      // ...
     * );
     * </code>
     *
     * @return array[]
     */
    public function provideInvalidCreationFieldData()
    {
        return array(
            array(
                new \stdClass(),
                'eZ\\Publish\\Core\\Base\\Exceptions\\InvalidArgumentType',
            ),
        );
    }

    /**
     * Get update field externals data
     *
     * @return array
     */
    public function getValidUpdateFieldData()
    {
        return new XmlTextValue( $this->updatedDOMValue );
    }

    /**
     * Get externals updated field data values
     *
     * This is a PHPUnit data provider
     *
     * @return array
     */
    public function assertUpdatedFieldDataLoadedCorrect( Field $field )
    {
        $this->assertInstanceOf(
            'eZ\\Publish\\Core\\FieldType\\XmlText\\Value',
            $field->value
        );

        $this->assertPropertiesCorrect(
            array(
                'xml' => $this->updatedDOMValue
            ),
            $field->value
        );
    }

    /**
     * Get field data which will result in errors during update
     *
     * This is a PHPUnit data provider.
     *
     * The returned records must contain of an error producing data value and
     * the expected exception class (from the API or SPI, not implementation
     * specific!) as the second element. For example:
     *
     * <code>
     * array(
     *      array(
     *          new DoomedValue( true ),
     *          'eZ\\Publish\\API\\Repository\\Exceptions\\ContentValidationException'
     *      ),
     *      // ...
     * );
     * </code>
     *
     * @return array[]
     */
    public function provideInvalidUpdateFieldData()
    {
        return $this->provideInvalidCreationFieldData();
    }

    /**
     * Asserts the the field data was loaded correctly.
     *
     * Asserts that the data provided by {@link getValidCreationFieldData()}
     * was copied and loaded correctly.
     *
     * @param Field $field
     */
    public function assertCopiedFieldDataLoadedCorrectly( Field $field )
    {
        $this->assertInstanceOf(
            'eZ\\Publish\\Core\\FieldType\\XmlText\\Value',
            $field->value
        );

        $this->assertPropertiesCorrect(
            array(
                'xml' => $this->createdDOMValue
            ),
            $field->value
        );
    }

    /**
     * Get data to test to hash method
     *
     * This is a PHPUnit data provider
     *
     * The returned records must have the the original value assigned to the
     * first index and the expected hash result to the second. For example:
     *
     * <code>
     * array(
     *      array(
     *          new MyValue( true ),
     *          array( 'myValue' => true ),
     *      ),
     *      // ...
     * );
     * </code>
     *
     * @return array
     */
    public function provideToHashData()
    {
        $xml = new DOMDocument;
        $xml->loadXML(
<<<EOT
<?xml version="1.0" encoding="utf-8"?>
<section xmlns:image="http://ez.no/namespaces/ezpublish3/image/" xmlns:xhtml="http://ez.no/namespaces/ezpublish3/xhtml/" xmlns:custom="http://ez.no/namespaces/ezpublish3/custom/">
<paragraph>Example</paragraph>
</section>
EOT
        );
        return array(
            array(
                new XmlTextValue( $xml ),
                array ( 'xml' => $xml->saveXML() ),
            ),
        );
    }

    /**
     * Get expectations for the fromHash call on our field value
     *
     * This is a PHPUnit data provider
     *
     * @return array
     */
    public function provideFromHashData()
    {
        return array(
            array(
                array(
                    'xml' => '<?xml version="1.0" encoding="utf-8"?>
<section xmlns:image="http://ez.no/namespaces/ezpublish3/image/" xmlns:xhtml="http://ez.no/namespaces/ezpublish3/xhtml/" xmlns:custom="http://ez.no/namespaces/ezpublish3/custom/">
<paragraph>Foobar</paragraph>
</section>
'
                )
            )
        );
    }

    /**
     * @dataProvider provideFromHashData
     * @TODO: Requires correct registered FieldTypeService, needs to be
     *        maintained!
     */
    public function testFromHash( $hash, $expectedValue = null )
    {
        $xmlTextValue = $this
                ->getRepository()
                ->getFieldTypeService()
                ->getFieldType( $this->getTypeName() )
                ->fromHash( $hash );
        $this->assertInstanceOf(
            'eZ\\Publish\\Core\\FieldType\\XmlText\\Value',
            $xmlTextValue
        );
        $this->assertInstanceOf( 'DOMDocument', $xmlTextValue->xml );

        $this->assertEquals( $hash['xml'], (string)$xmlTextValue );
    }

    public function providerForTestIsEmptyValue()
    {
        $doc = new DOMDocument;
        $doc->loadXML( "<section></section>" );

        return array(
            array( new XmlTextValue ),
            array( new XmlTextValue( $doc ) ),
        );
    }

    public function providerForTestIsNotEmptyValue()
    {
        $doc = new DOMDocument;
        $doc->loadXML( "<section> </section>" );
        $doc2 = new DOMDocument;
        $doc2->loadXML( "<section><paragraph></paragraph></section>" );
        return array(
            array(
                $this->getValidCreationFieldData()
            ),
            array( new XmlTextValue( $doc ) ),
            array( new XmlTextValue( $doc2 ) ),
        );
    }
}
