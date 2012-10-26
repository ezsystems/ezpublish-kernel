<?php
/**
 * File containing the URLAlias ValueObjectVisitor class
 *
 * @copyright Copyright (C) 1999-2012 eZ Systems AS. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License v2
 * @version //autogentag//
 */

namespace eZ\Publish\Core\REST\Server\Output\ValueObjectVisitor;

use eZ\Publish\Core\REST\Common\Output\ValueObjectVisitor;
use eZ\Publish\Core\REST\Common\Output\Generator;
use eZ\Publish\Core\REST\Common\Output\Visitor;
use eZ\Publish\API\Repository\Values;

/**
 * URLAlias value object visitor
 */
class URLAlias extends ValueObjectVisitor
{
    /**
     * Visit struct returned by controllers
     *
     * @param \eZ\Publish\Core\REST\Common\Output\Visitor $visitor
     * @param \eZ\Publish\Core\REST\Common\Output\Generator $generator
     * @param \eZ\Publish\API\Repository\Values\Content\URLAlias $data
     */
    public function visit( Visitor $visitor, Generator $generator, $data )
    {
        $generator->startObjectElement( 'UrlAlias' );
        $visitor->setHeader( 'Content-Type', $generator->getMediaType( 'UrlAlias' ) );

        $generator->startAttribute(
            'href',
            $this->urlHandler->generate( 'urlAlias', array( 'urlalias' => $data->id ) )
        );
        $generator->endAttribute( 'href' );

        $generator->startAttribute( 'id', $data->id );
        $generator->endAttribute( 'id' );

        $generator->startAttribute( 'type', $this->serializeType( $data->type ) );
        $generator->endAttribute( 'type' );

        if ( $data->type === Values\Content\URLAlias::LOCATION )
        {
            $generator->startObjectElement( 'location', 'Location' );
            $generator->startAttribute(
                'href',
                $this->urlHandler->generate( 'location', array( 'location' => rtrim( $data->destination->pathString, '/' ) ) )
            );
            $generator->endAttribute( 'href' );
            $generator->endObjectElement( 'location' );
        }
        else
        {
            $generator->startValueElement( 'resource', $data->destination );
            $generator->endValueElement( 'resource' );
        }

        $generator->startValueElement( 'path', $data->path );
        $generator->endValueElement( 'path' );

        $generator->startValueElement( 'languageCodes', implode( ',', $data->languageCodes ) );
        $generator->endValueElement( 'languageCodes' );

        $generator->startValueElement( 'alwaysAvailable', $this->serializeBool( $data->alwaysAvailable ) );
        $generator->endValueElement( 'alwaysAvailable' );

        $generator->startValueElement( 'isHistory', $this->serializeBool( $data->isHistory ) );
        $generator->endValueElement( 'isHistory' );

        $generator->startValueElement( 'forward', $this->serializeBool( $data->forward ) );
        $generator->endValueElement( 'forward' );

        $generator->startValueElement( 'custom', $this->serializeBool( $data->isCustom ) );
        $generator->endValueElement( 'custom' );

        $generator->endObjectElement( 'UrlAlias' );
    }

    /**
     * Serializes the given $urlAliasType to a string representation
     *
     * @param int $urlAliasType
     * @return string
     */
    protected function serializeType( $urlAliasType )
    {
        switch ( $urlAliasType )
        {
            case Values\Content\URLAlias::LOCATION:
                return 'LOCATION';

            case Values\Content\URLAlias::RESOURCE:
                return 'RESOURCE';

            case Values\Content\URLAlias::VIRTUAL:
                return 'VIRTUAL';
        }

        throw new \RuntimeException( "Unknown URL alias type: '{$urlAliasType}'." );
    }
}
