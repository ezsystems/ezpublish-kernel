<?php

/**
 * File containing the ChainRouter class.
 *
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
namespace eZ\Publish\Core\MVC\Symfony\Routing;

use Symfony\Cmf\Component\Routing\ChainRouter as BaseChainRouter;

class ChainRouter extends BaseChainRouter
{
    public function generate($name, $parameters = [], $referenceType = self::ABSOLUTE_PATH)
    {
        if ($name instanceof RouteReference) {
            $parameters += $name->getParams();
            $name = $name->getRoute();
        }

        return parent::generate($name, $parameters, $referenceType);
    }
}
