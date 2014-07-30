<?php
/**
 * File containing the ContentTypeGroup context class for RestBundle.
 *
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 * @version //autogentag//
 */

namespace eZ\Bundle\EzPublishRestBundle\Features\Context\SubContext;

use EzSystems\BehatBundle\Sentence\ContentTypeGroup as ContentTypeGroupSentences;
use Behat\Behat\Context\Step;
use Behat\Gherkin\Node\TableNode;
use PHPUnit_Framework_Assert as Assertion;

class ContentTypeGroup extends Base implements ContentTypeGroupSentences
{
    /**
     * When I create a Content Type Group with identifier "<identifier>"
     */
    public function iCreateContentTypeGroup( $identifier )
    {
        return array(
            new Step\When( 'I create a "POST" request to "/content/typegroups"' ),
            new Step\When( 'I add "content-type" header with "Input" for "ContentTypeGroup"' ),
            new Step\When( 'I add "accept" header for a "ContentTypeGroup"' ),
            new Step\When( 'I make a "ContentTypeGroupCreateStruct" object' ),
            new Step\When( 'I add "' . $identifier . '" value to "identifier" field' ),
            new Step\When( 'I send the request' )
        );
    }

    /**
     * When I read Content Type Group with identifier "<identifier>"
     */
    public function iReadContentTypeGroup( $identifier )
    {
        return array(
            new Step\When( 'I create a "GET" request to "/content/typegroups?identifier=' . $identifier . '"' ),
            new Step\When( 'I add "accept" header with a "ContentTypeGroup"' ),
            new Step\When( 'I send the request' )
        );
    }

    /**
     * When I read Content Type Group with id "<id>"
     */
    public function iReadContentTypeGroupWithId( $id )
    {
        return array(
            new Step\When( 'I create a "GET" request to "/content/typegroups/' . $id . '"' ),
            new Step\When( 'I add "accept" header with a "ContentTypeGroup"' ),
            new Step\When( 'I send the request' )
        );
    }

    /**
     * When I read Content Type Groups list
     */
    public function iReadContentTypeGroupsList()
    {
        return array(
            new Step\When( 'I create a "GET" request to "/content/typegroups"' ),
            new Step\When( 'I add "accept" header to "List" a "ContentTypeGroup"' ),
            new Step\When( 'I send the request' )
        );
    }

    /**
     * When I update Content Type Group with identifier "<actualIdentifier>" to "<newIdentifier>"
     */
    public function iUpdateContentTypeGroupIdentifier( $actualIdentifier, $newIdentifier )
    {
        // load the ContentTypeGroup to be updated
        $contentTypeGroup = $this
            ->getMainContext()
            ->getRepository()
            ->getContentTypeService()
            ->loadContentTypeGroupByIdentifier( $actualIdentifier );

        return array(
            new Step\When( 'I create a "PATCH" request to "/content/typegroups/' . $contentTypeGroup->id . '"' ),
            new Step\When( 'I add "content-type" header with "Input" for "ContentTypeGroup"' ),
            new Step\When( 'I add "accept" header for a "ContentTypeGroup"' ),
            new Step\When( 'I make a "ContentTypeGroupUpdateStruct" object' ),
            new Step\When( 'I add "' . $newIdentifier . '" value to "identifier" field' ),
            new Step\When( 'I send the request' )
        );
    }

    /**
     * When I delete Content Type Group with identifier "<identifier>"
     */
    public function iDeleteContentTypeGroup( $identifier )
    {
        $contentTypeGroup = $this
            ->getMainContext()
            ->getRepository()
            ->getContentTypeService()
            ->loadContentTypeGroupByIdentifier( $identifier );

        return array(
            new Step\When( 'I create a "DELETE" request to "/content/typegroups/' . $contentTypeGroup->id . '"' ),
            new Step\When( 'I send the request' )
        );
    }

    /**
     * Then I see (?:a |)Content Type Group with identifier "<identifier>"
     * Then response contains (?:a |)Content Type Group with identifier "<identifier>"
     */
    public function iSeeContentTypeGroup( $identifier )
    {
        return array(
            new Step\Then( 'I see "content-type" header with a "ContentTypeGroup"' ),
            new Step\Then( 'I see response body with "eZ\\Publish\\Core\\REST\\Client\\Values\\ContentType\\ContentTypeGroup" object' ),
            new Step\Then( 'I see response object field "identifier" with "' . $identifier . '" value' )
        );
    }

    /**
     * Then I see the following Content Type Groups:
     */
    public function iSeeTheFollowingContentTypeGroups( TableNode $table )
    {
        // get groups
        $groups = $this->getMainContext()->getSubContext( 'Common' )->convertTableToArrayOfData( $table );

        // verify if the expects objects are in the list
        foreach ( $this->getMainContext()->getResponseObject() as $ContentTypeGroup )
        {
            $found = array_search( $ContentTypeGroup->identifier, $groups );
            if ( $found !== false )
            {
                unset( $groups[$found] );
            }
        }

        // verify if all the expected groups were found
        Assertion::assertEmpty(
            $groups,
            "Expected to find all groups but couldn't find: " . print_r( $groups, true )
        );
    }
}
