<?php
/**
 * File containing the VersionList ValueObjectVisitor class
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
 * VersionList value object visitor
 */
class VersionList extends ValueObjectVisitor
{
    /**
     * Visit struct returned by controllers
     *
     * @param \eZ\Publish\Core\REST\Common\Output\Visitor $visitor
     * @param \eZ\Publish\Core\REST\Common\Output\Generator $generator
     * @param \eZ\Publish\Core\REST\Server\Values\VersionList $data
     */
    public function visit( Visitor $visitor, Generator $generator, $data )
    {
        $generator->startObjectElement( 'VersionList' );
        $visitor->setHeader( 'Content-Type', $generator->getMediaType( 'VersionList' ) );
        //@todo Needs refactoring, disabling certain headers should not be done this way
        $visitor->setHeader( 'Accept-Patch', false );

        $generator->startAttribute( 'href', $data->path );
        $generator->endAttribute( 'href' );

        $generator->startList( 'VersionItem' );
        foreach ( $data->versions as $version )
        {
            $generator->startHashElement( 'VersionItem' );

            $generator->startObjectElement( 'Version' );
            $generator->startAttribute(
                'href',
                $this->urlHandler->generate(
                    'objectVersion',
                    array(
                        'object' => $version->getContentInfo()->id,
                        'version' => $version->versionNo
                    )
                )
            );
            $generator->endAttribute( 'href' );
            $generator->endObjectElement( 'Version' );

            $visitor->visitValueObject( $version );

            $generator->endHashElement( 'VersionItem' );
        }
        $generator->endList( 'VersionItem' );

        $generator->endObjectElement( 'VersionList' );
    }
}
