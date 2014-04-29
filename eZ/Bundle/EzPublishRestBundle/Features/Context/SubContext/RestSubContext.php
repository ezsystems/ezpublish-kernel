<?php
/**
 * File containing the RestSubContext class.
 *
 * @copyright Copyright (C) 1999-2014 eZ Systems AS. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License v2
 * @version //autogentag//
 */

namespace eZ\Bundle\EzPublishRestBundle\Features\Context\SubContext;

use Behat\Behat\Context\BehatContext;
use eZ\Bundle\EzPublishRestBundle\Features\Context\RestClient\RestClient;

/**
 * RestSubContext
 *
 * This is the parent object of all REST sub contexts
 */
abstract class RestSubContext extends BehatContext
{
    /**
     * Rest client for all requests and responses
     *
     * @var eZ\Bundle\EzPublishRestBundle\Features\Context\RestClient\RestClient
     */
    public $restclient;

    public function __construct( RestClient $restclient )
    {
        $this->restclient = $restclient;
    }
}
