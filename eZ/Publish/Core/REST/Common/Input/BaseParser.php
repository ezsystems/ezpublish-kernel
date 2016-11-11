<?php

/**
 * File containing the Base parser class.
 *
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
namespace eZ\Publish\Core\REST\Common\Input;

use eZ\Publish\Core\REST\Common\RequestParser;

abstract class BaseParser extends Parser
{
    /**
     * URL handler.
     *
     * @var \eZ\Publish\Core\REST\Common\RequestParser
     */
    protected $requestParser;

    public function setRequestParser(RequestParser $requestParser)
    {
        $this->requestParser = $requestParser;
    }
}
