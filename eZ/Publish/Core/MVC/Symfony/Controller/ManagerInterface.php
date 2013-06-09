<?php
/**
 * File containing the ManagerInterface interface.
 *
 * @copyright Copyright (C) 1999-2013 eZ Systems AS. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License v2
 * @version //autogentag//
 */

namespace eZ\Publish\Core\MVC\Symfony\Controller;

use eZ\Publish\API\Repository\Values\ValueObject;

interface ManagerInterface
{
    /**
     * Returns a ControllerReference object corresponding to $valueObject and $viewType
     *
     * @param ValueObject $valueObject
     * @param string $viewType
     *
     * @return \Symfony\Component\HttpKernel\Controller\ControllerReference|null
     */
    public function getControllerReference( ValueObject $valueObject, $viewType );
}
