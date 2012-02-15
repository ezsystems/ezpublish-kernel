<?php
/**
 * File containing the ContentTypeCreateStructTest class
 *
 * @copyright Copyright (C) 1999-2012 eZ Systems AS. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License v2
 * @version //autogentag//
 */

namespace eZ\Publish\API\Repository\Tests\Values\ContentType;

use \eZ\Publish\API\Repository\Tests\BaseTest;

/**
 * Test case for operations in the ContentTypeCreateStruct using in memory storage.
 *
 * @see eZ\Publish\API\Repository\Values\ContentType\ContentTypeCreateStruct
 */
class ContentTypeCreateStructTest extends BaseTest
{
    /**
     * Test for the setName() method.
     *
     * @return void
     * @see \eZ\Publish\API\Repository\Values\ContentType\ContentTypeCreateStruct::setName($name, $language)
     * @dep_ends eZ\Publish\API\Repository\Tests\ContentTypeServiceTest::testNewContentTypeCreate
     */
    public function testSetNameWithLanguageParameter()
    {
        $repository =  $this->getRepository();

        /* BEGIN: Use Case */
        $contentTypeService = $repository->getContentTypeService();

        $typeCreate = $contentTypeService->newContentTypeCreateStruct(
            'new-type'
        );

        $typeCreate->setName( 'erweiterter-float', 'de_DE' );
        /* END: Use Case */

        $this->assertEquals(
            1,
            count( $typeCreate->names )
        );
        $this->assertEquals(
            'erweiterter-float',
            $typeCreate->names['de_DE']
        );
    }

    /**
     * Test for the setName() method with magic access.
     *
     * @return void
     * @see \eZ\Publish\API\Repository\Values\ContentType\ContentTypeCreateStruct::setName($name, $language)
     * @depends eZ\Publish\API\Repository\Tests\Values\ContentType\ContentTypeCreateStructTest::testSetNameWithLanguageParameter
     */
    public function testSetNameWithLanguageParameterMagicAccess()
    {
        $repository =  $this->getRepository();

        /* BEGIN: Use Case */
        $contentTypeService = $repository->getContentTypeService();

        $typeCreate = $contentTypeService->newContentTypeCreateStruct(
            'new-type'
        );

        $typeCreate->names['de_DE'] = 'erweiterter-float';
        /* END: Use Case */

        $this->assertEquals(
            1,
            count( $typeCreate->names )
        );
        $this->assertEquals(
            'erweiterter-float',
            $typeCreate->names['de_DE']
        );
    }

    /**
     * Test for the setName() method.
     *
     * @return void
     * @see \eZ\Publish\API\Repository\Values\ContentType\ContentTypeCreateStruct::setName()
     * @depends eZ\Publish\API\Repository\Tests\Values\ContentType\ContentTypeCreateStructTest::testSetNameWithLanguageParameter
     */
    public function testSetName()
    {
        $repository =  $this->getRepository();

        /* BEGIN: Use Case */
        $contentTypeService = $repository->getContentTypeService();

        $typeCreate = $contentTypeService->newContentTypeCreateStruct(
            'new-type'
        );
        $typeCreate->mainLanguageCode = 'en_US';

        // Set for en_US
        $typeCreate->setName( 'extensive-float' );
        /* END: Use Case */

        $this->assertEquals(
            'extensive-float',
            $typeCreate->names['en_US']
        );
    }

    /**
     * Test for the setName() method with magic access.
     *
     * @return void
     * @see \eZ\Publish\API\Repository\Values\ContentType\ContentTypeCreateStruct::setName()
     * @depends eZ\Publish\API\Repository\Tests\Values\ContentType\ContentTypeCreateStructTest::testSetName
     */
    public function testSetNameMagicAccess()
    {
        $repository =  $this->getRepository();

        /* BEGIN: Use Case */
        $contentTypeService = $repository->getContentTypeService();

        $typeCreate = $contentTypeService->newContentTypeCreateStruct(
            'new-type'
        );
        $typeCreate->mainLanguageCode = 'en_US';

        // Set for en_US
        $typeCreate->name = 'extensive-float';
        /* END: Use Case */

        $this->assertEquals(
            'extensive-float',
            $typeCreate->names['en_US']
        );
    }

    /**
     * Test for the setDescription() method.
     *
     * @return void
     * @see \eZ\Publish\API\Repository\Values\ContentType\ContentTypeCreateStruct::setDescription($description, $language)
     * 
     */
    public function testSetDescriptionWithLanguageParameter()
    {
        $repository =  $this->getRepository();

        /* BEGIN: Use Case */
        $contentTypeService = $repository->getContentTypeService();

        $typeCreate = $contentTypeService->newContentTypeCreateStruct(
            'new-type'
        );

        $typeCreate->setDescription( 'Dieses Float ist erweitert', 'de_DE' );
        /* END: Use Case */

        $this->assertEquals(
            1,
            count( $typeCreate->descriptions )
        );
        $this->assertEquals(
            'Dieses Float ist erweitert',
            $typeCreate->descriptions['de_DE']
        );
    }

    /**
     * Test for the setDescription() method magic access.
     *
     * @return void
     * @see \eZ\Publish\API\Repository\Values\ContentType\ContentTypeCreateStruct::setDescription($description, $language)
     * @depends eZ\Publish\API\Repository\Tests\Values\ContentType\ContentTypeCreateStructTest::testSetDescriptionWithLanguageParameter
     */
    public function testSetDescriptionWithLanguageParameterMagicAccess()
    {
        $repository =  $this->getRepository();

        /* BEGIN: Use Case */
        $contentTypeService = $repository->getContentTypeService();

        $typeCreate = $contentTypeService->newContentTypeCreateStruct(
            'new-type'
        );

        $typeCreate->descriptions['de_DE'] = 'Dieses Float ist erweitert';
        /* END: Use Case */

        $this->assertEquals(
            1,
            count( $typeCreate->descriptions )
        );
        $this->assertEquals(
            'Dieses Float ist erweitert',
            $typeCreate->descriptions['de_DE']
        );
    }

    /**
     * Test for the setDescription() method.
     *
     * @return void
     * @see \eZ\Publish\API\Repository\Values\ContentType\ContentTypeCreateStruct::setDescription()
     * 
     */
    public function testSetDescription()
    {
        $repository =  $this->getRepository();

        /* BEGIN: Use Case */
        $contentTypeService = $repository->getContentTypeService();

        $typeCreate = $contentTypeService->newContentTypeCreateStruct(
            'new-type'
        );
        $typeCreate->mainLanguageCode = 'en_US';

        $typeCreate->setDescription( 'This float is extended' );
        /* END: Use Case */

        $this->assertEquals(
            1,
            count( $typeCreate->descriptions )
        );
        $this->assertEquals(
            'This float is extended',
            $typeCreate->descriptions['en_US']
        );
    }

    /**
     * Test for the setDescription() method magic access.
     *
     * @return void
     * @see \eZ\Publish\API\Repository\Values\ContentType\ContentTypeCreateStruct::setDescription()
     * @depends eZ\Publish\API\Repository\Tests\Values\ContentType\ContentTypeCreateStructTest::testSetDescription
     */
    public function testSetDescriptionMagicAccess()
    {
        $repository =  $this->getRepository();

        /* BEGIN: Use Case */
        $contentTypeService = $repository->getContentTypeService();

        $typeCreate = $contentTypeService->newContentTypeCreateStruct(
            'new-type'
        );
        $typeCreate->mainLanguageCode = 'en_US';

        $typeCreate->description = 'This float is extended';
        /* END: Use Case */

        $this->assertEquals(
            1,
            count( $typeCreate->descriptions )
        );
        $this->assertEquals(
            'This float is extended',
            $typeCreate->descriptions['en_US']
        );
    }

    /**
     * Test for the addFieldDefinition() method.
     *
     * @return void
     * @see \eZ\Publish\API\Repository\Values\ContentType\ContentTypeCreateStruct::addFieldDefinition()
     * 
     */
    public function testAddFieldDefinition()
    {
        $repository =  $this->getRepository();

        /* BEGIN: Use Case */
        $contentTypeService = $repository->getContentTypeService();

        $typeCreate = $contentTypeService->newContentTypeCreateStruct(
            'new-type'
        );

        $firstField = $contentTypeService->newFieldDefinitionCreateStruct(
            'title', 'string'
        );
        $typeCreate->addFieldDefinition(
            $firstField
        );

        /* END: Use Case */

        $this->assertEquals(
            1,
            count( $typeCreate->fieldDefinitions )
        );
        $this->assertEquals(
            $firstField,
            $typeCreate->fieldDefinitions[0]
        );
    }

    /**
     * Test for the addFieldDefinition() method with magic access.
     *
     * @return void
     * @see \eZ\Publish\API\Repository\Values\ContentType\ContentTypeCreateStruct::addFieldDefinition()
     * @depends eZ\Publish\API\Repository\Tests\Values\ContentType\ContentTypeCreateStructTest::testAddFieldDefinition
     */
    public function testAddFieldDefinitionMagicAccess()
    {
        $repository =  $this->getRepository();

        /* BEGIN: Use Case */
        $contentTypeService = $repository->getContentTypeService();

        $typeCreate = $contentTypeService->newContentTypeCreateStruct(
            'new-type'
        );

        $firstField = $contentTypeService->newFieldDefinitionCreateStruct(
            'title', 'string'
        );
        $typeCreate->fieldDefinitions[] = $firstField;

        /* END: Use Case */

        $this->assertEquals(
            1,
            count( $typeCreate->fieldDefinitions )
        );
        $this->assertEquals(
            $firstField,
            $typeCreate->fieldDefinitions[0]
        );
    }
}
