<?php
/**
 * File containing the Base context class for sub contexts in RestBundle.
 *
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 * @version //autogentag//
 */

namespace eZ\Bundle\EzPublishRestBundle\Features\Context\SubContext;

use Behat\Behat\Context\BehatContext;
use eZ\Bundle\EzPublishRestBundle\Features\Context\RestClient\RestClient;

abstract class Base extends BehatContext
{
    /**
     * Rest client for all requests and responses
     *
     * @var \eZ\Bundle\EzPublishRestBundle\Features\Context\RestClient\RestClient
     */
    public $restClient;

    public function __construct( RestClient $restClient )
    {
        $this->restDriver = $restClient;
    }
}
