<?php
/**
 * File containing the EZP21798 class
 *
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 * @version //autogentag//
 */

namespace eZ\Publish\API\Repository\Tests\Regression;

use eZ\Publish\API\Repository\Tests\BaseTest;
use PHPUnit_Framework_TestCase;

/**
 * Test case for language issues in EZP-21798
 *
 * @issue EZP-21798
 */
class EZP21798Test extends BaseTest
{
    /**
     * Test for EZP-21798 - Role changes not working correctly on Postgres 9.1
     * 
     * This test will verify that anonymous users can access to a new section
     * that it's allowed to
     * 
     * @return void
     */
    public function testRoleChanges()
    {
        $repository = $this->getRepository();

        $sectionService = $repository->getSectionService();
        $contentService = $repository->getContentService();
        $contentTypeService = $repository->getContentTypeService();
        $locationService = $repository->getLocationService();
        $urlAliasService = $repository->getURLAliasService();
        $roleService = $repository->getRoleService();
        $userService = $repository->getUserService();

        $administratorUser = $userService->loadUser( 14 );
        $repository->setCurrentUser( $administratorUser );

        // Create a new section
        $sectionCreateStruct = $sectionService->newSectionCreateStruct();
        $sectionCreateStruct->name = 'Private';
        $sectionCreateStruct->identifier = 'private';
        $sectionService->createSection( $sectionCreateStruct );

        // Create a new folder
        $contentTypeFolder = $contentTypeService->loadContentTypeByIdentifier( 'folder' );
        $contentCreateStructFolder = $contentService->newContentCreateStruct( $contentTypeFolder, 'eng-GB' );

        $contentCreateStructFolder->setField( 'name', 'News' );

        $locationCreateStructFolder = $locationService->newLocationCreateStruct( 2 );
        $draftFolder = $contentService->createContent( $contentCreateStructFolder, array( $locationCreateStructFolder ) );
        $contentFolder = $contentService->publishVersion( $draftFolder->versionInfo );

        // Create a new article, inside the folder
        $contentTypeArticle = $contentTypeService->loadContentTypeByIdentifier( 'article' );
        $contentCreateStructArticle = $contentService->newContentCreateStruct( $contentTypeArticle, 'eng-GB' );

        $contentCreateStructArticle->setField( 'title', 'Article 1' );

        $contentCreateStructArticle->setField(
            'intro',
            '<?xml version="1.0" encoding="utf-8"?>
            <section xmlns:image="http://ez.no/namespaces/ezpublish3/image/"
            xmlns:xhtml="http://ez.no/namespaces/ezpublish3/xhtml/"
            xmlns:custom="http://ez.no/namespaces/ezpublish3/custom/">
            <paragraph xmlns:tmp="http://ez.no/namespaces/ezpublish3/temporary/">
            This is summary of Article Test
            </paragraph>
            </section>'
        );

        $newsLocation = $urlAliasService->lookup( '/News' );
        $locationNews = $locationService->loadLocation( $newsLocation->destination );

        $locationCreateStructArticle = $locationService->newLocationCreateStruct( $locationNews->id );
        $draftArticle = $contentService->createContent( $contentCreateStructArticle, array( $locationCreateStructArticle ) );
        $contentArticle = $contentService->publishVersion( $draftArticle->versionInfo );

        // Assign the article to the Private Section
        $section = $sectionService->loadSectionByIdentifier( 'private' );
        $sectionService->assignSection( $contentArticle->contentInfo, $section );

        $contentInfoarticle = $contentService->loadContentInfo( $contentArticle->contentInfo->id );

        // Allow anonymous user to Content/Read/Section( Standard, Private )
        $roles = $roleService->loadRoleByIdentifier( 'Anonymous' );

        $numPolicies = count( $roles->getPolicies() );
        $policies = $roles->getPolicies();
        $found = false;

        do
        {
            $numPolicies--;
            if ( $policies[$numPolicies]->module == "content" && $policies[$numPolicies]->function == "read" )
            {
                $found = true;
            }
        }
        while ( $numPolicies > 0 && !$found );

        $this->assertTrue( $found, "Couldn't find policy with module 'content' and function 'read'" );

        $newPolicy = $roleService->newPolicyUpdateStruct();
        $newLimitation = new \eZ\Publish\API\Repository\Values\User\Limitation\SectionLimitation;
        $section = $sectionService->loadSectionByIdentifier( 'private' );
        $newLimitation->limitationValues = array( 1, $section->id );
        $newPolicy->addLimitation( $newLimitation );

        $roleService->updatePolicy( $policies[$numPolicies], $newPolicy );

        // Access /Folder/Article
        $anonymousUser = $userService->loadAnonymousUser();
        $repository->setCurrentUser( $anonymousUser );

        $contentService->loadContent( $contentInfoarticle->id );
    }
}
