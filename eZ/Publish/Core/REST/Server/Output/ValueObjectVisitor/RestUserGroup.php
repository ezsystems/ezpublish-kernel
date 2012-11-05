<?php
/**
 * File containing the RestUserGroup ValueObjectVisitor class
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
 * RestUserGroup value object visitor
 */
class RestUserGroup extends ValueObjectVisitor
{
    /**
     * Visit struct returned by controllers
     *
     * @param \eZ\Publish\Core\REST\Common\Output\Visitor $visitor
     * @param \eZ\Publish\Core\REST\Common\Output\Generator $generator
     * @param \eZ\Publish\Core\REST\Server\Values\RestUserGroup $data
     */
    public function visit( Visitor $visitor, Generator $generator, $data )
    {
        $contentInfo = $data->contentInfo;
        $mainLocation = $data->mainLocation;
        $mainLocationPath = rtrim( $mainLocation->pathString, '/' );

        $generator->startObjectElement( 'UserGroup' );

        $generator->startAttribute( 'href', $this->urlHandler->generate( 'group', array( 'group' => $mainLocationPath ) ) );
        $generator->endAttribute( 'href' );

        $generator->startAttribute( 'id', $contentInfo->id );
        $generator->endAttribute( 'id' );

        $generator->startAttribute( 'remoteId', $contentInfo->remoteId );
        $generator->endAttribute( 'remoteId' );

        $visitor->setHeader( 'Content-Type', $generator->getMediaType( 'UserGroup' ) );
        $visitor->setHeader( 'Accept-Patch', $generator->getMediaType( 'UserGroupUpdate' ) );

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
                    'location' => $mainLocationPath
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

        $generator->startObjectElement( 'ParentUserGroup', 'UserGroup' );
        $generator->startAttribute(
            'href',
            $this->urlHandler->generate(
                'group',
                array(
                    'group' => '/' . implode( '/', array_slice( $mainLocation->path, 0, count( $mainLocation->path ) - 1 ) )
                )
            )
        );
        $generator->endAttribute( 'href' );
        $generator->endObjectElement( 'ParentUserGroup' );

        $generator->startObjectElement( 'Subgroups', 'UserGroupList' );
        $generator->startAttribute(
            'href',
            $this->urlHandler->generate(
                'groupSubgroups',
                array(
                    'group' => $mainLocationPath
                )
            )
        );
        $generator->endAttribute( 'href' );
        $generator->endObjectElement( 'Subgroups' );

        $generator->startObjectElement( 'Users', 'UserList' );
        $generator->startAttribute(
            'href',
            $this->urlHandler->generate(
                'groupUsers',
                array(
                    'group' => $mainLocationPath
                )
            )
        );
        $generator->endAttribute( 'href' );
        $generator->endObjectElement( 'Users' );

        $generator->startObjectElement( 'Roles', 'RoleAssignmentList' );
        $generator->startAttribute(
            'href',
            $this->urlHandler->generate(
                'groupRoleAssignments',
                array(
                    'group' => $mainLocationPath
                )
            )
        );
        $generator->endAttribute( 'href' );
        $generator->endObjectElement( 'Roles' );

        $generator->endObjectElement( 'UserGroup' );
    }
}
