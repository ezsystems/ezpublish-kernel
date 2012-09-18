<?php
/**
 * File containing the Version ValueObjectVisitor class
 *
 * @copyright Copyright (C) 1999-2012 eZ Systems AS. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License v2
 * @version //autogentag//
 */

namespace eZ\Publish\Core\REST\Server\Output\ValueObjectVisitor;

use eZ\Publish\Core\REST\Common\Output\ValueObjectVisitor;
use eZ\Publish\Core\REST\Common\Output\Generator;
use eZ\Publish\Core\REST\Common\Output\Visitor;

use eZ\Publish\API\Repository\Values\Content\VersionInfo;

/**
 * Version value object visitor
 */
class Version extends ValueObjectVisitor
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
        $generator->startObjectElement( 'Version' );
        $visitor->setHeader( 'Content-Type', $generator->getMediaType( 'Version' ) );

        if ( $data->versionInfo->status === VersionInfo::STATUS_DRAFT )
        {
            $visitor->setHeader( 'Accept-Patch', $generator->getMediaType( 'VersionUpdate' ) );
        }

        $generator->startAttribute(
            'href',
            $this->urlHandler->generate(
                'objectVersion',
                array(
                    'object' => $data->contentId,
                    'version' => $data->versionInfo->versionNo
                )
            )
        );
        $generator->endAttribute( 'href' );

        $visitor->visitValueObject( $data->versionInfo );

        $generator->endObjectElement( 'Version' );
    }
}
