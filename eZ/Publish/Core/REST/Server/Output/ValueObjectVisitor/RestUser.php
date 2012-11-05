<?php
/**
 * File containing the RestUser ValueObjectVisitor class
 *
 * @copyright Copyright (C) 1999-2012 eZ Systems AS. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License v2
 * @version //autogentag//
 */

namespace eZ\Publish\Core\REST\Server\Output\ValueObjectVisitor;

use eZ\Publish\Core\REST\Common\Output\ValueObjectVisitor,
    eZ\Publish\Core\REST\Common\Output\Generator,
    eZ\Publish\Core\REST\Common\Output\Visitor,
    eZ\Publish\Core\REST\Server\Values\Version as VersionValue;

/**
 * RestUser value object visitor
 */
class RestUser extends ValueObjectVisitor
{
    /**
     * Visit struct returned by controllers
     *
     * @param \eZ\Publish\Core\REST\Common\Output\Visitor $visitor
     * @param \eZ\Publish\Core\REST\Common\Output\Generator $generator
     * @param \eZ\Publish\Core\REST\Server\Values\RestUser $data
     */
    public function visit( Visitor $visitor, Generator $generator, $data )
    {
        $contentInfo = $data->contentInfo;

        $generator->startObjectElement( 'User' );

        $generator->startAttribute( 'href', $this->urlHandler->generate( 'user', array( 'user' => $contentInfo->id ) ) );
        $generator->endAttribute( 'href' );

        $generator->startAttribute( 'id', $contentInfo->id );
        $generator->endAttribute( 'id' );

        $generator->startAttribute( 'remoteId', $contentInfo->remoteId );
        $generator->endAttribute( 'remoteId' );

        $visitor->setHeader( 'Content-Type', $generator->getMediaType( 'User' ) );
        $visitor->setHeader( 'Accept-Patch', $generator->getMediaType( 'UserUpdate' ) );

        $generator->startObjectElement( 'ContentType' );

        $contentType = $contentInfo->getContentType();
        $generator->startAttribute( 'href', $this->urlHandler->generate( 'type', array( 'type' => $contentType->id ) ) );
        $generator->endAttribute( 'href' );

        $generator->endObjectElement( 'ContentType' );

        $generator->startValueElement( 'name', $contentInfo->name );
        $generator->endValueElement( 'name' );

        $generator->startObjectElement( 'Versions', 'VersionList' );
        $generator->startAttribute( 'href', $this->urlHandler->generate( 'objectVersions', array( 'object' => $contentInfo->id ) ) );
        $generator->endAttribute( 'href' );
        $generator->endObjectElement( 'Versions' );

        $generator->startObjectElement( 'Section' );
        $generator->startAttribute( 'href', $this->urlHandler->generate( 'section', array( 'section' => $contentInfo->sectionId ) ) );
        $generator->endAttribute( 'href' );
        $generator->endObjectElement( 'Section' );

        $generator->startObjectElement( 'MainLocation', 'Location' );
        $generator->startAttribute(
            'href',
            $this->urlHandler->generate(
                'location',
                array(
                    'location' => rtrim( $data->mainLocation->pathString, '/' )
                )
            )
        );
        $generator->endAttribute( 'href' );
        $generator->endObjectElement( 'MainLocation' );

        $generator->startObjectElement( 'Locations', 'LocationList' );
        $generator->startAttribute( 'href', $this->urlHandler->generate( 'objectLocations', array( 'object' => $contentInfo->id ) ) );
        $generator->endAttribute( 'href' );
        $generator->endObjectElement( 'Locations' );

        $generator->startObjectElement( 'Owner', 'User' );
        $generator->startAttribute( 'href', $this->urlHandler->generate( 'user', array( 'user' => $contentInfo->ownerId ) ) );
        $generator->endAttribute( 'href' );
        $generator->endObjectElement( 'Owner' );

        $generator->startValueElement( 'publishDate', $contentInfo->publishedDate->format( 'c' ) );
        $generator->endValueElement( 'publishDate' );

        $generator->startValueElement( 'lastModificationDate', $contentInfo->modificationDate->format( 'c' ) );
        $generator->endValueElement( 'lastModificationDate' );

        $generator->startValueElement( 'mainLanguageCode', $contentInfo->mainLanguageCode );
        $generator->endValueElement( 'mainLanguageCode' );

        $generator->startValueElement( 'alwaysAvailable', $contentInfo->alwaysAvailable ? 'true' : 'false' );
        $generator->endValueElement( 'alwaysAvailable' );

        $visitor->visitValueObject( new VersionValue( $data->content ) );

        $generator->startValueElement( 'login', $data->content->login );
        $generator->endValueElement( 'login' );

        $generator->startValueElement( 'email', $data->content->email );
        $generator->endValueElement( 'email' );

        $generator->startValueElement( 'enabled', $data->content->enabled ? 'true' : 'false' );
        $generator->endValueElement( 'enabled' );

        $generator->startObjectElement( 'UserGroups', 'UserGroupList' );
        $generator->startAttribute(
            'href',
            $this->urlHandler->generate(
                'userGroups',
                array(
                    'user' => $contentInfo->id
                )
            )
        );
        $generator->endAttribute( 'href' );
        $generator->endObjectElement( 'UserGroups' );

        $generator->startObjectElement( 'Roles', 'RoleAssignmentList' );
        $generator->startAttribute(
            'href',
            $this->urlHandler->generate(
                'userRoleAssignments',
                array(
                    'user' => $contentInfo->id
                )
            )
        );
        $generator->endAttribute( 'href' );
        $generator->endObjectElement( 'Roles' );

        $generator->endObjectElement( 'User' );
    }
}
