<?php
/**
 * This file contains the AcceptHeaderVisitorDispatcher class
 *
 * @version $Revision$
 * @copyright Copyright (c) 2011 Qafoo GmbH
 * @license Dual licensed under the MIT and GPL licenses.
 */

namespace eZ\Publish\Core\REST\Server\View;

use Symfony\Component\HttpFoundation\Request;
use eZ\Publish\Core\REST\Common\Output\Visitor as OutputVisitor;
use Qafoo\RMF\View\NowViewFoundException;

/**
 * Dispatcher for various visitors depending on the mime-type accept header
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
     * Adds view handler
     *
     * @param string $regexp
     * @param \eZ\Publish\Core\REST\Common\Output\Visitor $visitor
     */
    public function addVisitor( $regexp, OutputVisitor $visitor )
    {
        $this->mapping[$regexp] = $visitor;
    }

    /**
     * Dispatches a visitable result to the mapped visitor
     *
     * @param \Symfony\Component\HttpFoundation\Request $request
     * @param mixed $result
     *
     * @return \eZ\Publish\Core\REST\Common\Message
     */
    public function dispatch( Request $request, $result )
    {
        foreach ( $request->getAcceptableContentTypes() as $mimeType )
        {
            /** @var \eZ\Publish\Core\REST\Common\Output\Visitor $visitor */
            foreach ( $this->mapping as $regexp => $visitor )
            {
                if ( preg_match( $regexp, $mimeType ) )
                {
                    return $visitor->visit( $result );
                }
            }
        }

        throw new NowViewFoundException( "No view mapping found." );
    }
}
