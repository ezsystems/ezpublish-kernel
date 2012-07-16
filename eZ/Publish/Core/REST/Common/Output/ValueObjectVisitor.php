<?php
/**
 * File containing the ValueObjectVisitor class
 *
 * @copyright Copyright (C) 1999-2012 eZ Systems AS. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License v2
 * @version //autogentag//
 */

namespace eZ\Publish\API\REST\Common\Output;
use eZ\Publish\API\REST\Common\UrlHandler;

/**
 * Basic ValueObjectVisitor
 */
abstract class ValueObjectVisitor
{
    /**
     * URL handler for URL generation
     *
     * @var \eZ\Publish\API\REST\Common\UrlHandler
     */
    protected $urlHandler;

    /**
     * Construct from used URL handler
     *
     * @param \eZ\Publish\API\REST\Common\UrlHandler $urlHandler
     */
    public function __construct( UrlHandler $urlHandler )
    {
        $this->urlHandler = $urlHandler;
    }

    /**
     * Visit struct returned by controllers
     *
     * @param \eZ\Publish\API\REST\Common\Output\Visitor $visitor
     * @param \eZ\Publish\API\REST\Common\Output\Generator $generator
     * @param mixed $data
     */
    abstract public function visit( Visitor $visitor, Generator $generator, $data );
}

