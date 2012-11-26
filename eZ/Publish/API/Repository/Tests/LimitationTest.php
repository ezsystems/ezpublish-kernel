<?php
/**
 * File containing the LimitationTest class
 *
 * @copyright Copyright (C) 1999-2012 eZ Systems AS. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License v2
 * @version //autogentag//
 */

namespace eZ\Publish\API\Repository\Tests;

use eZ\Publish\API\Repository\Values\Content\Location;
use eZ\Publish\API\Repository\Values\User\Limitation\ContentTypeLimitation;
use eZ\Publish\API\Repository\Values\User\Limitation\ParentContentTypeLimitation;

/**
 * Test case for different content object limitations.
 *
 * @see eZ\Publish\API\Repository\Values\User\Limitation
 * @see eZ\Publish\API\Repository\Values\User\Limitation\ContentTypeLimitation
 * @see eZ\Publish\API\Repository\Values\User\Limitation\ParentContentTypeLimitation
 * @group integration
 * @group limitation
 */
class LimitationTest extends BaseTest
{
    /**
     * Test for ParentContentTypeLimitation and ContentTypeLimitation.
     *
     * @return void
     * @see \eZ\Publish\API\Repository\Values\User\Limitation\ContentTypeLimitation
     * @see \eZ\Publish\API\Repository\Values\User\Limitation\ParentContentTypeLimitation
     */
    public function testContentTypeAndParentContentTypeLimitationAllow()
    {
        $repository = $this->getRepository();

        $parentLocationId = $this->generateId( 'location', 60 );
        $sectionId = $this->generateId( 'section', 1 );
        /* BEGIN: Use Case */
        $user = $this->createUserVersion1();

        $roleService = $repository->getRoleService();

        $role = $roleService->loadRoleByIdentifier( 'Editor' );

        $policyCreate = $roleService->newPolicyCreateStruct( 'content', 'create' );
        $policyCreate->addLimitation(
            new ParentContentTypeLimitation(
                array( 'limitationValues' => array( 20 ) )
            )
        );
        $policyCreate->addLimitation(
            new ContentTypeLimitation(
                array( 'limitationValues' => array( 22 ) )
            )
        );

        $role = $roleService->addPolicy( $role, $policyCreate );

        $roleService->assignRoleToUser( $role, $user );

        $repository->setCurrentUser( $user );

        $contentTypeService = $repository->getContentTypeService();
        $locationService = $repository->getLocationService();
        $contentService = $repository->getContentService();

        // Configure new location
        // $parentLocationId is the id of the /Home/Contact-Us node
        $locationCreate = $locationService->newLocationCreateStruct( $parentLocationId );

        $locationCreate->priority = 23;
        $locationCreate->hidden = true;
        $locationCreate->remoteId = '0123456789abcdef0123456789abcdef';
        $locationCreate->sortField = Location::SORT_FIELD_NODE_ID;
        $locationCreate->sortOrder = Location::SORT_ORDER_DESC;

        // Load content type
        $wikiPageType = $contentTypeService->loadContentTypeByIdentifier( 'wiki_page' );

        // Configure new content object
        $wikiPageCreate = $contentService->newContentCreateStruct( $wikiPageType, 'eng-US' );

        $wikiPageCreate->setField( 'title', 'An awesome wiki page' );
        $wikiPageCreate->remoteId = 'abcdef0123456789abcdef0123456789';
        // $sectionId is the ID of section 1
        $wikiPageCreate->sectionId = $sectionId;
        $wikiPageCreate->alwaysAvailable = true;

        // Create a draft
        $contentService->createContent(
            $wikiPageCreate,
            array( $locationCreate )
        );
        /* END: Use Case */
    }

    /**
     * Test for ParentContentTypeLimitation and ContentTypeLimitation.
     *
     * @return void
     * @see \eZ\Publish\API\Repository\Values\User\Limitation\ContentTypeLimitation
     * @see \eZ\Publish\API\Repository\Values\User\Limitation\ParentContentTypeLimitation
     * @expectedException \eZ\Publish\API\Repository\Exceptions\UnauthorizedException
     */
    public function testContentTypeAndParentContentTypeLimitationForbid()
    {
        $repository = $this->getRepository();

        $parentLocationId = $this->generateId( 'location', 60 );
        $sectionId = $this->generateId( 'section', 1 );
        /* BEGIN: Use Case */
        $user = $this->createUserVersion1();

        $roleService = $repository->getRoleService();

        $role = $roleService->loadRoleByIdentifier( 'Editor' );

        $policyCreate = $roleService->newPolicyCreateStruct( 'content', 'create' );
        $policyCreate->addLimitation(
            new ParentContentTypeLimitation(
                array( 'limitationValues' => array( 20 ) )
            )
        );
        $policyCreate->addLimitation(
            new ContentTypeLimitation(
                array( 'limitationValues' => array( 33 ) )
            )
        );

        $role = $roleService->addPolicy( $role, $policyCreate );

        $roleService->assignRoleToUser( $role, $user );

        $repository->setCurrentUser( $user );

        $contentTypeService = $repository->getContentTypeService();
        $locationService = $repository->getLocationService();
        $contentService = $repository->getContentService();

        // Configure new location
        // $parentLocationId is the id of the /Home/Contact-Us node
        $locationCreate = $locationService->newLocationCreateStruct( $parentLocationId );

        $locationCreate->priority = 23;
        $locationCreate->hidden = true;
        $locationCreate->remoteId = '0123456789abcdef0123456789abcdef';
        $locationCreate->sortField = Location::SORT_FIELD_NODE_ID;
        $locationCreate->sortOrder = Location::SORT_ORDER_DESC;

        // Load content type
        $contentType = $contentTypeService->loadContentTypeByIdentifier( 'wiki_page' );

        // Configure new content object
        $contentCreate = $contentService->newContentCreateStruct( $contentType, 'eng-US' );

        $contentCreate->setField( 'title', 'An awesome wiki page' );
        $contentCreate->remoteId = 'abcdef0123456789abcdef0123456789';
        // $sectionId is the ID of section 1
        $contentCreate->sectionId = $sectionId;
        $contentCreate->alwaysAvailable = true;

        // This call will fail with an UnauthorizedException
        $contentService->createContent(
            $contentCreate,
            array( $locationCreate )
        );
        /* END: Use Case */
    }
}
