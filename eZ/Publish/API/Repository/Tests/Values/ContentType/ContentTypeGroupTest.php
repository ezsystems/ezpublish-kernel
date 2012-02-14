<?php
/**
 * File containing the ContentTypeGroupTest class
 *
 * @copyright Copyright (C) 1999-2012 eZ Systems AS. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License v2
 * @version //autogentag//
 */

namespace eZ\Publish\API\Repository\Tests\Values\ContentType;

use \eZ\Publish\API\Repository\Tests\BaseTest;

/**
 * Test case for operations in the ContentTypeGroup using in memory storage.
 *
 * @see eZ\Publish\API\Repository\Values\ContentType\ContentTypeGroup
 */
class ContentTypeGroupTest extends BaseTest
{
    /**
     * Test for the getNames() method.
     *
     * @return void
     * @see \eZ\Publish\API\Repository\Values\ContentType\ContentTypeGroup::getNames()
     * @dep_ends eZ\Publish\API\Repository\Tests\ContentTypeServiceTest::testCreateContentTypeGroup
     */
    public function testGetNames()
    {
        $repository = $this->getRepository();

        $this->createGroup();

        /* BEGIN: Use Case */
        $contentTypeService = $repository->getContentTypeService();

        $group = $contentTypeService->loadContentTypeGroupByIdentifier( 'new-group' );

        $names = $group->getNames();
        /* END: Use Case */

        $this->assertInternalType( 'array', $names );

        return $names;
    }

    /**
     * Test for the getNames() method.
     *
     * @return void
     * @see \eZ\Publish\API\Repository\Values\ContentType\ContentTypeGroup::getNames()
     * @depends eZ\Publish\API\Repository\Tests\Values\ContentType\ContentTypeGroupTest::testGetNames
     */
    public function testGetNamesContent( $names )
    {
        $this->assertEquals( 2, count( $names ) );
        $this->assertEquals( 'Ein Name', $names['de_DE'] );
        $this->assertEquals( 'A name', $names['en_US'] );
    }

    /**
     * Test for the getName() method.
     *
     * @return void
     * @see \eZ\Publish\API\Repository\Values\ContentType\ContentTypeGroup::getName()
     * @dep_ends eZ\Publish\API\Repository\Tests\ContentTypeServiceTest::testCreateContentTypeGroup
     */
    public function testGetName()
    {
        $repository = $this->getRepository();

        $this->createGroup();

        /* BEGIN: Use Case */
        $contentTypeService = $repository->getContentTypeService();

        $group = $contentTypeService->loadContentTypeGroupByIdentifier( 'new-group' );

        $germanName = $group->getName( 'de_DE' );
        /* END: Use Case */

        $this->assertEquals( 'Ein Name', $germanName );
    }

    /**
     * Test for the getName() method in a magic way.
     *
     * @return void
     * @see \eZ\Publish\API\Repository\Values\ContentType\ContentTypeGroup::getName()
     * @dep_ends eZ\Publish\API\Repository\Tests\ContentTypeServiceTest::testCreateContentTypeGroup
     */
    public function testGetNameMagic()
    {
        $repository = $this->getRepository();

        $this->createGroup();

        /* BEGIN: Use Case */
        $contentTypeService = $repository->getContentTypeService();

        $group = $contentTypeService->loadContentTypeGroupByIdentifier( 'new-group' );

        $germanName = $group->names['de_DE'];
        /* END: Use Case */

        $this->assertEquals( 'Ein Name', $germanName );
    }

    /**
     * Test for magic write access to names.
     *
     * @return void
     * @see \eZ\Publish\API\Repository\Values\ContentType\ContentTypeGroup::getName()
     * @expectedException eZ\Publish\API\Repository\Exceptions\PropertyReadOnlyException
     * @dep_ends eZ\Publish\API\Repository\Tests\ContentTypeServiceTest::testCreateContentTypeGroup
     */
    public function testSetNameThrowsException()
    {
        $repository = $this->getRepository();

        $this->createGroup();

        /* BEGIN: Use Case */
        $contentTypeService = $repository->getContentTypeService();

        $group = $contentTypeService->loadContentTypeGroupByIdentifier( 'new-group' );

        // Throws PropertyReadOnlyExceptionStub
        $group->names['de_DE'] = 'Ein neuer Name';
        /* END: Use Case */
    }

    /**
     * Test for the getDescriptions() method.
     *
     * @return void
     * @see \eZ\Publish\API\Repository\Values\ContentType\ContentTypeGroup::getDescriptions()
     * @dep_ends eZ\Publish\API\Repository\Tests\ContentTypeServiceTest::testCreateContentTypeGroup
     */
    public function testGetDescriptions()
    {
        $repository = $this->getRepository();

        $this->createGroup();

        /* BEGIN: Use Case */
        $contentTypeService = $repository->getContentTypeService();

        $group = $contentTypeService->loadContentTypeGroupByIdentifier( 'new-group' );

        $descriptions = $group->getDescriptions();
        /* END: Use Case */

        $this->assertInternalType( 'array', $descriptions );

        return $descriptions;
    }

    /**
     * Test for the getDescriptions() method.
     *
     * @return void
     * @see \eZ\Publish\API\Repository\Values\ContentType\ContentTypeGroup::getDescriptions()
     * @depends eZ\Publish\API\Repository\Tests\Values\ContentType\ContentTypeGroupTest::testGetDescriptions
     */
    public function testGetDescriptionsContent( $descriptions )
    {
        $this->assertEquals( 2, count( $descriptions ) );
        $this->assertEquals( 'Eine Beschreibung', $descriptions['de_DE'] );
        $this->assertEquals( 'A description', $descriptions['en_US'] );
    }

    /**
     * Test for the getDescription() method.
     *
     * @return void
     * @see \eZ\Publish\API\Repository\Values\ContentType\ContentTypeGroup::getDescription()
     * @dep_ends eZ\Publish\API\Repository\Tests\ContentTypeServiceTest::testCreateContentTypeGroup
     */
    public function testGetDescription()
    {
        $repository = $this->getRepository();

        $this->createGroup();

        /* BEGIN: Use Case */
        $contentTypeService = $repository->getContentTypeService();

        $group = $contentTypeService->loadContentTypeGroupByIdentifier( 'new-group' );

        $germanDescription = $group->getDescription( 'de_DE' );
        /* END: Use Case */

        $this->assertEquals( 'Eine Beschreibung', $germanDescription );
    }

    /**
     * Test for the getDescription() method in the magic way.
     *
     * @return void
     * @see \eZ\Publish\API\Repository\Values\ContentType\ContentTypeGroup::getDescription()
     * @dep_ends eZ\Publish\API\Repository\Tests\ContentTypeServiceTest::testCreateContentTypeGroup
     */
    public function testGetDescriptionMagic()
    {
        $repository = $this->getRepository();

        $this->createGroup();

        /* BEGIN: Use Case */
        $contentTypeService = $repository->getContentTypeService();

        $group = $contentTypeService->loadContentTypeGroupByIdentifier( 'new-group' );

        $germanDescription = $group->descriptions['de_DE'];
        /* END: Use Case */

        $this->assertEquals( 'Eine Beschreibung', $germanDescription );
    }

    /**
     * Test for magic write access to descriptions.
     *
     * @return void
     * @see \eZ\Publish\API\Repository\Values\ContentType\ContentTypeGroup::getDescription()
     * @expectedException eZ\Publish\API\Repository\Exceptions\PropertyReadOnlyException
     * @dep_ends eZ\Publish\API\Repository\Tests\ContentTypeServiceTest::testCreateContentTypeGroup
     */
    public function testSetDescriptionThrowsException()
    {
        $repository = $this->getRepository();

        $this->createGroup();

        /* BEGIN: Use Case */
        $contentTypeService = $repository->getContentTypeService();

        $group = $contentTypeService->loadContentTypeGroupByIdentifier( 'new-group' );

        // Throws PropertyReadOnlyExceptionStub
        $group->descriptions['de_DE'] = 'Eine neue Beschreibung';
        /* END: Use Case */
    }

    /**
     * Creates a content type group fixture
     *
     * @return eZ\Publish\API\Repository\Tests\Stubs\ContentTypeGroupStub
     */
    protected function createGroup()
    {
        $repository = $this->getRepository();
        $contentTypeService = $repository->getContentTypeService();

        $groupCreate = $contentTypeService->newContentTypeGroupCreateStruct(
            'new-group'
        );

        $groupCreate->mainLanguageCode = 'de_DE';
        $groupCreate->setName( 'Ein Name' );
        $groupCreate->setName( 'A name', 'en_US' );
        $groupCreate->setDescription( 'Eine Beschreibung' );
        $groupCreate->setDescription( 'A description', 'en_US' );

        return $contentTypeService->createContentTypeGroup( $groupCreate );
    }
}
