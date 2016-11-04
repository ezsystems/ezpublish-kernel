<?php

/**
 * File containing the TemporaryRedirect class.
 *
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
namespace eZ\Publish\Core\REST\Server\Values;

use eZ\Publish\Core\REST\Common\Value as RestValue;

class TemporaryRedirect extends RestValue
{
    /**
     * Redirect URI.
     *
     * @var string
     */
    public $redirectUri;

    /**
     * Construct.
     *
     * @param string $redirectUri
     */
    public function __construct($redirectUri)
    {
        $this->redirectUri = $redirectUri;
    }
}
