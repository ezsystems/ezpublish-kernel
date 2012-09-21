<?php
/**
 * File containing the ValueObjectVisitor class
 *
 * @copyright Copyright (C) 1999-2012 eZ Systems AS. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License v2
 * @version //autogentag//
 */

namespace eZ\Publish\Core\REST\Common\Output;
use eZ\Publish\Core\REST\Common\UrlHandler;
use eZ\Publish\API\Repository\Values\User\Limitation;

/**
 * Basic ValueObjectVisitor
 */
abstract class ValueObjectVisitor
{
    /**
     * URL handler for URL generation
     *
     * @var \eZ\Publish\Core\REST\Common\UrlHandler
     */
    protected $urlHandler;

    /**
     * Construct from used URL handler
     *
     * @param \eZ\Publish\Core\REST\Common\UrlHandler $urlHandler
     */
    public function __construct( UrlHandler $urlHandler )
    {
        $this->urlHandler = $urlHandler;
    }

    /**
     * Visit struct returned by controllers
     *
     * @param \eZ\Publish\Core\REST\Common\Output\Visitor $visitor
     * @param \eZ\Publish\Core\REST\Common\Output\Generator $generator
     * @param mixed $data
     */
    abstract public function visit( Visitor $visitor, Generator $generator, $data );

    /**
     * Returns a string representation for the given $boolValue
     *
     * @param bool $boolValue
     * @return string
     */
    protected function serializeBool( $boolValue )
    {
        return ( $boolValue ? 'true' : 'false' );
    }

    /**
     * Visits the given list of $names
     *
     * @param \eZ\Publish\Core\REST\Common\Output\Generator $generator
     * @param array $names
     */
    protected function visitNamesList( Generator $generator, array $names )
    {
        $this->visitTranslatedList( $generator, $names, 'names' );
    }

    /**
     * Visits the given list of $descriptions
     *
     * @param \eZ\Publish\Core\REST\Common\Output\Generator $generator
     * @param array $descriptions
     */
    protected function visitDescriptionsList( Generator $generator, array $descriptions )
    {
        $this->visitTranslatedList( $generator, $descriptions, 'descriptions' );
    }

    /**
     * Visits a list of translated elements
     *
     * @param \eZ\Publish\Core\REST\Common\Output\Generator $generator
     * @param array $translatedElements
     * @param mixed $listName
     */
    protected function visitTranslatedList( Generator $generator, array $translatedElements, $listName )
    {
        $generator->startHashElement( $listName );
        $generator->startList( 'value' );
        foreach ( $translatedElements as $languageCode => $element )
        {
            $generator->startValueElement( 'value', $element, array( 'languageCode' => $languageCode ) );
            $generator->endValueElement( 'value' );
        }
        $generator->endList( 'value' );
        $generator->endHashElement( $listName );
    }

    /**
     * Visits a limitation
     *
     * @param \eZ\Publish\Core\REST\Common\Output\Generator $generator
     * @param \eZ\Publish\API\Repository\Values\User\Limitation $limitation
     */
    protected function visitLimitation( Generator $generator, Limitation $limitation )
    {
        $generator->startHashElement( 'limitation' );

        $generator->startAttribute( 'identifier', $limitation->getIdentifier() );
        $generator->endAttribute( 'identifier' );

        $generator->startHashElement( 'values' );
        $generator->startList( 'ref' );

        foreach ( $limitation->limitationValues as $limitationValue )
        {
            $generator->startObjectElement( 'ref' );
            $generator->startAttribute( 'href', $limitationValue );
            $generator->endAttribute( 'href' );
            $generator->endObjectElement( 'ref' );
        }

        $generator->endList( 'ref' );
        $generator->endHashElement( 'values' );

        $generator->endHashElement( 'limitation' );
    }
}
