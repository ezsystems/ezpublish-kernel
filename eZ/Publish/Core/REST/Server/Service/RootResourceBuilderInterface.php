<?php

/**
 * File containing the RootResourceBuilderInterface class.
 *
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
namespace eZ\Publish\Core\REST\Server\Service;

interface RootResourceBuilderInterface
{
    /**
     * Build root resource.
     *
     * @return array|\eZ\Publish\Core\REST\Common\Values\Root
     */
    public function buildRootResource();
}
