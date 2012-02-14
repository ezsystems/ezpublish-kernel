<?php
/**
 * File containing the ContentTypeGroupCreateStructTest class
 *
 * @copyright Copyright (C) 1999-2012 eZ Systems AS. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License v2
 * @version //autogentag//
 */

namespace eZ\Publish\API\Repository\Tests\Values\ContentType;

use \eZ\Publish\API\Repository\Tests\BaseTest;

/**
 * Test case for operations in the ContentTypeGroupCreateStruct using in memory storage.
 *
 * @see eZ\Publish\API\Repository\Values\ContentType\ContentTypeGroupCreateStruct
 */
class ContentTypeGroupCreateStructTest extends BaseTest
{
    /**
     * Test for the setName() method.
     *
     * @return void
     * @see \eZ\Publish\API\Repository\Values\ContentType\ContentTypeGroupCreateStruct::setName($name, $language)
     * @dep_ends eZ\Publish\API\Repository\Tests\ContentTypeServiceTest::testNewContentTypeGroupCreateStruct
     */
    public function testSetNameWithLanguage()
    {
        $repository =  $this->getRepository();

        /* BEGIN: Use Case */
        $contentTypeService = $repository->getContentTypeService();

        $groupCreate = $contentTypeService->newContentTypeGroupCreateStruct(
            'new-group'
        );

        $groupCreate->setName( 'John Michael Dorian', 'eng_US' );
        /* END: Use Case */

        $this->assertEquals(
            'John Michael Dorian',
            $groupCreate->names['eng_US']
        );
    }

    /**
     * Test for the setName() method.
     *
     * @return void
     * @see \eZ\Publish\API\Repository\Values\ContentType\ContentTypeGroupCreateStruct::setName()
     * @dep_ends eZ\Publish\API\Repository\Tests\Values\ContentType\ContentTypeGroupCreateStructTest::testSetNameWithLanguage
     */
    public function testSetName()
    {
        $repository =  $this->getRepository();

        /* BEGIN: Use Case */
        $contentTypeService = $repository->getContentTypeService();

        $groupCreate = $contentTypeService->newContentTypeGroupCreateStruct(
            'new-group'
        );
        $groupCreate->mainLanguageCode = 'de_DE';

        $groupCreate->setName( 'John Michael Dorian' );
        /* END: Use Case */

        $this->assertEquals(
            'John Michael Dorian',
            $groupCreate->names['de_DE']
        );
    }

    /**
     * Test for the setName() method via magic property access.
     *
     * @return void
     * @see \eZ\Publish\API\Repository\Values\ContentType\ContentTypeGroupCreateStruct::setName()
     * @dep_ends eZ\Publish\API\Repository\Tests\Values\ContentType\ContentTypeGroupCreateStructTest::testSetName
     */
    public function testSetNameMagicAccess()
    {
        $repository =  $this->getRepository();

        /* BEGIN: Use Case */
        $contentTypeService = $repository->getContentTypeService();

        $groupCreate = $contentTypeService->newContentTypeGroupCreateStruct(
            'new-group'
        );
        $groupCreate->mainLanguageCode = 'de_DE';

        $groupCreate->name = 'John Michael Dorian';
        /* END: Use Case */

        $this->assertEquals(
            'John Michael Dorian',
            $groupCreate->names['de_DE']
        );
        return $groupCreate;
    }

    /**
     * Test for magic property access to $name
     *
     * @return void
     * @see \eZ\Publish\API\Repository\Values\ContentType\ContentTypeGroupCreateStruct::setName()
     * @depends eZ\Publish\API\Repository\Tests\Values\ContentType\ContentTypeGroupCreateStructTest::testSetNameMagicAccess
     */
    public function testGetNameMagicAccess( $groupCreate )
    {
        $this->assertEquals(
            'John Michael Dorian',
            $groupCreate->name
        );
    }

    /**
     * Test for the setDescription() method.
     *
     * @return void
     * @see \eZ\Publish\API\Repository\Values\ContentType\ContentTypeGroupCreateStruct::setDescription($description, $language)
     * 
     */
    public function testSetDescriptionWithLanguage()
    {
        $repository =  $this->getRepository();

        /* BEGIN: Use Case */
        $contentTypeService = $repository->getContentTypeService();

        $groupCreate = $contentTypeService->newContentTypeGroupCreateStruct(
            'new-group'
        );

        $groupCreate->setDescription( 'Elliot Reid', 'eng_US' );
        /* END: Use Case */

        $this->assertEquals(
            'Elliot Reid',
            $groupCreate->descriptions['eng_US']
        );
    }

    /**
     * Test for the setDescription() method.
     *
     * @return void
     * @see \eZ\Publish\API\Repository\Values\ContentType\ContentTypeGroupCreateStruct::setDescription()
     * @dep_ends eZ\Publish\API\Repository\Tests\Values\ContentType\ContentTypeGroupCreateStructTest::testSetDescriptionWithLanguage
     */
    public function testSetDescription()
    {
        $repository =  $this->getRepository();

        /* BEGIN: Use Case */
        $contentTypeService = $repository->getContentTypeService();

        $groupCreate = $contentTypeService->newContentTypeGroupCreateStruct(
            'new-group'
        );
        $groupCreate->mainLanguageCode = 'de_DE';

        $groupCreate->setDescription( 'Elliot Reid' );
        /* END: Use Case */

        $this->assertEquals(
            'Elliot Reid',
            $groupCreate->descriptions['de_DE']
        );
    }

    /**
     * Test for the setDescription() method via magic property access.
     *
     * @return void
     * @see \eZ\Publish\API\Repository\Values\ContentType\ContentTypeGroupCreateStruct::setDescription()
     * @dep_ends eZ\Publish\API\Repository\Tests\Values\ContentType\ContentTypeGroupCreateStructTest::testSetDescription
     */
    public function testSetDescriptionMagicAccess()
    {
        $repository =  $this->getRepository();

        /* BEGIN: Use Case */
        $contentTypeService = $repository->getContentTypeService();

        $groupCreate = $contentTypeService->newContentTypeGroupCreateStruct(
            'new-group'
        );
        $groupCreate->mainLanguageCode = 'de_DE';

        $groupCreate->description = 'Elliot Reid';
        /* END: Use Case */

        $this->assertEquals(
            'Elliot Reid',
            $groupCreate->descriptions['de_DE']
        );
        return $groupCreate;
    }

    /**
     * Test for magic property access to $description
     *
     * @return void
     * @see \eZ\Publish\API\Repository\Values\ContentType\ContentTypeGroupCreateStruct::setDescription()
     * @depends eZ\Publish\API\Repository\Tests\Values\ContentType\ContentTypeGroupCreateStructTest::testSetDescriptionMagicAccess
     */
    public function testGetDescriptionMagicAccess( $groupCreate )
    {
        $this->assertEquals(
            'Elliot Reid',
            $groupCreate->description
        );
    }
}
