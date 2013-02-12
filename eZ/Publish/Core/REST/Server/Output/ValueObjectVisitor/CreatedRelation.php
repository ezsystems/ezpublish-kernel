<?php
/**
 * File containing the CreatedRelation ValueObjectVisitor class
 *
 * @copyright Copyright (C) 1999-2013 eZ Systems AS. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License v2
 * @version //autogentag//
 */

namespace eZ\Publish\Core\REST\Server\Output\ValueObjectVisitor;

use eZ\Publish\Core\REST\Common\Output\Generator;
use eZ\Publish\Core\REST\Common\Output\Visitor;

/**
 * CreatedRelation value object visitor
 */
class CreatedRelation extends RestRelation
{
    /**
     * Visit struct returned by controllers
     *
     * @param \eZ\Publish\Core\REST\Common\Output\Visitor $visitor
     * @param \eZ\Publish\Core\REST\Common\Output\Generator $generator
     * @param \eZ\Publish\Core\REST\Server\Values\CreatedRelation $data
     */
    public function visit( Visitor $visitor, Generator $generator, $data )
    {
        parent::visit( $visitor, $generator, $data->relation );
        $visitor->setHeader(
            'Location',
            $this->urlHandler->generate(
                'objectVersionRelation',
                array(
                    'object' => $data->relation->contentId,
                    'version' => $data->relation->versionNo,
                    'relation' => $data->relation->relation->id
                )
            )
        );
        $visitor->setStatus( 201 );
    }
}
