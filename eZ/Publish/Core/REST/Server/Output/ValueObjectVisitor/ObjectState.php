<?php
/**
 * File containing the ObjectState ValueObjectVisitor class
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
 * ObjectState value object visitor
 */
class ObjectState extends ValueObjectVisitor
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
        $generator->startObjectElement( 'ObjectState' );
        $visitor->setHeader( 'Content-Type', $generator->getMediaType( 'ObjectState' ) );

        $generator->startAttribute(
            'href',
            $this->urlHandler->generate( 'objectstate', array( 'objectstategroup' => $data->groupId, 'objectstate' => $data->objectState->id ) )
        );
        $generator->endAttribute( 'href' );

        $generator->startValueElement( 'identifier', $data->objectState->identifier );
        $generator->endValueElement( 'identifier' );

        $generator->startValueElement( 'priority', $data->objectState->priority );
        $generator->endValueElement( 'priority' );

        $generator->startObjectElement( 'ObjectStateGroup' );

        $generator->startAttribute(
            'href',
            $this->urlHandler->generate( 'objectstategroup', array( 'objectstategroup' => $data->groupId ) )
        );
        $generator->endAttribute( 'href' );

        $generator->endObjectElement( 'ObjectStateGroup' );

        $generator->startValueElement( 'defaultLanguageCode', $data->objectState->defaultLanguageCode );
        $generator->endValueElement( 'defaultLanguageCode' );

        $generator->startValueElement( 'languageCodes', implode( ',', $data->objectState->languageCodes ) );
        $generator->endValueElement( 'languageCodes' );

        $this->visitNamesList( $generator, $data->objectState->getNames() );
        $this->visitDescriptionsList( $generator, $data->objectState->getDescriptions() );

        $generator->endObjectElement( 'ObjectState' );
    }
}
