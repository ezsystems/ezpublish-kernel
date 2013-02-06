<?php
/**
 * File containing the Root ValueObjectVisitor class
 *
 * @copyright Copyright (C) 1999-2013 eZ Systems AS. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License v2
 * @version //autogentag//
 */

namespace eZ\Publish\Core\REST\Server\Output\ValueObjectVisitor;

use eZ\Publish\Core\REST\Common\Output\ValueObjectVisitor;
use eZ\Publish\Core\REST\Common\Output\Generator;
use eZ\Publish\Core\REST\Common\Output\Visitor;

/**
 * Root value object visitor
 */
class Root extends ValueObjectVisitor
{
    /**
     * Visit struct returned by controllers
     *
     * @param \eZ\Publish\Core\REST\Common\Output\Visitor $visitor
     * @param \eZ\Publish\Core\REST\Common\Output\Generator $generator
     * @param \eZ\Publish\Core\REST\Common\Values\Root $data
     */
    public function visit( Visitor $visitor, Generator $generator, $data )
    {
        $generator->startObjectElement( 'Root' );
        $visitor->setHeader( 'Content-Type', $generator->getMediaType( 'Root' ) );

        // Uses hashElement instead of objectElement as a workaround to be able to have
        // an empty media-type, since there is no media type for list of content yet
        $generator->startHashElement( 'content' );
        $generator->startAttribute( 'media-type', '' );
        $generator->endAttribute( 'media-type' );
        $generator->startAttribute( 'href', $this->urlHandler->generate( 'objects' ) );
        $generator->endAttribute( 'href' );
        $generator->endHashElement( 'content' );

        $generator->startObjectElement( 'contentTypes', 'ContentTypeInfoList' );
        $generator->startAttribute( 'href', $this->urlHandler->generate( 'types' ) );
        $generator->endAttribute( 'href' );
        $generator->endObjectElement( 'contentTypes' );

        $generator->startObjectElement( 'users', 'UserRefList' );
        $generator->startAttribute( 'href', $this->urlHandler->generate( 'users' ) );
        $generator->endAttribute( 'href' );
        $generator->endObjectElement( 'users' );

        $generator->startObjectElement( 'roles', 'RoleList' );
        $generator->startAttribute( 'href', $this->urlHandler->generate( 'roles' ) );
        $generator->endAttribute( 'href' );
        $generator->endObjectElement( 'roles' );

        // @todo Load the locations of the following three items from settings
        $generator->startObjectElement( 'rootLocation', 'Location' );
        $generator->startAttribute( 'href', $this->urlHandler->generate( 'location', array( 'location' => '/1/2' ) ) );
        $generator->endAttribute( 'href' );
        $generator->endObjectElement( 'rootLocation' );

        $generator->startObjectElement( 'rootUserGroup', 'UserGroup' );
        $generator->startAttribute( 'href', $this->urlHandler->generate( 'group', array( 'group' => '/1/5' ) ) );
        $generator->endAttribute( 'href' );
        $generator->endObjectElement( 'rootUserGroup' );

        $generator->startObjectElement( 'rootMediaFolder', 'Location' );
        $generator->startAttribute( 'href', $this->urlHandler->generate( 'location', array( 'location' => '/1/43' ) ) );
        $generator->endAttribute( 'href' );
        $generator->endObjectElement( 'rootMediaFolder' );

        $generator->startObjectElement( 'trash', 'Trash' );
        $generator->startAttribute( 'href', $this->urlHandler->generate( 'trashItems' ) );
        $generator->endAttribute( 'href' );
        $generator->endObjectElement( 'trash' );

        $generator->startObjectElement( 'sections', 'SectionList' );
        $generator->startAttribute( 'href', $this->urlHandler->generate( 'sections' ) );
        $generator->endAttribute( 'href' );
        $generator->endObjectElement( 'sections' );

        $generator->startObjectElement( 'views', 'RefList' );
        $generator->startAttribute( 'href', $this->urlHandler->generate( 'views' ) );
        $generator->endAttribute( 'href' );
        $generator->endObjectElement( 'views' );

        // @todo object states?
        // @todo url aliases?
        // @todo url wildcards?
        // @todo content type groups?
        // @todo user policies?

        $generator->endObjectElement( 'Root' );
    }
}
