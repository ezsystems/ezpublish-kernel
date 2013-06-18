<?php
/**
 * File containing the NonRedundantFieldSetTest class
 *
 * @copyright Copyright (C) 1999-2013 eZ Systems AS. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License v2
 * @version //autogentag//
 */

namespace eZ\Publish\API\Repository\Tests;

use eZ\Publish\API\Repository\Values\Content\Content;

/**
 * Test case for create and update Content operations in the ContentService with regard to
 * non-redundant set of fields being passed to the storage.
 *
 * These tests depends on TextLine field type being functional.
 *
 * @see eZ\Publish\API\Repository\ContentService
 * @group content
 */
class NonRedundantFieldSetTest extends BaseNonRedundantFieldSetTest
{
    /**
     * Test for the createContent() method.
     *
     * Default values are stored.
     *
     * @see \eZ\Publish\API\Repository\ContentService::createContent()
     * @depends eZ\Publish\API\Repository\Tests\ContentServiceTest::testCreateContent
     * @depends eZ\Publish\API\Repository\Tests\ContentTypeServiceTest::testCreateContentType
     *
     * @return \eZ\Publish\API\Repository\Values\Content\Content
     */
    public function testCreateContentDefaultValues()
    {
        $mainLanguageCode = "eng-US";
        $fieldValues = array(
            "field1" => array( "eng-US" => "new value 1" ),
            "field3" => array( "eng-US" => "new value 3" )
        );

        $content = $this->createTestContent( $mainLanguageCode, $fieldValues );

        $this->assertInstanceOf( "\\eZ\\Publish\\API\\Repository\\Values\\Content\\Content", $content );

        return $content;
    }

    /**
     * Test for the createContent() method.
     *
     * @see \eZ\Publish\API\Repository\ContentService::createContent()
     * @depends eZ\Publish\API\Repository\Tests\NonRedundantFieldSetTest::testCreateContentDefaultValues
     *
     * @param \eZ\Publish\API\Repository\Values\Content\Content $content
     */
    public function testCreateContentDefaultValuesFields( Content $content )
    {
        $this->assertCount( 1, $content->versionInfo->languageCodes );
        $this->assertContains( "eng-US", $content->versionInfo->languageCodes );
        $this->assertCount( 4, $content->getFields() );

        // eng-US
        $this->assertEquals( "new value 1", $content->getFieldValue( "field1", "eng-US" ) );
        $this->assertEquals( "default value 2", $content->getFieldValue( "field2", "eng-US" ) );
        $this->assertEquals( "new value 3", $content->getFieldValue( "field3", "eng-US" ) );
        $this->assertEquals( "default value 4", $content->getFieldValue( "field4", "eng-US" ) );
    }

    /**
     * Test for the createContent() method.
     *
     * Creating fields with empty values, no values being passed to storage.
     *
     * @see \eZ\Publish\API\Repository\ContentService::createContent()
     * @depends eZ\Publish\API\Repository\Tests\ContentServiceTest::testCreateContent
     * @depends eZ\Publish\API\Repository\Tests\ContentTypeServiceTest::testCreateContentType
     *
     * @return \eZ\Publish\API\Repository\Values\Content\Content
     */
    public function testCreateContentEmptyValues()
    {
        $mainLanguageCode = "eng-US";
        $fieldValues = array(
            "field2" => array( "eng-US" => null ),
            "field4" => array( "eng-US" => null )
        );

        $content = $this->createTestContent( $mainLanguageCode, $fieldValues );

        $this->assertInstanceOf( "\\eZ\\Publish\\API\\Repository\\Values\\Content\\Content", $content );

        return $content;
    }

    /**
     * Test for the createContent() method.
     *
     * @see \eZ\Publish\API\Repository\ContentService::createContent()
     * @depends eZ\Publish\API\Repository\Tests\NonRedundantFieldSetTest::testCreateContentEmptyValues
     *
     * @param \eZ\Publish\API\Repository\Values\Content\Content $content
     */
    public function testCreateContentEmptyValuesFields( Content $content )
    {
        $this->assertCount( 1, $content->versionInfo->languageCodes );
        $this->assertContains( "eng-US", $content->versionInfo->languageCodes );
        $this->assertCount( 4, $content->getFields() );

        // eng-US
        $this->assertContains( "eng-US", $content->versionInfo->languageCodes );
        $this->assertNull( $content->getFieldValue( "field1", "eng-US" ) );
        $this->assertNull( $content->getFieldValue( "field2", "eng-US" ) );
        $this->assertNull( $content->getFieldValue( "field3", "eng-US" ) );
        $this->assertNull( $content->getFieldValue( "field4", "eng-US" ) );
    }

    /**
     * Test for the createContent() method.
     *
     * Creating fields with empty values, no values being passed to storage.
     * Case where additional language is not stored.
     *
     * @see \eZ\Publish\API\Repository\ContentService::createContent()
     * @depends eZ\Publish\API\Repository\Tests\ContentServiceTest::testCreateContent
     * @depends eZ\Publish\API\Repository\Tests\ContentTypeServiceTest::testCreateContentType
     *
     * @return \eZ\Publish\API\Repository\Values\Content\Content
     */
    public function testCreateContentEmptyValuesTranslationNotStored()
    {
        $mainLanguageCode = "eng-US";
        $fieldValues = array(
            "field2" => array( "eng-US" => null ),
            "field4" => array( "eng-US" => null, "ger-DE" => null )
        );

        $content = $this->createTestContent( $mainLanguageCode, $fieldValues );

        $this->assertInstanceOf( "\\eZ\\Publish\\API\\Repository\\Values\\Content\\Content", $content );

        return $content;
    }

    /**
     * Test for the createContent() method.
     *
     * @see \eZ\Publish\API\Repository\ContentService::createContent()
     * @depends eZ\Publish\API\Repository\Tests\NonRedundantFieldSetTest::testCreateContentEmptyValuesTranslationNotStored
     *
     * @param \eZ\Publish\API\Repository\Values\Content\Content $content
     */
    public function testCreateContentEmptyValuesTranslationNotStoredFields( Content $content )
    {
        $this->assertCount( 1, $content->versionInfo->languageCodes );
        $this->assertContains( "eng-US", $content->versionInfo->languageCodes );
        $this->assertCount( 4, $content->getFields() );

        // eng-US
        $this->assertContains( "eng-US", $content->versionInfo->languageCodes );
        $this->assertNull( $content->getFieldValue( "field1", "eng-US" ) );
        $this->assertNull( $content->getFieldValue( "field2", "eng-US" ) );
        $this->assertNull( $content->getFieldValue( "field3", "eng-US" ) );
        $this->assertNull( $content->getFieldValue( "field4", "eng-US" ) );

        // ger-DE is not stored!
        $this->assertNotContains( "ger-DE", $content->versionInfo->languageCodes );
    }

    /**
     * Test for the createContent() method.
     *
     * Creating with two languages, main language is always stored (even with all values being empty).
     *
     * @see \eZ\Publish\API\Repository\ContentService::createContent()
     * @depends eZ\Publish\API\Repository\Tests\ContentServiceTest::testCreateContent
     * @depends eZ\Publish\API\Repository\Tests\ContentTypeServiceTest::testCreateContentType
     *
     * @return \eZ\Publish\API\Repository\Values\Content\Content
     */
    public function testCreateContentTwoLanguagesMainTranslationStored()
    {
        $mainLanguageCode = "eng-US";
        $fieldValues = array(
            "field2" => array( "eng-US" => null ),
            "field4" => array( "eng-US" => null, "ger-DE" => "new ger-DE value 4" )
        );

        $content = $this->createTestContent( $mainLanguageCode, $fieldValues );

        $this->assertInstanceOf( "\\eZ\\Publish\\API\\Repository\\Values\\Content\\Content", $content );

        return $content;
    }

    /**
     * Test for the createContent() method.
     *
     * @see \eZ\Publish\API\Repository\ContentService::createContent()
     * @depends eZ\Publish\API\Repository\Tests\NonRedundantFieldSetTest::testCreateContentTwoLanguagesMainTranslationStored
     *
     * @param \eZ\Publish\API\Repository\Values\Content\Content $content
     */
    public function testCreateContentTwoLanguagesMainTranslationStoredFields( Content $content )
    {
        $this->assertCount( 2, $content->versionInfo->languageCodes );
        $this->assertContains( "ger-DE", $content->versionInfo->languageCodes );
        $this->assertContains( "eng-US", $content->versionInfo->languageCodes );
        $this->assertCount( 8, $content->getFields() );

        // eng-US
        $this->assertContains( "eng-US", $content->versionInfo->languageCodes );
        $this->assertNull( $content->getFieldValue( "field1", "eng-US" ) );
        $this->assertNull( $content->getFieldValue( "field2", "eng-US" ) );
        $this->assertNull( $content->getFieldValue( "field3", "eng-US" ) );
        $this->assertNull( $content->getFieldValue( "field4", "eng-US" ) );

        // ger-DE
        $this->assertNull( $content->getFieldValue( "field1", "ger-DE" ) );
        $this->assertNull( $content->getFieldValue( "field2", "ger-DE" ) );
        $this->assertNull( $content->getFieldValue( "field3", "ger-DE" ) );
        $this->assertEquals( "new ger-DE value 4", $content->getFieldValue( "field4", "ger-DE" ) );
    }

    /**
     * Test for the createContent() method.
     *
     * Creating with two languages, second (not main one) language with empty values, causing no fields
     * for it being passed to the storage. Second language will not be stored.
     *
     * @see \eZ\Publish\API\Repository\ContentService::createContent()
     * @depends eZ\Publish\API\Repository\Tests\ContentServiceTest::testCreateContent
     * @depends eZ\Publish\API\Repository\Tests\ContentTypeServiceTest::testCreateContentType
     *
     * @return \eZ\Publish\API\Repository\Values\Content\Content
     */
    public function testCreateContentTwoLanguagesSecondTranslationNotStored()
    {
        $mainLanguageCode = "eng-US";
        $fieldValues = array(
            "field4" => array( "ger-DE" => null )
        );

        $content = $this->createTestContent( $mainLanguageCode, $fieldValues );

        $this->assertInstanceOf( "\\eZ\\Publish\\API\\Repository\\Values\\Content\\Content", $content );

        return $content;
    }

    /**
     * Test for the createContent() method.
     *
     * @see \eZ\Publish\API\Repository\ContentService::createContent()
     * @depends eZ\Publish\API\Repository\Tests\NonRedundantFieldSetTest::testCreateContentTwoLanguagesSecondTranslationNotStored
     *
     * @param \eZ\Publish\API\Repository\Values\Content\Content $content
     */
    public function testCreateContentTwoLanguagesSecondTranslationNotStoredFields( Content $content )
    {
        $this->assertCount( 1, $content->versionInfo->languageCodes );
        $this->assertContains( "eng-US", $content->versionInfo->languageCodes );
        $this->assertCount( 4, $content->getFields() );

        // eng-US
        $this->assertNull( $content->getFieldValue( "field1", "eng-US" ) );
        $this->assertEquals( "default value 2", $content->getFieldValue( "field2", "eng-US" ) );
        $this->assertNull( $content->getFieldValue( "field3", "eng-US" ) );
        $this->assertEquals( "default value 4", $content->getFieldValue( "field4", "eng-US" ) );

        // ger-DE is not stored!
        $this->assertNotContains( "ger-DE", $content->versionInfo->languageCodes );
    }

    /**
     * Test for the createContent() method.
     *
     * Creating with no fields in struct, using only default values.
     *
     * @see \eZ\Publish\API\Repository\ContentService::createContent()
     * @depends eZ\Publish\API\Repository\Tests\ContentServiceTest::testCreateContent
     * @depends eZ\Publish\API\Repository\Tests\ContentTypeServiceTest::testCreateContentType
     *
     * @return \eZ\Publish\API\Repository\Values\Content\Content
     */
    public function testCreateContentDefaultValuesNoStructFields()
    {
        $mainLanguageCode = "eng-US";
        $fieldValues = array();

        $content = $this->createTestContent( $mainLanguageCode, $fieldValues );

        $this->assertInstanceOf( "\\eZ\\Publish\\API\\Repository\\Values\\Content\\Content", $content );

        return $content;
    }

    /**
     * Test for the createContent() method.
     *
     * @see \eZ\Publish\API\Repository\ContentService::createContent()
     * @depends eZ\Publish\API\Repository\Tests\NonRedundantFieldSetTest::testCreateContentDefaultValuesNoStructFields
     *
     * @param \eZ\Publish\API\Repository\Values\Content\Content $content
     */
    public function testCreateContentDefaultValuesNoStructFieldsFields( Content $content )
    {
        $this->assertCount( 1, $content->versionInfo->languageCodes );
        $this->assertContains( "eng-US", $content->versionInfo->languageCodes );
        $this->assertCount( 4, $content->getFields() );

        // eng-US
        $this->assertNull( $content->getFieldValue( "field1", "eng-US" ) );
        $this->assertEquals( "default value 2", $content->getFieldValue( "field2", "eng-US" ) );
        $this->assertNull( $content->getFieldValue( "field3", "eng-US" ) );
        $this->assertEquals( "default value 4", $content->getFieldValue( "field4", "eng-US" ) );
    }

    /**
     * Test for the createContent() method.
     *
     * Creating in two languages with no given field values for main language.
     *
     * @see \eZ\Publish\API\Repository\ContentService::createContent()
     * @depends eZ\Publish\API\Repository\Tests\ContentServiceTest::testCreateContent
     * @depends eZ\Publish\API\Repository\Tests\ContentTypeServiceTest::testCreateContentType
     *
     * @return \eZ\Publish\API\Repository\Values\Content\Content
     */
    public function testCreateContentTwoLanguagesNoValuesForMainLanguage()
    {
        $mainLanguageCode = "eng-US";
        $fieldValues = array(
            "field4" => array( "ger-DE" => "new value 4" )
        );

        $content = $this->createTestContent( $mainLanguageCode, $fieldValues );

        $this->assertInstanceOf( "\\eZ\\Publish\\API\\Repository\\Values\\Content\\Content", $content );

        return $content;
    }

    /**
     * Test for the createContent() method.
     *
     * @see \eZ\Publish\API\Repository\ContentService::createContent()
     * @depends eZ\Publish\API\Repository\Tests\NonRedundantFieldSetTest::testCreateContentTwoLanguagesNoValuesForMainLanguage
     *
     * @param \eZ\Publish\API\Repository\Values\Content\Content $content
     */
    public function testCreateContentTwoLanguagesNoValuesForMainLanguageFields( Content $content )
    {
        $this->assertCount( 2, $content->versionInfo->languageCodes );
        $this->assertContains( "ger-DE", $content->versionInfo->languageCodes );
        $this->assertContains( "eng-US", $content->versionInfo->languageCodes );
        $this->assertCount( 8, $content->getFields() );

        // eng-US
        $this->assertNull( $content->getFieldValue( "field1", "eng-US" ) );
        $this->assertEquals( "default value 2", $content->getFieldValue( "field2", "eng-US" ) );
        $this->assertNull( $content->getFieldValue( "field3", "eng-US" ) );
        $this->assertEquals( "default value 4", $content->getFieldValue( "field4", "eng-US" ) );

        // ger-DE
        $this->assertNull( $content->getFieldValue( "field1", "ger-DE" ) );
        $this->assertEquals( "default value 2", $content->getFieldValue( "field2", "ger-DE" ) );
        $this->assertNull( $content->getFieldValue( "field3", "ger-DE" ) );
        $this->assertEquals( "new value 4", $content->getFieldValue( "field4", "ger-DE" ) );
    }

    /**
     * Test for the updateContent() method.
     *
     * Testing update with new language:
     *  - value for new language is copied from value in main language
     *  - value for new language is empty
     *  - value for new language is not empty
     *
     * @see \eZ\Publish\API\Repository\ContentService::createContent()
     * @depends eZ\Publish\API\Repository\Tests\ContentServiceTest::testCreateContent
     * @depends eZ\Publish\API\Repository\Tests\ContentTypeServiceTest::testCreateContentType
     *
     * @return \eZ\Publish\API\Repository\Values\Content\Content
     */
    public function testUpdateContentWithNewLanguage()
    {
        $initialLanguageCode = "ger-DE";
        $fieldValues = array(
            "field4" => array( "ger-DE" => "new value 4" )
        );

        $content = $this->updateTestContent( $initialLanguageCode, $fieldValues );
        $this->assertInstanceOf( "\\eZ\\Publish\\API\\Repository\\Values\\Content\\Content", $content );

        return $content;
    }

    /**
     * Test for the updateContent() method.
     *
     * @see \eZ\Publish\API\Repository\ContentService::updateContent()
     * @depends eZ\Publish\API\Repository\Tests\NonRedundantFieldSetTest::testUpdateContentWithNewLanguage
     *
     * @param \eZ\Publish\API\Repository\Values\Content\Content $content
     */
    public function testUpdateContentWithNewLanguageFields( Content $content )
    {
        $this->assertCount( 2, $content->versionInfo->languageCodes );
        $this->assertContains( "ger-DE", $content->versionInfo->languageCodes );
        $this->assertContains( "eng-US", $content->versionInfo->languageCodes );
        $this->assertCount( 8, $content->getFields() );

        // eng-US
        $this->assertEquals( "value 1", $content->getFieldValue( "field1", "eng-US" ) );
        $this->assertEquals( "value 2", $content->getFieldValue( "field2", "eng-US" ) );
        $this->assertEquals( "value 3", $content->getFieldValue( "field3", "eng-US" ) );
        $this->assertEquals( "value 4", $content->getFieldValue( "field4", "eng-US" ) );

        // ger-DE
        $this->assertEquals( "value 1", $content->getFieldValue( "field1", "ger-DE" ) );
        $this->assertEquals( "value 2", $content->getFieldValue( "field2", "ger-DE" ) );
        $this->assertNull( $content->getFieldValue( "field3", "ger-DE" ) );
        $this->assertEquals( "new value 4", $content->getFieldValue( "field4", "ger-DE" ) );
    }

    /**
     * Test for the updateContent() method.
     *
     * Testing update of existing language and adding a new language:
     *  - value for new language is copied from value in main language
     *  - value for new language is empty
     *  - value for new language is not empty
     *  - existing language value updated with empty value
     *  - existing language value not changed
     *
     * @see \eZ\Publish\API\Repository\ContentService::createContent()
     * @depends eZ\Publish\API\Repository\Tests\ContentServiceTest::testCreateContent
     * @depends eZ\Publish\API\Repository\Tests\ContentTypeServiceTest::testCreateContentType
     *
     * @return \eZ\Publish\API\Repository\Values\Content\Content
     */
    public function testUpdateContentWithNewLanguageVariant()
    {
        $initialLanguageCode = "ger-DE";
        $fieldValues = array(
            "field1" => array( "eng-US" => null ),
            "field4" => array( "ger-DE" => "new value 4" )
        );

        $content = $this->updateTestContent( $initialLanguageCode, $fieldValues );
        $this->assertInstanceOf( "\\eZ\\Publish\\API\\Repository\\Values\\Content\\Content", $content );

        return $content;
    }

    /**
     * Test for the updateContent() method.
     *
     * @see \eZ\Publish\API\Repository\ContentService::updateContent()
     * @depends eZ\Publish\API\Repository\Tests\NonRedundantFieldSetTest::testUpdateContentWithNewLanguageVariant
     *
     * @param \eZ\Publish\API\Repository\Values\Content\Content $content
     */
    public function testUpdateContentWithNewLanguageVariantFields( Content $content )
    {
        $this->assertCount( 2, $content->versionInfo->languageCodes );
        $this->assertContains( "ger-DE", $content->versionInfo->languageCodes );
        $this->assertContains( "eng-US", $content->versionInfo->languageCodes );
        $this->assertCount( 8, $content->getFields() );

        // eng-US
        $this->assertNull( $content->getFieldValue( "field1", "eng-US" ) );
        $this->assertEquals( "value 2", $content->getFieldValue( "field2", "eng-US" ) );
        $this->assertEquals( "value 3", $content->getFieldValue( "field3", "eng-US" ) );
        $this->assertEquals( "value 4", $content->getFieldValue( "field4", "eng-US" ) );

        // ger-DE
        $this->assertNull( $content->getFieldValue( "field1", "ger-DE" ) );
        $this->assertEquals( "value 2", $content->getFieldValue( "field2", "ger-DE" ) );
        $this->assertNull( $content->getFieldValue( "field3", "ger-DE" ) );
        $this->assertEquals( "new value 4", $content->getFieldValue( "field4", "ger-DE" ) );
    }

    /**
     * Test for the updateContent() method.
     *
     * Updating with with new language and no field values given in the update struct.
     *
     * @see \eZ\Publish\API\Repository\ContentService::createContent()
     * @depends eZ\Publish\API\Repository\Tests\ContentServiceTest::testCreateContent
     * @depends eZ\Publish\API\Repository\Tests\ContentTypeServiceTest::testCreateContentType
     *
     * @return \eZ\Publish\API\Repository\Values\Content\Content
     */
    public function testUpdateContentWithNewLanguageNoValues()
    {
        $initialLanguageCode = "ger-DE";
        $fieldValues = array();

        $content = $this->updateTestContent( $initialLanguageCode, $fieldValues );
        $this->assertInstanceOf( "\\eZ\\Publish\\API\\Repository\\Values\\Content\\Content", $content );

        return $content;
    }

    /**
     * Test for the updateContent() method.
     *
     * @see \eZ\Publish\API\Repository\ContentService::updateContent()
     * @depends eZ\Publish\API\Repository\Tests\NonRedundantFieldSetTest::testUpdateContentWithNewLanguageNoValues
     *
     * @param \eZ\Publish\API\Repository\Values\Content\Content $content
     */
    public function testUpdateContentWithNewLanguageNoValuesFields( Content $content )
    {
        $this->assertCount( 2, $content->versionInfo->languageCodes );
        $this->assertContains( "ger-DE", $content->versionInfo->languageCodes );
        $this->assertContains( "eng-US", $content->versionInfo->languageCodes );
        $this->assertCount( 8, $content->getFields() );

        // eng-US
        $this->assertEquals( "value 1", $content->getFieldValue( "field1", "eng-US" ) );
        $this->assertEquals( "value 2", $content->getFieldValue( "field2", "eng-US" ) );
        $this->assertEquals( "value 3", $content->getFieldValue( "field3", "eng-US" ) );
        $this->assertEquals( "value 4", $content->getFieldValue( "field4", "eng-US" ) );

        // ger-DE
        $this->assertEquals( "value 1", $content->getFieldValue( "field1", "ger-DE" ) );
        $this->assertEquals( "value 2", $content->getFieldValue( "field2", "ger-DE" ) );
        $this->assertNull( $content->getFieldValue( "field3", "ger-DE" ) );
        $this->assertEquals( "default value 4", $content->getFieldValue( "field4", "ger-DE" ) );
    }

    /**
     * Test for the updateContent() method.
     *
     * Updating with two languages, initial language is always stored (even with all values being empty).
     *
     * @see \eZ\Publish\API\Repository\ContentService::createContent()
     * @depends eZ\Publish\API\Repository\Tests\ContentServiceTest::testCreateContent
     * @depends eZ\Publish\API\Repository\Tests\ContentTypeServiceTest::testCreateContentType
     *
     * @return \eZ\Publish\API\Repository\Values\Content\Content
     */
    public function testUpdateContentWithTwoLanguagesInitialLanguageTranslationNotCreated()
    {
        $initialLanguageCode = "ger-DE";
        $fieldValues = array(
            "field1" => array( "eng-US" => null ),
            "field2" => array( "eng-US" => null ),
            "field4" => array( "ger-DE" => null )
        );

        $content = $this->updateTestContent( $initialLanguageCode, $fieldValues );
        $this->assertInstanceOf( "\\eZ\\Publish\\API\\Repository\\Values\\Content\\Content", $content );

        return $content;
    }

    /**
     * Test for the updateContent() method.
     *
     * @see \eZ\Publish\API\Repository\ContentService::updateContent()
     * @depends eZ\Publish\API\Repository\Tests\NonRedundantFieldSetTest::testUpdateContentWithTwoLanguagesInitialLanguageTranslationNotCreated
     *
     * @param \eZ\Publish\API\Repository\Values\Content\Content $content
     */
    public function testUpdateContentWithTwoLanguagesInitialLanguageTranslationNotCreatedFields( Content $content )
    {
        $this->assertCount( 2, $content->versionInfo->languageCodes );
        $this->assertContains( "ger-DE", $content->versionInfo->languageCodes );
        $this->assertContains( "eng-US", $content->versionInfo->languageCodes );
        $this->assertCount( 8, $content->getFields() );

        // eng-US
        $this->assertNull( $content->getFieldValue( "field1", "eng-US" ) );
        $this->assertNull( $content->getFieldValue( "field2", "eng-US" ) );
        $this->assertEquals( "value 3", $content->getFieldValue( "field3", "eng-US" ) );
        $this->assertEquals( "value 4", $content->getFieldValue( "field4", "eng-US" ) );

        // ger-DE
        $this->assertNull( $content->getFieldValue( "field1", "ger-DE" ) );
        $this->assertNull( $content->getFieldValue( "field2", "ger-DE" ) );
        $this->assertNull( $content->getFieldValue( "field3", "ger-DE" ) );
        $this->assertNull( $content->getFieldValue( "field4", "ger-DE" ) );
    }
}
