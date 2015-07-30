<?php

/**
 * File containing the Request class.
 *
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 *
 * @version //autogentag//
 */
namespace eZ\Publish\Core\REST\Server;

use Qafoo\RMF\Request\HTTP as RMFRequest;
use Qafoo\RMF\Request\PropertyHandler;

/**
 * Encapsulated RMF HTTP Request for REST server.
 *
 * @todo Remove when the REST client is refactored
 */
class Request extends RMFRequest
{
    /**
     * Construct request from a set of handlers.
     *
     * @param array $handlers
     *
     * @return \eZ\Publish\Core\REST\Server\Request
     */
    public function __construct(array $handlers = array())
    {
        $this->addHandler('body', new PropertyHandler\RawBody());

        $this->addHandler(
            'contentType',
            new PropertyHandler\Override(
                array(
                    new PropertyHandler\Server('CONTENT_TYPE'),
                    new PropertyHandler\Server('HTTP_CONTENT_TYPE'),
                )
            )
        );

        $this->addHandler(
            'method',
            new PropertyHandler\Override(
                array(
                    new PropertyHandler\Server('HTTP_X_HTTP_METHOD_OVERRIDE'),
                    new PropertyHandler\Server('REQUEST_METHOD'),
                )
            )
        );

        $this->addHandler('destination', new PropertyHandler\Server('HTTP_DESTINATION'));

        parent::__construct($handlers);
    }
}
