<?php
/**
 * This file contains the AcceptHeaderVisitorDispatcher class
 *
 * @version $Revision$
 * @copyright Copyright (c) 2011 Qafoo GmbH
 * @license Dual licensed under the MIT and GPL licenses.
 */

namespace eZ\Publish\Core\REST\Server\View;
use eZ\Publish\Core\REST\Server\Request;
use eZ\Publish\Core\REST\Common\Output\Visitor;

/**
 * Dispatcher for various visitors depending on the mime-type accept header
 *
 * @version $Revision$
 */
class AcceptHeaderVisitorDispatcher
{
    /**
     * Mapping of regular expressions matching the mime type accept headers to
     * view handlers.
     *
     * @var array
     */
    protected $mapping = array();

    /**
     * Cosntruct from view handler mapping
     *
     * @param array $mapping
     * @return void
     */
    public function __construct( array $mapping )
    {
        foreach ( $mapping as $regexp => $visitor )
        {
            $this->addVisitor( $regexp, $visitor );
        }
    }

    /**
     * Add view handler
     *
     * @param string $regexp
     * @param Visitor $visitor
     * @return void
     */
    public function addVisitor( $regexp, Visitor $visitor )
    {
        $this->mapping[$regexp] = $visitor;
    }

    /**
     * Dispatches a visitable result to the mapped visitor
     *
     * @param Request $request
     * @param mixed $result
     * @return void
     */
    public function dispatch( Request $request, $result )
    {
        foreach ( $request->mimetype as $mimeType )
        {
            foreach ( $this->mapping as $regexp => $visitor )
            {
                if ( preg_match( $regexp, $mimeType['value'] ) )
                {
                    return $visitor->visit( $result );
                }
            }
        }

        throw new NowViewFoundException( "No view mapping found." );
    }
}

