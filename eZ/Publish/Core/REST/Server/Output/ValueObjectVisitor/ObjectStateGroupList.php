<?php
/**
 * File containing the ObjectStateGroupList visitor class
 *
 * @copyright Copyright (C) 1999-2012 eZ Systems AS. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License v2
 * @version //autogentag//
 */

namespace eZ\Publish\Core\REST\Server\Output\ValueObjectVisitor;

use eZ\Publish\Core\REST\Common\Output\ValueObjectVisitor;
use eZ\Publish\Core\REST\Common\Output\Generator;
use eZ\Publish\Core\REST\Common\Output\Visitor;

/**
 * ObjectStateGroupList value object visitor
 */
class ObjectStateGroupList extends ValueObjectVisitor
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
        $generator->startObjectElement( 'ObjectStateGroupList' );
        $visitor->setHeader( 'Content-Type', $generator->getMediaType( 'ObjectStateGroupList' ) );

        $generator->startAttribute( 'href', $this->urlHandler->generate( 'objectstategroups' ) );
        $generator->endAttribute( 'href' );

        $generator->startList( 'ObjectStateGroup' );

        foreach ( $data->groups as $group )
        {
            $generator->startObjectElement( 'ObjectStateGroup' );
            $generator->startAttribute(
                'href',
                $this->urlHandler->generate(
                    'objectstategroup',
                    array(
                        'objectstategroup' => $group->id
                    )
                )
            );
            $generator->endAttribute( 'href' );
            $generator->endObjectElement( 'ObjectStateGroup' );
        }

        $generator->endList( 'ObjectStateGroup' );

        $generator->endObjectElement( 'ObjectStateGroupList' );
    }
}
