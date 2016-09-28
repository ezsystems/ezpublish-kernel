<?php

/**
 * File containing the URLAlias controller class.
 *
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 *
 * @version //autogentag//
 */
namespace eZ\Publish\Core\REST\Server\HttpCache\Controller;

use eZ\Publish\API\Repository\Values\Content\URLAlias as URLAliasValue;
use eZ\Publish\Core\REST\Server\Values\CachedValue;
use Symfony\Component\HttpFoundation\Request;

class URLAliasController extends AbstractController
{
    /**
     * @var \eZ\Publish\Core\REST\Server\Controller\URLAlias
     */
    protected $innerController;

    /**
     * @param \eZ\Publish\Core\REST\Server\Controller\Location $innerController
     */
    public function __construct($innerController)
    {
        $this->innerController = $innerController;
    }

    public function loadURLAlias($urlAliasId)
    {
        $urlAlias = $this->innerController->loadURLAlias($urlAliasId);

        return $urlAlias->type === URLAliasValue::LOCATION ? new CachedValue(
            $urlAlias,
            [
                'location' => $urlAlias->destination,
            ]
        ) : $urlAlias;
    }

    public function listGlobalURLAliases()
    {
        return $this->innerController->listGlobalURLAliases();
    }

    public function listLocationURLAliases($locationPath, Request $request)
    {
        $aliasesRefList = $this->innerController->listLocationURLAliases($locationPath, $request);
        $pathArray = explode('/', trim($locationPath, '/'));

        return new CachedValue(
            $aliasesRefList,
            [
                'location' => array_pop($pathArray),
            ]
        );
    }

    public function createURLAlias(Request $request)
    {
        return $this->innerController->createURLAlias($request);
    }

    public function deleteURLAlias($urlAliasId)
    {
        return $this->innerController->deleteURLAlias($urlAliasId);
    }
}
