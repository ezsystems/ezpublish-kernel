<?php
/**
 * File containing the PreBuildKernelWebHandlerEvent class.
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
 * This event is triggered right before the initialization of the legacy kernel web handler.
 * It allows to inject parameters into the legacy kernel through the parameter bag.
 */
class PreBuildKernelWebHandlerEvent extends Event
{
    /**
     * @var \Symfony\Component\HttpFoundation\Request
     */
    private $request;

    /**
     * Parameters that will be passed to the legacy kernel web handler
     *
     * @var \Symfony\Component\HttpFoundation\ParameterBag
     */
    private $parameters;

    public function __construct( ParameterBag $parameters, Request $request )
    {
        $this->parameters = $parameters;
        $this->request = $request;
    }

    /**
     * @return \Symfony\Component\HttpFoundation\Request
     */
    public function getRequest()
    {
        return $this->request;
    }

    /**
     * @return \Symfony\Component\HttpFoundation\ParameterBag
     */
    public function getParameters()
    {
        return $this->parameters;
    }
}
