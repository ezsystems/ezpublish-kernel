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
use eZ\Publish\API\Repository\Values\User\Limitation\LanguageLimitation;
use eZ\Publish\API\Repository\Values\User\Limitation\OwnerLimitation;
use eZ\Publish\API\Repository\Values\User\Limitation\ParentContentTypeLimitation;
use eZ\Publish\API\Repository\Values\User\Limitation\ParentDepthLimitation;
use eZ\Publish\API\Repository\Values\User\Limitation\ParentOwnerLimitation;
use eZ\Publish\API\Repository\Values\User\Limitation\SectionLimitation;

/**
 * Test case for different content object limitations.
 *
 * @see eZ\Publish\API\Repository\Values\User\Limitation
 * @see eZ\Publish\API\Repository\Values\User\Limitation\ContentTypeLimitation
 * @see eZ\Publish\API\Repository\Values\User\Limitation\LanguageLimitation
 * @see eZ\Publish\API\Repository\Values\User\Limitation\OwnerLimitation
 * @see eZ\Publish\API\Repository\Values\User\Limitation\ParentContentTypeLimitation
 * @see eZ\Publish\API\Repository\Values\User\Limitation\ParentDepthLimitation
 * @see eZ\Publish\API\Repository\Values\User\Limitation\ParentOwnerLimitation
 * @see eZ\Publish\API\Repository\Values\User\Limitation\SectionLimitation
 * @group integration
 * @group limitation
 */
class LimitationTest extends BaseTest
{
    /**
     * Test for the ContentTypeLimitation.
     *
     * @return void
     * @see \eZ\Publish\API\Repository\Values\User\Limitation\ContentTypeLimitation
     * @throws \ErrorException
     */
    public function testContentTypeLimitationAllow()
    {
        $repository = $this->getRepository();

        $contentService = $repository->getContentService();

        $contentTypeId = $this->generateId( 'contentType', 22 );
        /* BEGIN: Use Case */
        $user = $this->createUserVersion1();

        $roleService = $repository->getRoleService();

        $role = $roleService->loadRoleByIdentifier( 'Editor' );

        $editPolicy = null;
        foreach ( $role->getPolicies() as $policy )
        {
            if ( 'content' != $policy->module || 'edit' != $policy->function )
            {
                continue;
            }
            $editPolicy = $policy;
            break;
        }

        if ( null === $editPolicy )
        {
            throw new \ErrorException( 'No content:edit policy found.' );
        }

        $policyUpdate = $roleService->newPolicyUpdateStruct();
        $policyUpdate->addLimitation(
            new ContentTypeLimitation(
                array( 'limitationValues' => array( $contentTypeId ) )
            )
        );

        $roleService->updatePolicy( $editPolicy, $policyUpdate );
        $roleService->assignRoleToUser( $roleService->loadRole( $role->id ), $user );

        $content = $this->createWikiPage();

        $repository->setCurrentUser( $user );

        $updateDraft = $contentService->createContentDraft( $content->contentInfo );

        $contentUpdate = $contentService->newContentUpdateStruct();
        $contentUpdate->setField( 'title', 'Your wiki page' );

        $updateContent = $contentService->updateContent(
            $updateDraft->versionInfo,
            $contentUpdate
        );
        /* END: Use Case */

        $this->assertEquals(
            'Your wiki page',
            $updateContent->getFieldValue('title')->text
        );
    }

    /**
     * Test for the ContentTypeLimitation.
     *
     * @return void
     * @see \eZ\Publish\API\Repository\Values\User\Limitation\ContentTypeLimitation
     * @throws \ErrorException
     * @expectedException \eZ\Publish\API\Repository\Exceptions\UnauthorizedException
     */
    public function testContentTypeLimitationForbid()
    {
        $repository = $this->getRepository();

        $contentService = $repository->getContentService();

        $contentTypeId = $this->generateId( 'contentType', 33 );
        /* BEGIN: Use Case */
        $user = $this->createUserVersion1();

        $roleService = $repository->getRoleService();

        $role = $roleService->loadRoleByIdentifier( 'Editor' );

        $editPolicy = null;
        foreach ( $role->getPolicies() as $policy )
        {
            if ( 'content' != $policy->module || 'edit' != $policy->function )
            {
                continue;
            }
            $editPolicy = $policy;
            break;
        }

        if ( null === $editPolicy )
        {
            throw new \ErrorException( 'No content:edit policy found.' );
        }

        $policyUpdate = $roleService->newPolicyUpdateStruct();
        $policyUpdate->addLimitation(
            new ContentTypeLimitation(
                array( 'limitationValues' => array( $contentTypeId ) )
            )
        );

        $roleService->updatePolicy( $editPolicy, $policyUpdate );
        $roleService->assignRoleToUser( $roleService->loadRole( $role->id ), $user );

        $content = $this->createWikiPage();

        $repository->setCurrentUser( $user );

        // This call fails with an UnauthorizedException
        $contentService->createContentDraft( $content->contentInfo );
        /* END: Use Case */
    }

    /**
     * Test for the ContentTypeLimitation.
     *
     * @return void
     * @see \eZ\Publish\API\Repository\Values\User\Limitation\ContentTypeLimitation
     * @throws \ErrorException
     * @expectedException \eZ\Publish\API\Repository\Exceptions\UnauthorizedException
     */
    public function testContentTypeLimitationForbidVariant()
    {
        $repository = $this->getRepository();

        $contentService = $repository->getContentService();

        $contentTypeId = $this->generateId( 'contentType', 33 );
        /* BEGIN: Use Case */
        $user = $this->createUserVersion1();

        $roleService = $repository->getRoleService();

        $role = $roleService->loadRoleByIdentifier( 'Editor' );

        $editPolicy = null;
        foreach ( $role->getPolicies() as $policy )
        {
            if ( 'content' != $policy->module || 'edit' != $policy->function )
            {
                continue;
            }
            $editPolicy = $policy;
            break;
        }

        if ( null === $editPolicy )
        {
            throw new \ErrorException( 'No content:edit policy found.' );
        }

        $policyUpdate = $roleService->newPolicyUpdateStruct();
        $policyUpdate->addLimitation(
            new ContentTypeLimitation(
                array( 'limitationValues' => array( $contentTypeId ) )
            )
        );

        $roleService->updatePolicy( $editPolicy, $policyUpdate );
        $roleService->assignRoleToUser( $roleService->loadRole( $role->id ), $user );

        $content = $this->createWikiPage();

        $updateDraft = $contentService->createContentDraft( $content->contentInfo );

        $repository->setCurrentUser( $user );

        $contentUpdate = $contentService->newContentUpdateStruct();
        $contentUpdate->setField( 'title', 'Your wiki page' );

        // This call fails with an UnauthorizedException
        $contentService->updateContent(
            $updateDraft->versionInfo,
            $contentUpdate
        );
        /* END: Use Case */
    }

    /**
     * Test for the SectionLimitation.
     *
     * @return void
     * @see \eZ\Publish\API\Repository\Values\User\Limitation\SectionLimitation
     * @throws \ErrorException
     */
    public function testSectionLimitationAllow()
    {
        $repository = $this->getRepository();

        /* BEGIN: Use Case */
        $user = $this->createUserVersion1();

        $roleService = $repository->getRoleService();

        $role = $roleService->loadRoleByIdentifier( 'Editor' );

        $readPolicy = null;
        foreach ( $role->getPolicies() as $policy )
        {
            if ( 'content' != $policy->module || 'read' != $policy->function )
            {
                continue;
            }
            $readPolicy = $policy;
            break;
        }

        if ( null === $readPolicy )
        {
            throw new \ErrorException( 'No content:read policy found.' );
        }

        // Only allow access to the media section
        $policyUpdate = $roleService->newPolicyUpdateStruct();
        $policyUpdate->addLimitation(
            new SectionLimitation(
                array( 'limitationValues' => array( 3 ) )
            )
        );

        $roleService->updatePolicy( $readPolicy, $policyUpdate );
        $roleService->assignRoleToUser( $role, $user );

        $repository->setCurrentUser( $user );

        $contentService = $repository->getContentService();

        // Load the images folder
        $images = $contentService->loadContentByRemoteId( 'e7ff633c6b8e0fd3531e74c6e712bead' );
        /* END: Use Case */

        $this->assertEquals(
            'Images',
            $images->getFieldValue( 'name' )->text
        );
    }

    /**
     * Test for the SectionLimitation.
     *
     * @return void
     * @see \eZ\Publish\API\Repository\Values\User\Limitation\SectionLimitation
     * @throws \ErrorException
     * @expectedException \eZ\Publish\API\Repository\Exceptions\UnauthorizedException
     */
    public function testSectionLimitationForbid()
    {
        $repository = $this->getRepository();

        /* BEGIN: Use Case */
        $user = $this->createUserVersion1();

        $roleService = $repository->getRoleService();

        $role = $roleService->loadRoleByIdentifier( 'Editor' );

        $readPolicy = null;
        foreach ( $role->getPolicies() as $policy )
        {
            if ( 'content' != $policy->module || 'read' != $policy->function )
            {
                continue;
            }
            $readPolicy = $policy;
            break;
        }

        if ( null === $readPolicy )
        {
            throw new \ErrorException( 'No content:read policy found.' );
        }

        // Give access to "Standard" and "Restricted" section
        $policyUpdate = $roleService->newPolicyUpdateStruct();
        $policyUpdate->addLimitation(
            new SectionLimitation(
                array( 'limitationValues' => array( 1, 6 ) )
            )
        );

        $roleService->updatePolicy( $readPolicy, $policyUpdate );
        $roleService->assignRoleToUser( $role, $user );

        $repository->setCurrentUser( $user );

        $contentService = $repository->getContentService();

        // This call fails with an UnauthorizedException because the current user
        // cannot access the "Media" section
        $contentService->loadContentByRemoteId( 'e7ff633c6b8e0fd3531e74c6e712bead' );
        /* END: Use Case */
    }

    /**
     * Test for the OwnerLimitation
     *
     * @return void
     * @see \eZ\Publish\API\Repository\Values\User\Limitation\OwnerLimitation
     * @throws \ErrorException
     */
    public function testOwnerLimitationAllow()
    {
        $repository = $this->getRepository();

        $contentService = $repository->getContentService();

        /* BEGIN: Use Case */
        $user = $this->createUserVersion1();

        $roleService = $repository->getRoleService();

        $role = $roleService->loadRoleByIdentifier( 'Editor' );

        $removePolicy = null;
        foreach ( $role->getPolicies() as $policy )
        {
            if ( 'content' != $policy->module || 'remove' != $policy->function )
            {
                continue;
            }
            $removePolicy = $policy;
            break;
        }

        if ( null === $removePolicy )
        {
            throw new \ErrorException( 'No content:remove policy found.' );
        }

        // Only allow remove for the user's own content
        $policyUpdate = $roleService->newPolicyUpdateStruct();
        $policyUpdate->addLimitation(
            new OwnerLimitation(
                array( 'limitationValues' => array( 1 ) )
            )
        );
        $roleService->updatePolicy( $removePolicy, $policyUpdate );

        $roleService->assignRoleToUser( $role, $user );

        $content = $this->createWikiPage();

        $metadataUpdate = $contentService->newContentMetadataUpdateStruct();
        $metadataUpdate->ownerId = $user->id;

        $contentService->updateContentMetadata(
            $content->contentInfo,
            $metadataUpdate
        );

        $repository->setCurrentUser( $user );

        $contentService->deleteContent(
            $contentService->loadContentInfo( $content->id )
        );
        /* END: Use Case */

        $this->setExpectedException(
            '\\eZ\\Publish\\API\\Repository\\Exceptions\\NotFoundException'
        );
        $contentService->loadContent( $content->id );
    }

    /**
     * Test for the OwnerLimitation
     *
     * @return void
     * @see \eZ\Publish\API\Repository\Values\User\Limitation\OwnerLimitation
     * @throws \ErrorException
     * @expectedException \eZ\Publish\API\Repository\Exceptions\UnauthorizedException
     */
    public function testOwnerLimitationForbid()
    {
        $repository = $this->getRepository();

        $contentService = $repository->getContentService();

        /* BEGIN: Use Case */
        $user = $this->createUserVersion1();

        $roleService = $repository->getRoleService();

        $role = $roleService->loadRoleByIdentifier( 'Editor' );

        $removePolicy = null;
        foreach ( $role->getPolicies() as $policy )
        {
            if ( 'content' != $policy->module || 'remove' != $policy->function )
            {
                continue;
            }
            $removePolicy = $policy;
            break;
        }

        if ( null === $removePolicy )
        {
            throw new \ErrorException( 'No content:remove policy found.' );
        }

        // Only allow remove for the user's own content
        $policyUpdate = $roleService->newPolicyUpdateStruct();
        $policyUpdate->addLimitation(
            new OwnerLimitation(
                array( 'limitationValues' => array( 1 ) )
            )
        );
        $roleService->updatePolicy( $removePolicy, $policyUpdate );

        $roleService->assignRoleToUser( $role, $user );

        $content = $this->createWikiPage();

        $repository->setCurrentUser( $user );

        // This call fails with an UnauthorizedException, because the current
        // user is not the content owner
        $contentService->deleteContent(
            $contentService->loadContentInfo( $content->id )
        );
        /* END: Use Case */
    }

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

        $parentContentTypeId = $this->generateId( 'contentType', 20 );
        $contentTypeId = $this->generateId( 'contentType', 22 );
        /* BEGIN: Use Case */
        $user = $this->createUserVersion1();

        $roleService = $repository->getRoleService();

        $role = $roleService->loadRoleByIdentifier( 'Editor' );

        $policyCreate = $roleService->newPolicyCreateStruct( 'content', 'create' );
        $policyCreate->addLimitation(
            new ParentContentTypeLimitation(
                array( 'limitationValues' => array( $parentContentTypeId ) )
            )
        );
        $policyCreate->addLimitation(
            new ContentTypeLimitation(
                array( 'limitationValues' => array( $contentTypeId ) )
            )
        );

        $role = $roleService->addPolicy( $role, $policyCreate );

        $roleService->assignRoleToUser( $role, $user );

        $repository->setCurrentUser( $user );

        $draft = $this->createWikiPageDraft();
        /* END: Use Case */

        $this->assertEquals(
            'An awesome wiki page',
            $draft->getFieldValue('title')->text
        );
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

        $parentContentTypeId = $this->generateId( 'contentType', 20 );
        $contentTypeId = $this->generateId( 'contentType', 33 );
        /* BEGIN: Use Case */
        $user = $this->createUserVersion1();

        $roleService = $repository->getRoleService();

        $role = $roleService->loadRoleByIdentifier( 'Editor' );

        $policyCreate = $roleService->newPolicyCreateStruct( 'content', 'create' );
        $policyCreate->addLimitation(
            new ParentContentTypeLimitation(
                array( 'limitationValues' => array( $parentContentTypeId ) )
            )
        );
        $policyCreate->addLimitation(
            new ContentTypeLimitation(
                array( 'limitationValues' => array( $contentTypeId ) )
            )
        );

        $role = $roleService->addPolicy( $role, $policyCreate );

        $roleService->assignRoleToUser( $role, $user );

        $repository->setCurrentUser( $user );

        $this->createWikiPageDraft();
        /* END: Use Case */
    }

    /**
     * Test for the LanguageLimitation
     *
     * @return void
     * @see \eZ\Publish\API\Repository\Values\User\Limitation\LanguageLimitation
     * @throws \ErrorException
     */
    public function testLanguageLimitationAllow()
    {
        $repository = $this->getRepository();

        $contentId = $this->generateId( 'content', 58 );
        /* BEGIN: Use Case */
        $user = $this->createUserVersion1();

        $roleService = $repository->getRoleService();

        $role = $roleService->loadRoleByIdentifier( 'Editor' );

        $editPolicy = null;
        foreach ( $role->getPolicies() as $policy )
        {
            if ( 'content' != $policy->module || 'edit' != $policy->function )
            {
                continue;
            }
            $editPolicy = $policy;
            break;
        }

        if ( null === $editPolicy )
        {
            throw new \ErrorException( 'No content:edit policy found.' );
        }

        // Only allow eng-GB content
        $policyUpdate = $roleService->newPolicyUpdateStruct();
        $policyUpdate->addLimitation(
            new LanguageLimitation(
                array( 'limitationValues' => array( 'eng-GB' ) )
            )
        );
        $roleService->updatePolicy( $editPolicy, $policyUpdate );

        $roleService->assignRoleToUser( $role, $user );

        $contentService = $repository->getContentService();

        $repository->setCurrentUser( $user );

        $contentUpdate = $contentService->newContentUpdateStruct();
        $contentUpdate->setField( 'name', 'Contact Me' );

        $draft = $contentService->createContentDraft(
            $contentService->loadContentInfo( $contentId )
        );

        // Update content object
        $draft = $contentService->updateContent(
            $draft->versionInfo,
            $contentUpdate
        );

        $contentService->publishVersion( $draft->versionInfo );
        /* END: Use Case */

        $this->assertEquals(
            'Contact Me',
            $contentService->loadContent( $contentId )
                ->getFieldValue( 'name' )->text
        );
    }

    /**
     * Test for the LanguageLimitation
     *
     * @return void
     * @see \eZ\Publish\API\Repository\Values\User\Limitation\LanguageLimitation
     * @expectedException \eZ\Publish\API\Repository\Exceptions\UnauthorizedException
     * @throws \ErrorException
     */
    public function testLanguageLimitationForbid()
    {
        $repository = $this->getRepository();

        $contentId = $this->generateId( 'content', 58 );
        /* BEGIN: Use Case */
        $user = $this->createUserVersion1();

        $roleService = $repository->getRoleService();

        $role = $roleService->loadRoleByIdentifier( 'Editor' );

        $editPolicy = null;
        foreach ( $role->getPolicies() as $policy )
        {
            if ( 'content' != $policy->module || 'edit' != $policy->function )
            {
                continue;
            }
            $editPolicy = $policy;
            break;
        }

        if ( null === $editPolicy )
        {
            throw new \ErrorException( 'No content:edit policy found.' );
        }

        // Only allow eng-US content
        $policyUpdate = $roleService->newPolicyUpdateStruct();
        $policyUpdate->addLimitation(
            new LanguageLimitation(
                array( 'limitationValues' => array( 'eng-US' ) )
            )
        );
        $roleService->updatePolicy( $editPolicy, $policyUpdate );

        $roleService->assignRoleToUser( $role, $user );

        $contentService = $repository->getContentService();

        $repository->setCurrentUser( $user );

        $contentUpdate = $contentService->newContentUpdateStruct();
        $contentUpdate->setField( 'name', 'Contact Me' );

        // This call will fail with an UnauthorizedException
        $contentService->createContentDraft(
            $contentService->loadContentInfo( $contentId )
        );
        /* END: Use Case */
    }

    /**
     * Tests a combination of ParentDepthLimitation and ContentTypeLimitation.
     *
     * @return void
     * @see \eZ\Publish\API\Repository\Values\User\Limitation\ContentTypeLimitation
     * @see \eZ\Publish\API\Repository\Values\User\Limitation\ParentDepthLimitation
     */
    public function testParentDepthAndContentTypeLimitationAllow()
    {
        $repository = $this->getRepository();

        $contentTypeId = $this->generateId( 'contentType', 22 );
        /* BEGIN: Use Case */
        $user = $this->createUserVersion1();

        $roleService = $repository->getRoleService();

        $role = $roleService->loadRoleByIdentifier( 'Editor' );

        $policyCreate = $roleService->newPolicyCreateStruct( 'content', 'create' );
        $policyCreate->addLimitation(
            new ParentDepthLimitation(
                array( 'limitationValues' => array( 2 ) )
            )
        );
        $policyCreate->addLimitation(
            new ContentTypeLimitation(
                array( 'limitationValues' => array( $contentTypeId ) )
            )
        );

        $role = $roleService->addPolicy( $role, $policyCreate );

        $roleService->assignRoleToUser( $role, $user );

        $repository->setCurrentUser( $user );

        $draft = $this->createWikiPageDraft();
        /* END: Use Case */

        $this->assertEquals(
            'An awesome wiki page',
            $draft->getFieldValue('title')->text
        );
    }

    /**
     * Tests a combination of ParentDepthLimitation and ContentTypeLimitation.
     *
     * @return void
     * @see \eZ\Publish\API\Repository\Values\User\Limitation\ContentTypeLimitation
     * @see \eZ\Publish\API\Repository\Values\User\Limitation\ParentDepthLimitation
     * @expectedException \eZ\Publish\API\Repository\Exceptions\UnauthorizedException
     */
    public function testParentDepthAndContentTypeLimitationForbid()
    {
        $repository = $this->getRepository();

        $contentTypeId = $this->generateId( 'contentType', 22 );
        /* BEGIN: Use Case */
        $user = $this->createUserVersion1();

        $roleService = $repository->getRoleService();

        $role = $roleService->loadRoleByIdentifier( 'Editor' );

        $policyCreate = $roleService->newPolicyCreateStruct( 'content', 'create' );
        $policyCreate->addLimitation(
            new ParentDepthLimitation(
                array( 'limitationValues' => array( 1, 3, 4 ) )
            )
        );
        $policyCreate->addLimitation(
            new ContentTypeLimitation(
                array( 'limitationValues' => array( $contentTypeId ) )
            )
        );

        $role = $roleService->addPolicy( $role, $policyCreate );

        $roleService->assignRoleToUser( $role, $user );

        $repository->setCurrentUser( $user );

        $this->createWikiPageDraft();
        /* END: Use Case */
    }

    /**
     * Tests the ParentOwnerLimitation.
     *
     * @return void
     * @see \eZ\Publish\API\Repository\Values\User\Limitation\ParentOwnerLimitation
     */
    public function testParentOwnerLimitationAllow()
    {
        $repository = $this->getRepository();

        $parentContentId = $this->generateId( 'content', 58 );
        /* BEGIN: Use Case */
        $user = $this->createUserVersion1();

        $roleService = $repository->getRoleService();

        $role = $roleService->loadRoleByIdentifier( 'Editor' );

        $policyCreate = $roleService->newPolicyCreateStruct( 'content', 'create' );
        $policyCreate->addLimitation(
            new ParentOwnerLimitation(
                array( 'limitationValues' => array( 1 ) )
            )
        );

        $role = $roleService->addPolicy( $role, $policyCreate );

        $roleService->assignRoleToUser( $role, $user );

        $contentService = $repository->getContentService();

        $metadataUpdate = $contentService->newContentMetadataUpdateStruct();
        $metadataUpdate->ownerId = $user->id;

        $contentService->updateContentMetadata(
            $contentService->loadContentInfo( $parentContentId ),
            $metadataUpdate
        );

        $repository->setCurrentUser( $user );

        $draft = $this->createWikiPageDraft();
        /* END: Use Case */

        $this->assertEquals(
            'An awesome wiki page',
            $draft->getFieldValue( 'title' )->text
        );
    }

    /**
     * Tests the ParentOwnerLimitation.
     *
     * @return void
     * @see \eZ\Publish\API\Repository\Values\User\Limitation\ParentOwnerLimitation
     * @expectedException \eZ\Publish\API\Repository\Exceptions\UnauthorizedException
     */
    public function testParentOwnerLimitationForbid()
    {
        $repository = $this->getRepository();

        $parentContentId = $this->generateId( 'content', 58 );
        /* BEGIN: Use Case */
        $user = $this->createUserVersion1();

        $roleService = $repository->getRoleService();

        $role = $roleService->loadRoleByIdentifier( 'Editor' );

        $policyCreate = $roleService->newPolicyCreateStruct( 'content', 'create' );
        $policyCreate->addLimitation(
            new ParentOwnerLimitation(
                array( 'limitationValues' => array( 1 ) )
            )
        );

        $role = $roleService->addPolicy( $role, $policyCreate );

        $roleService->assignRoleToUser( $role, $user );

        $repository->setCurrentUser( $user );

        $this->createWikiPageDraft();
        /* END: Use Case */
    }

    /**
     * Creates a published wiki page.
     *
     * @return \eZ\Publish\API\Repository\Values\Content\Content
     */
    protected function createWikiPage()
    {
        $repository = $this->getRepository();

        $contentService = $repository->getContentService();
        /* BEGIN: Inline */
        $draft = $this->createWikiPageDraft();

        $content = $contentService->publishVersion( $draft->versionInfo );
        /* END: Inline */

        return $content;
    }

    /**
     * Creates a fresh clean content draft.
     *
     * @return \eZ\Publish\API\Repository\Values\Content\Content
     */
    protected function createWikiPageDraft()
    {
        $repository = $this->getRepository();

        $parentLocationId = $this->generateId( 'location', 60 );
        $sectionId = $this->generateId( 'section', 1 );
        /* BEGIN: Inline */
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
        $draft = $contentService->createContent(
            $wikiPageCreate,
            array( $locationCreate )
        );
        /* END: Inline */

        return $draft;
    }

    /**
     * Marks the limitation integration tests skipped against memory stub
     *
     * Since the limitations integration tests rely on multiple factors which are
     * complicated and hard to mimic by the memory stub, these should only run
     * against the real core implementation.
     *
     * @return void
     */
    protected function setUp()
    {
        parent::setUp();

        if ( $this->getRepository() instanceof \eZ\Publish\API\Repository\Tests\Stubs\RepositoryStub )
        {
            $this->markTestSkipped(
                'Limitation integration tests cannot be run against memory stub.'
            );
        }
    }
}
