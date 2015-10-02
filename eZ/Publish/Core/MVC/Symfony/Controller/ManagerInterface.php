<?php

/**
 * File containing the ManagerInterface interface.
 *
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 *
 * @version //autogentag//
 */
namespace eZ\Publish\Core\MVC\Symfony\Controller;

use eZ\Publish\API\Repository\Values\ValueObject;
use eZ\Publish\Core\MVC\Symfony\View\View;

interface ManagerInterface
{
    /**
     * Returns a ControllerReference object corresponding to $valueObject and $viewType.
     *
     * @param ValueObject $valueObject
     * @param string $viewType
     *
     * @return \Symfony\Component\HttpKernel\Controller\ControllerReference|null
     */
    public function getControllerReference(View $view);
}
