<?php
/**
 * File containing the PreBuildKernelEvent class.
 *
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 * @version //autogentag//
 */

namespace eZ\Publish\Core\MVC\Legacy\Event;

use Symfony\Component\EventDispatcher\Event;
use Symfony\Component\HttpFoundation\ParameterBag;

/**
 * This event is triggered right before the initialization of the legacy kernel.
 * It allows to inject parameters into the legacy kernel through
 * the parameter bag.
 */
class PreBuildKernelEvent extends Event
{
    /**
     * Parameters that will be passed to the legacy kernel web handler
     *
     * @var \Symfony\Component\HttpFoundation\ParameterBag
     */
    private $parameters;

    public function __construct( ParameterBag $parameters )
    {
        $this->parameters = $parameters;
    }

    /**
     * @return \Symfony\Component\HttpFoundation\ParameterBag
     */
    public function getParameters()
    {
        return $this->parameters;
    }
}
