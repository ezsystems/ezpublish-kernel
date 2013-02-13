<?php
/**
 * File containing the PreBuildKernelEvent class.
 *
 * @copyright Copyright (C) 1999-2013 eZ Systems AS. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License v2
 * @version //autogentag//
 */

namespace eZ\Publish\Core\MVC\Legacy\Event;

use Symfony\Component\EventDispatcher\Event;
use Symfony\Component\HttpFoundation\Request;
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
