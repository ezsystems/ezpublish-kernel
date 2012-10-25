<?php
/**
 * File containing the CreatedVersion ValueObjectVisitor class
 *
 * @copyright Copyright (C) 1999-2012 eZ Systems AS. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License v2
 * @version //autogentag//
 */

namespace eZ\Publish\Core\REST\Server\Output\ValueObjectVisitor;

use eZ\Publish\Core\REST\Common\Output\Generator;
use eZ\Publish\Core\REST\Common\Output\Visitor;

/**
 * CreatedVersion value object visitor
 */
class CreatedVersion extends Version
{
    /**
     * Visit struct returned by controllers
     *
     * @param \eZ\Publish\Core\REST\Common\Output\Visitor $visitor
     * @param \eZ\Publish\Core\REST\Common\Output\Generator $generator
     * @param \eZ\Publish\Core\REST\Server\Values\CreatedVersion $data
     */
    public function visit( Visitor $visitor, Generator $generator, $data )
    {
        parent::visit( $visitor, $generator, $data->version );
        $visitor->setHeader(
            'Location',
            $this->urlHandler->generate(
                'objectVersion',
                array(
                    'object' => $data->version->content->id,
                    'version' => $data->version->content->getVersionInfo()->versionNo
                )
            )
        );
        $visitor->setStatus( 201 );
    }
}
