<?php
/**
 * File containing the FeatureContext class.
 *
 * This class contains general REST feature context for Behat.
 *
 * @copyright Copyright (C) 1999-2014 eZ Systems AS. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License v2
 * @version //autogentag//
 */

namespace eZ\Bundle\EzPublishRestBundle\Features\Context;

use EzSystems\BehatBundle\Features\Context\FeatureContext as BaseContext;
use eZ\Bundle\EzPublishRestBundle\Features\Context\RestClient\RestClient;

/**
 * Feature context.
 */
class FeatureContext extends BaseContext
{
    /**
     * Rest client for all requests and responses
     *
     * @var eZ\Bundle\EzPublishRestBundle\Features\Context\RestClientInterface
     */
    public $restclient;

    /**
     * @param array $parameters
     */
    public function __construct( array $parameters )
    {
        // set parent parameters
        parent::__construct( $parameters );

        // create a new REST Client
        $this->restclient = new RestClient();
    }
}
