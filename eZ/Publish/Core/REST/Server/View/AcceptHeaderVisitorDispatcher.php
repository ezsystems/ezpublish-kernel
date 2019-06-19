<?php

/**
 * This file contains the AcceptHeaderVisitorDispatcher class.
 *
 * @version $Revision$
 *
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
namespace eZ\Publish\Core\REST\Server\View;

use Symfony\Component\HttpFoundation\Request;
use eZ\Publish\Core\REST\Common\Output\Visitor as OutputVisitor;
use Symfony\Component\HttpFoundation\Response;
use RuntimeException;

/**
 * Dispatcher for various visitors depending on the mime-type accept header.
 */
class AcceptHeaderVisitorDispatcher
{
    /**
     * Mapping of regular expressions matching the mime type accept headers to
     * view handlers.
     *
     * @var array
     */
    protected $mapping = [];

    /**
     * Adds view handler.
     *
     * @param string $regexp
     * @param \eZ\Publish\Core\REST\Common\Output\Visitor $visitor
     */
    public function addVisitor($regexp, OutputVisitor $visitor)
    {
        $this->mapping[$regexp] = $visitor;
    }

    /**
     * Dispatches a visitable result to the mapped visitor.
     *
     * @param \Symfony\Component\HttpFoundation\Request $request
     * @param mixed $result
     *
     * @throws RuntimeException
     *
     * @return Response
     */
    public function dispatch(Request $request, $result)
    {
        foreach ($request->getAcceptableContentTypes() as $mimeType) {
            /** @var \eZ\Publish\Core\REST\Common\Output\Visitor $visitor */
            foreach ($this->mapping as $regexp => $visitor) {
                if (preg_match($regexp, $mimeType)) {
                    return $visitor->visit($result);
                }
            }
        }

        throw new RuntimeException('No view mapping found.');
    }
}
