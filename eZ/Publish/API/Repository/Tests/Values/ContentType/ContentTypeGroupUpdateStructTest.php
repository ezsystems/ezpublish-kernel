<?php
/**
 * File containing the ContentTypeGroupUpdateStructTest class
 *
 * @copyright Copyright (C) 1999-2012 eZ Systems AS. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License v2
 * @version //autogentag//
 */

namespace eZ\Publish\API\Repository\Tests\Values\ContentType;

use \eZ\Publish\API\Repository\Tests\BaseTest;

/**
 * Test case for operations in the ContentTypeGroupUpdateStruct using in memory storage.
 *
 * @see eZ\Publish\API\Repository\Values\ContentType\ContentTypeGroupUpdateStruct
 */
class ContentTypeGroupUpdateStructTest extends BaseTest
{
    /**
     * Test for the setName() method.
     *
     * @return void
     * @see \eZ\Publish\API\Repository\Values\ContentType\ContentTypeGroupUpdateStruct::setName($name, $language)
     * @dep_ends eZ\Publish\API\Repository\Tests\ContentTypeServiceTest::testNewContentTypeGroupUpdateStruct
     */
    public function testSetNameWithLanguage()
    {
        $repository =  $this->getRepository();

        /* BEGIN: Use Case */
        $contentTypeService = $repository->getContentTypeService();

        $groupUpdate = $contentTypeService->newContentTypeGroupUpdateStruct(
            'new-group'
        );

        $groupUpdate->setName( 'John Michael Dorian', 'eng_US' );
        /* END: Use Case */

        $this->assertEquals(
            'John Michael Dorian',
            $groupUpdate->names['eng_US']
        );
    }

    /**
     * Test for the setName() method.
     *
     * @return void
     * @see \eZ\Publish\API\Repository\Values\ContentType\ContentTypeGroupUpdateStruct::setName()
     * @dep_ends eZ\Publish\API\Repository\Tests\Values\ContentType\ContentTypeGroupUpdateStructTest::testSetNameWithLanguage
     */
    public function testSetName()
    {
        $repository =  $this->getRepository();

        /* BEGIN: Use Case */
        $contentTypeService = $repository->getContentTypeService();

        $groupUpdate = $contentTypeService->newContentTypeGroupUpdateStruct(
            'new-group'
        );
        $groupUpdate->mainLanguageCode = 'de_DE';

        $groupUpdate->setName( 'John Michael Dorian' );
        /* END: Use Case */

        $this->assertEquals(
            'John Michael Dorian',
            $groupUpdate->names['de_DE']
        );
    }

    /**
     * Test for the setName() method via magic property access.
     *
     * @return void
     * @see \eZ\Publish\API\Repository\Values\ContentType\ContentTypeGroupUpdateStruct::setName()
     * @dep_ends eZ\Publish\API\Repository\Tests\Values\ContentType\ContentTypeGroupUpdateStructTest::testSetName
     */
    public function testSetNameMagicAccess()
    {
        $repository =  $this->getRepository();

        /* BEGIN: Use Case */
        $contentTypeService = $repository->getContentTypeService();

        $groupUpdate = $contentTypeService->newContentTypeGroupUpdateStruct(
            'new-group'
        );
        $groupUpdate->mainLanguageCode = 'de_DE';

        $groupUpdate->name = 'John Michael Dorian';
        /* END: Use Case */

        $this->assertEquals(
            'John Michael Dorian',
            $groupUpdate->names['de_DE']
        );
        return $groupUpdate;
    }

    /**
     * Test for magic property access to $name
     *
     * @return void
     * @see \eZ\Publish\API\Repository\Values\ContentType\ContentTypeGroupUpdateStruct::setName()
     * @depends eZ\Publish\API\Repository\Tests\Values\ContentType\ContentTypeGroupUpdateStructTest::testSetNameMagicAccess
     */
    public function testGetNameMagicAccess( $groupUpdate )
    {
        $this->assertEquals(
            'John Michael Dorian',
            $groupUpdate->name
        );
    }

    /**
     * Test for the setDescription() method.
     *
     * @return void
     * @see \eZ\Publish\API\Repository\Values\ContentType\ContentTypeGroupUpdateStruct::setDescription($description, $language)
     * 
     */
    public function testSetDescriptionWithLanguage()
    {
        $repository =  $this->getRepository();

        /* BEGIN: Use Case */
        $contentTypeService = $repository->getContentTypeService();

        $groupUpdate = $contentTypeService->newContentTypeGroupUpdateStruct(
            'new-group'
        );

        $groupUpdate->setDescription( 'Elliot Reid', 'eng_US' );
        /* END: Use Case */

        $this->assertEquals(
            'Elliot Reid',
            $groupUpdate->descriptions['eng_US']
        );
    }

    /**
     * Test for the setDescription() method.
     *
     * @return void
     * @see \eZ\Publish\API\Repository\Values\ContentType\ContentTypeGroupUpdateStruct::setDescription()
     * @dep_ends eZ\Publish\API\Repository\Tests\Values\ContentType\ContentTypeGroupUpdateStructTest::testSetDescriptionWithLanguage
     */
    public function testSetDescription()
    {
        $repository =  $this->getRepository();

        /* BEGIN: Use Case */
        $contentTypeService = $repository->getContentTypeService();

        $groupUpdate = $contentTypeService->newContentTypeGroupUpdateStruct(
            'new-group'
        );
        $groupUpdate->mainLanguageCode = 'de_DE';

        $groupUpdate->setDescription( 'Elliot Reid' );
        /* END: Use Case */

        $this->assertEquals(
            'Elliot Reid',
            $groupUpdate->descriptions['de_DE']
        );
    }

    /**
     * Test for the setDescription() method via magic property access.
     *
     * @return void
     * @see \eZ\Publish\API\Repository\Values\ContentType\ContentTypeGroupUpdateStruct::setDescription()
     * @dep_ends eZ\Publish\API\Repository\Tests\Values\ContentType\ContentTypeGroupUpdateStructTest::testSetDescription
     */
    public function testSetDescriptionMagicAccess()
    {
        $repository =  $this->getRepository();

        /* BEGIN: Use Case */
        $contentTypeService = $repository->getContentTypeService();

        $groupUpdate = $contentTypeService->newContentTypeGroupUpdateStruct(
            'new-group'
        );
        $groupUpdate->mainLanguageCode = 'de_DE';

        $groupUpdate->description = 'Elliot Reid';
        /* END: Use Case */

        $this->assertEquals(
            'Elliot Reid',
            $groupUpdate->descriptions['de_DE']
        );
        return $groupUpdate;
    }

    /**
     * Test for magic property access to $description
     *
     * @return void
     * @see \eZ\Publish\API\Repository\Values\ContentType\ContentTypeGroupUpdateStruct::setDescription()
     * @depends eZ\Publish\API\Repository\Tests\Values\ContentType\ContentTypeGroupUpdateStructTest::testSetDescriptionMagicAccess
     */
    public function testGetDescriptionMagicAccess( $groupUpdate )
    {
        $this->assertEquals(
            'Elliot Reid',
            $groupUpdate->description
        );
    }
}
