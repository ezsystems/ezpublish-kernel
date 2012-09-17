<?php
/**
 * File containing the VersionList ValueObjectVisitor class
 *
 * @copyright Copyright (C) 1999-2012 eZ Systems AS. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License v2
 * @version //autogentag//
 */

namespace eZ\Publish\Core\REST\Server\Output\ValueObjectVisitor;

use eZ\Publish\Core\REST\Common\Output\ValueObjectVisitor;
use eZ\Publish\Core\REST\Common\Output\Generator;
use eZ\Publish\Core\REST\Common\Output\Visitor;
use eZ\Publish\Core\REST\Server\Values\Version;

/**
 * VersionList value object visitor
 */
class VersionList extends ValueObjectVisitor
{
    /**
     * Visit struct returned by controllers
     *
     * @param \eZ\Publish\Core\REST\Common\Output\Visitor $visitor
     * @param \eZ\Publish\Core\REST\Common\Output\Generator $generator
     * @param mixed $data
     */
    public function visit( Visitor $visitor, Generator $generator, $data )
    {
        $generator->startObjectElement( 'VersionList' );
        $visitor->setHeader( 'Content-Type', $generator->getMediaType( 'VersionList' ) );

        $generator->startAttribute(
            'href',
            $this->urlHandler->generate(
                'objectVersions',
                array( 'object' => $data->contentId )
            )
        );
        $generator->endAttribute( 'href' );

        $generator->startList( 'Version' );
        foreach ( $data->versions as $version )
        {
            $visitor->visitValueObject( new Version( $version, $data->contentId ) );
        }
        $generator->endList( 'Version' );

        $generator->endObjectElement( 'VersionList' );
    }
}
