<?php

/**
 * File contains: eZ\Publish\API\Repository\Tests\FieldType\RichTextIntegrationTest class.
 *
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
namespace eZ\Publish\API\Repository\Tests\FieldType;

use eZ\Publish\Core\FieldType\RichText\Value as RichTextValue;
use eZ\Publish\API\Repository\Values\Content\Field;
use DOMDocument;
use eZ\Publish\Core\Repository\Values\Content\Relation;
use eZ\Publish\API\Repository\Values\Content\Content;

/**
 * Integration test for use field type.
 *
 * @group integration
 * @group field-type
 */
class RichTextIntegrationTest extends SearchBaseIntegrationTest
{
    use RelationSearchBaseIntegrationTestTrait;

    /**
     * @var \DOMDocument
     */
    private $createdDOMValue;

    /**
     * @var \DOMDocument
     */
    private $updatedDOMValue;

    public function __construct($name = null, array $data = array(), $dataName = '')
    {
        $this->createdDOMValue = new DOMDocument();
        $this->createdDOMValue->loadXML(<<<EOT
<?xml version="1.0" encoding="UTF-8"?>
<section xmlns="http://docbook.org/ns/docbook" xmlns:xlink="http://www.w3.org/1999/xlink" xmlns:ezxhtml="http://ez.no/xmlns/ezpublish/docbook/xhtml" xmlns:ezcustom="http://ez.no/xmlns/ezpublish/docbook/custom" version="5.0-variant ezpublish-1.0">
    <para><link xlink:href="ezlocation://58" xlink:show="none">link1</link></para>
    <para><link xlink:href="ezcontent://54" xlink:show="none">link2</link> <ezembedinline xlink:href="ezlocation://60" view="embed" xml:id="embed-id-1" ezxhtml:class="embed-class" ezxhtml:align="left"></ezembedinline></para>
</section>
EOT
        );

        $this->updatedDOMValue = new DOMDocument();
        $this->updatedDOMValue->loadXML(<<<EOT
<?xml version="1.0" encoding="UTF-8"?>
<section xmlns="http://docbook.org/ns/docbook" xmlns:xlink="http://www.w3.org/1999/xlink" xmlns:ezxhtml="http://ez.no/xmlns/ezpublish/docbook/xhtml" xmlns:ezcustom="http://ez.no/xmlns/ezpublish/docbook/custom" version="5.0-variant ezpublish-1.0">
    <para><link xlink:href="ezlocation://60" xlink:show="none">link1</link></para>
    <para><link xlink:href="ezcontent://56" xlink:show="none">link2</link></para>
    <ezembed xlink:href="ezcontent://54" view="embed" xml:id="embed-id-1" ezxhtml:class="embed-class" ezxhtml:align="left">
      <ezconfig>
        <ezvalue key="size">medium</ezvalue>
        <ezvalue key="offset">10</ezvalue>
        <ezvalue key="limit">5</ezvalue>
      </ezconfig>
    </ezembed>
</section>
EOT
        );

        parent::__construct($name, $data, $dataName);
    }

    /**
     * @param \eZ\Publish\API\Repository\Values\Content\Content $content
     *
     * @return \eZ\Publish\Core\Repository\Values\Content\Relation[]
     */
    public function getCreateExpectedRelations(Content $content)
    {
        $contentService = $this->getRepository()->getContentService();

        return array(
            new Relation(
                array(
                    'type' => Relation::LINK,
                    'sourceContentInfo' => $content->contentInfo,
                    'destinationContentInfo' => $contentService->loadContentInfo(56),
                )
            ),
            new Relation(
                array(
                    'type' => Relation::LINK,
                    'sourceContentInfo' => $content->contentInfo,
                    'destinationContentInfo' => $contentService->loadContentInfo(54),
                )
            ),
            new Relation(
                array(
                    'type' => Relation::EMBED,
                    'sourceContentInfo' => $content->contentInfo,
                    'destinationContentInfo' => $contentService->loadContentInfo(58),
                )
            ),
        );
    }

    /**
     * @param \eZ\Publish\API\Repository\Values\Content\Content $content
     *
     * @return \eZ\Publish\Core\Repository\Values\Content\Relation[]
     */
    public function getUpdateExpectedRelations(Content $content)
    {
        $contentService = $this->getRepository()->getContentService();

        return array(
            new Relation(
                array(
                    'type' => Relation::LINK,
                    'sourceContentInfo' => $content->contentInfo,
                    'destinationContentInfo' => $contentService->loadContentInfo(58),
                )
            ),
            new Relation(
                array(
                    'type' => Relation::LINK,
                    'sourceContentInfo' => $content->contentInfo,
                    'destinationContentInfo' => $contentService->loadContentInfo(56),
                )
            ),
            new Relation(
                array(
                    // @todo Won't be possible to add before we break how we store relations with legacy kernel.
                    //'sourceFieldDefinitionIdentifier' => 'data',
                    'type' => Relation::EMBED,
                    'sourceContentInfo' => $content->contentInfo,
                    'destinationContentInfo' => $contentService->loadContentInfo(54),
                )
            ),
        );
    }

    /**
     * Get name of tested field type.
     *
     * @return string
     */
    public function getTypeName()
    {
        return 'ezrichtext';
    }

    /**
     * Get expected settings schema.
     *
     * @return array
     */
    public function getSettingsSchema()
    {
        return [];
    }

    /**
     * Get a valid $fieldSettings value.
     *
     * @return mixed
     */
    public function getValidFieldSettings()
    {
        return [];
    }

    /**
     * Get $fieldSettings value not accepted by the field type.
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
     * Get expected validator schema.
     *
     * @return array
     */
    public function getValidatorSchema()
    {
        return array();
    }

    /**
     * Get a valid $validatorConfiguration.
     *
     * @return mixed
     */
    public function getValidValidatorConfiguration()
    {
        return array();
    }

    /**
     * Get $validatorConfiguration not accepted by the field type.
     *
     * @return mixed
     */
    public function getInvalidValidatorConfiguration()
    {
        return array(
            'unknown' => array('value' => 23),
        );
    }

    /**
     * Get initial field data for valid object creation.
     *
     * @return mixed
     */
    public function getValidCreationFieldData()
    {
        return new RichTextValue($this->createdDOMValue);
    }

    /**
     * Get name generated by the given field type (either via Nameable or fieldType->getName()).
     *
     * @return string
     */
    public function getFieldName()
    {
        return 'link1 link2';
    }

    /**
     * Asserts that the field data was loaded correctly.
     *
     * Asserts that the data provided by {@link getValidCreationFieldData()}
     * was stored and loaded correctly.
     *
     * @param Field $field
     */
    public function assertFieldDataLoadedCorrect(Field $field)
    {
        $this->assertInstanceOf(
            'eZ\\Publish\\Core\\FieldType\\RichText\\Value',
            $field->value
        );

        $this->assertPropertiesCorrect(
            array(
                'xml' => $this->createdDOMValue,
            ),
            $field->value
        );
    }

    /**
     * Get field data which will result in errors during creation.
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
     * Get update field externals data.
     *
     * @return array
     */
    public function getValidUpdateFieldData()
    {
        return new RichTextValue($this->updatedDOMValue);
    }

    /**
     * Get externals updated field data values.
     *
     * This is a PHPUnit data provider
     *
     * @return array
     */
    public function assertUpdatedFieldDataLoadedCorrect(Field $field)
    {
        $this->assertInstanceOf(
            'eZ\\Publish\\Core\\FieldType\\RichText\\Value',
            $field->value
        );

        $this->assertPropertiesCorrect(
            array(
                'xml' => $this->updatedDOMValue,
            ),
            $field->value
        );
    }

    /**
     * Get field data which will result in errors during update.
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
    public function assertCopiedFieldDataLoadedCorrectly(Field $field)
    {
        $this->assertInstanceOf(
            'eZ\\Publish\\Core\\FieldType\\RichText\\Value',
            $field->value
        );

        $this->assertPropertiesCorrect(
            array(
                'xml' => $this->createdDOMValue,
            ),
            $field->value
        );
    }

    /**
     * Get data to test to hash method.
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
        $xml = new DOMDocument();
        $xml->loadXML(<<<EOT
<?xml version="1.0" encoding="UTF-8"?>
<section xmlns="http://docbook.org/ns/docbook" xmlns:xlink="http://www.w3.org/1999/xlink" xmlns:ezxhtml="http://ez.no/xmlns/ezpublish/docbook/xhtml" xmlns:ezcustom="http://ez.no/xmlns/ezpublish/docbook/custom" version="5.0-variant ezpublish-1.0">
    <title>Some text</title>
    <para>Foobar</para>
</section>
EOT
        );

        return array(
            array(
                new RichTextValue($xml),
                array('xml' => $xml->saveXML()),
            ),
        );
    }

    /**
     * Get expectations for the fromHash call on our field value.
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
                    'xml' => <<<EOT
<?xml version="1.0" encoding="UTF-8"?>
<section xmlns="http://docbook.org/ns/docbook" xmlns:xlink="http://www.w3.org/1999/xlink" xmlns:ezxhtml="http://ez.no/xmlns/ezpublish/docbook/xhtml" xmlns:ezcustom="http://ez.no/xmlns/ezpublish/docbook/custom" version="5.0-variant ezpublish-1.0">
    <title>Some text</title>
    <para>Foobar</para>
</section>

EOT
                ),
            ),
        );
    }

    /**
     * @dataProvider provideFromHashData
     * @todo: Requires correct registered FieldTypeService, needs to be
     *        maintained!
     */
    public function testFromHash($hash, $expectedValue = null)
    {
        $richTextValue = $this
                ->getRepository()
                ->getFieldTypeService()
                ->getFieldType($this->getTypeName())
                ->fromHash($hash);
        $this->assertInstanceOf(
            'eZ\\Publish\\Core\\FieldType\\RichText\\Value',
            $richTextValue
        );
        $this->assertInstanceOf('DOMDocument', $richTextValue->xml);

        $this->assertEquals($hash['xml'], (string)$richTextValue);
    }

    public function providerForTestIsEmptyValue()
    {
        $xml = <<<EOT
<?xml version="1.0" encoding="UTF-8"?>
<section xmlns="http://docbook.org/ns/docbook" xmlns:xlink="http://www.w3.org/1999/xlink" xmlns:ezxhtml="http://ez.no/xmlns/ezpublish/docbook/xhtml" xmlns:ezcustom="http://ez.no/xmlns/ezpublish/docbook/custom" version="5.0-variant ezpublish-1.0"/>
EOT;

        return array(
            array(new RichTextValue()),
            array(new RichTextValue($xml)),
        );
    }

    public function providerForTestIsNotEmptyValue()
    {
        $xml = <<<EOT
<?xml version="1.0" encoding="UTF-8"?>
<section xmlns="http://docbook.org/ns/docbook" xmlns:xlink="http://www.w3.org/1999/xlink" xmlns:ezxhtml="http://ez.no/xmlns/ezpublish/docbook/xhtml" xmlns:ezcustom="http://ez.no/xmlns/ezpublish/docbook/custom" version="5.0-variant ezpublish-1.0"> </section>
EOT;
        $xml2 = <<<EOT
<?xml version="1.0" encoding="UTF-8"?>
<section xmlns="http://docbook.org/ns/docbook" xmlns:xlink="http://www.w3.org/1999/xlink" xmlns:ezxhtml="http://ez.no/xmlns/ezpublish/docbook/xhtml" xmlns:ezcustom="http://ez.no/xmlns/ezpublish/docbook/custom" version="5.0-variant ezpublish-1.0">
    <para/>
</section>
EOT;

        return array(
            array(
                $this->getValidCreationFieldData(),
            ),
            array(new RichTextValue($xml)),
            array(new RichTextValue($xml2)),
        );
    }

    /**
     * Get data to test remote id conversion.
     *
     * This is a PHP Unit data provider
     *
     * @see testConvertReomoteObjectIdToObjectId()
     */
    public function providerForTestConvertRemoteObjectIdToObjectId()
    {
        $remoteId = '[RemoteId]';
        $objectId = '[ObjectId]';

        return array(
            array(
                // test link
                '<?xml version="1.0" encoding="utf-8"?>
<section xmlns="http://docbook.org/ns/docbook" xmlns:xlink="http://www.w3.org/1999/xlink" xmlns:ezxhtml="http://ez.no/xmlns/ezpublish/docbook/xhtml" xmlns:ezcustom="http://ez.no/xmlns/ezpublish/docbook/custom" version="5.0-variant ezpublish-1.0">
    <para>
        <link xlink:href="ezremote://' . $remoteId . '#fragment">link</link>
    </para>
</section>',
                '<?xml version="1.0" encoding="utf-8"?>
<section xmlns="http://docbook.org/ns/docbook" xmlns:xlink="http://www.w3.org/1999/xlink" xmlns:ezxhtml="http://ez.no/xmlns/ezpublish/docbook/xhtml" xmlns:ezcustom="http://ez.no/xmlns/ezpublish/docbook/custom" version="5.0-variant ezpublish-1.0">
    <para>
        <link xlink:href="ezcontent://' . $objectId . '#fragment">link</link>
    </para>
</section>
',
            ), /*, @TODO adapt and enable when embeds are implemented with remote id support
            array(
                // test embed
            '<?xml version="1.0" encoding="utf-8"?>
<section xmlns="http://docbook.org/ns/docbook" xmlns:xlink="http://www.w3.org/1999/xlink" xmlns:ezxhtml="http://ez.no/xmlns/ezpublish/docbook/xhtml" xmlns:ezcustom="http://ez.no/xmlns/ezpublish/docbook/custom" version="5.0-variant ezpublish-1.0">
    <para>
        <embed view="embed" size="medium" object_remote_id="' . $remoteId . '"/>
    </para>
</section>'
            ,
            '<?xml version="1.0" encoding="utf-8"?>
<section xmlns="http://docbook.org/ns/docbook" xmlns:xlink="http://www.w3.org/1999/xlink" xmlns:ezxhtml="http://ez.no/xmlns/ezpublish/docbook/xhtml" xmlns:ezcustom="http://ez.no/xmlns/ezpublish/docbook/custom" version="5.0-variant ezpublish-1.0">
    <para>
        <embed view="embed" size="medium" object_id="' . $objectId . '"/>
    </para>
</section>'
            ),
            array(
                // test embed-inline
            '<?xml version="1.0" encoding="utf-8"?>
<section xmlns="http://docbook.org/ns/docbook" xmlns:xlink="http://www.w3.org/1999/xlink" xmlns:ezxhtml="http://ez.no/xmlns/ezpublish/docbook/xhtml" xmlns:ezcustom="http://ez.no/xmlns/ezpublish/docbook/custom" version="5.0-variant ezpublish-1.0">
    <para>
        <embed-inline size="medium" object_remote_id="' . $remoteId . '"/>
    </para>
</section>',
                '<?xml version="1.0" encoding="utf-8"?>
<section xmlns="http://docbook.org/ns/docbook" xmlns:xlink="http://www.w3.org/1999/xlink" xmlns:ezxhtml="http://ez.no/xmlns/ezpublish/docbook/xhtml" xmlns:ezcustom="http://ez.no/xmlns/ezpublish/docbook/custom" version="5.0-variant ezpublish-1.0">
    <para>
        <embed-inline size="medium" object_id="' . $objectId . '"/>
    </para>
</section>'
            ),
*/
        );
    }

    /**
     * This tests the conversion from remote_object_id to object_id.
     *
     * @dataProvider providerForTestConvertRemoteObjectIdToObjectId
     */
    public function testConvertRemoteObjectIdToObjectId($test, $expected)
    {
        $repository = $this->getRepository();

        $contentService = $repository->getContentService();
        $contentTypeService = $repository->getContentTypeService();
        $locationService = $repository->getLocationService();

        // Create Type containing RichText Field definition
        $createStruct = $contentTypeService->newContentTypeCreateStruct('test-RichText');
        $createStruct->mainLanguageCode = 'eng-GB';
        $createStruct->remoteId = 'test-RichText-abcdefghjklm9876543210';
        $createStruct->names = array('eng-GB' => 'Test');
        $createStruct->creatorId = $repository->getCurrentUser()->id;
        $createStruct->creationDate = $this->createDateTime();

        $fieldCreate = $contentTypeService->newFieldDefinitionCreateStruct('description', 'ezrichtext');
        $fieldCreate->names = array('eng-GB' => 'Title');
        $fieldCreate->fieldGroup = 'main';
        $fieldCreate->position = 1;
        $fieldCreate->isTranslatable = true;
        $createStruct->addFieldDefinition($fieldCreate);

        $contentGroup = $contentTypeService->loadContentTypeGroupByIdentifier('Content');
        $contentTypeDraft = $contentTypeService->createContentType($createStruct, array($contentGroup));

        $contentTypeService->publishContentTypeDraft($contentTypeDraft);
        $testContentType = $contentTypeService->loadContentType($contentTypeDraft->id);

        // Create a folder for tests
        $createStruct = $contentService->newContentCreateStruct(
            $contentTypeService->loadContentTypeByIdentifier('folder'),
            'eng-GB'
        );

        $createStruct->setField('name', 'Folder Link');
        $draft = $contentService->createContent(
            $createStruct,
            array($locationService->newLocationCreateStruct(2))
        );

        $folder = $contentService->publishVersion(
            $draft->versionInfo
        );

        $objectId = $folder->versionInfo->contentInfo->id;
        $locationId = $folder->versionInfo->contentInfo->mainLocationId;
        $remoteId = $folder->versionInfo->contentInfo->remoteId;

        // Create value to be tested
        $testStruct = $contentService->newContentCreateStruct($testContentType, 'eng-GB');
        $testStruct->setField('description', str_replace('[RemoteId]', $remoteId, $test));
        $test = $contentService->createContent(
            $testStruct,
            array($locationService->newLocationCreateStruct($locationId))
        );

        $this->assertEquals(
            str_replace('[ObjectId]', $objectId, $expected),
            $test->getField('description')->value->xml->saveXML()
        );
    }

    protected function checkSearchEngineSupport()
    {
        if (ltrim(get_class($this->getSetupFactory()), '\\') === 'eZ\\Publish\\API\\Repository\\Tests\\SetupFactory\\Legacy') {
            $this->markTestSkipped(
                "'ezrichtext' field type is not searchable with Legacy Search Engine"
            );
        }
    }

    protected function getValidSearchValueOne()
    {
        return <<<EOT
<?xml version="1.0" encoding="UTF-8"?>
<section xmlns="http://docbook.org/ns/docbook" xmlns:xlink="http://www.w3.org/1999/xlink" xmlns:ezxhtml="http://ez.no/xmlns/ezpublish/docbook/xhtml" xmlns:ezcustom="http://ez.no/xmlns/ezpublish/docbook/custom" version="5.0-variant ezpublish-1.0">
    <para>caution is the path to mediocrity</para>
</section>
EOT;
    }

    protected function getSearchTargetValueOne()
    {
        // ensure case-insensitivity
        return strtoupper('caution is the path to mediocrity');
    }

    protected function getValidSearchValueTwo()
    {
        return <<<EOT
<?xml version="1.0" encoding="UTF-8"?>
<section xmlns="http://docbook.org/ns/docbook" xmlns:xlink="http://www.w3.org/1999/xlink" xmlns:ezxhtml="http://ez.no/xmlns/ezpublish/docbook/xhtml" xmlns:ezcustom="http://ez.no/xmlns/ezpublish/docbook/custom" version="5.0-variant ezpublish-1.0">
    <para>truth suffers from too much analysis</para>
</section>
EOT;
    }

    protected function getSearchTargetValueTwo()
    {
        // ensure case-insensitivity
        return strtoupper('truth suffers from too much analysis');
    }

    protected function getFullTextIndexedFieldData()
    {
        return array(
            array('mediocrity', 'analysis'),
        );
    }
}
