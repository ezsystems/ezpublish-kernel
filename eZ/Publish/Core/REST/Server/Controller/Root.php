<?php

/**
 * File containing the Root controller class.
 *
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 *
 * @version //autogentag//
 */
namespace eZ\Publish\Core\REST\Server\Controller;

use eZ\Publish\Core\REST\Common\Exceptions\NotFoundException;
use eZ\Publish\Core\REST\Common\Values;
use eZ\Publish\Core\REST\Server\Controller as RestController;

/**
 * Root controller.
 */
class Root extends RestController
{
    /**
     * @var Values\Resource[]
     */
    private $resources;

    public function __construct($restConfig)
    {
        $definition = $restConfig['resources'];

        foreach ($definition as $name => $resource) {
            $this->resources[] = new Values\Resource($name, $resource['mediaType'], $resource['href']);
        }
    }

    /**
     * List the root resources of the eZ Publish installation.
     *
     * @return \eZ\Publish\Core\REST\Common\Values\Root
     */
    public function loadRootResource()
    {
        return new Values\Root($this->resources);
    }

    /**
     * Catch-all for REST requests.
     *
     * @throws \eZ\Publish\Core\REST\Common\Exceptions\NotFoundException
     */
    public function catchAll()
    {
        throw new NotFoundException('No such route');
    }
}
