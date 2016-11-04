<?php

/**
 * This file is part of the eZ Publish Kernel package.
 *
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
namespace eZ\Bundle\EzPublishDebugBundle\Collector;

use eZ\Publish\Core\MVC\Symfony\SiteAccess;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\DataCollector\DataCollector;

/**
 * Data collector showing siteaccess.
 */
class SiteAccessCollector extends DataCollector
{
    public function collect(Request $request, Response $response, \Exception $exception = null)
    {
        $this->data = [
            'siteAccess' => $request->attributes->get('siteaccess'),
        ];
    }

    public function getName()
    {
        return 'ezpublish.debug.siteaccess';
    }

    /**
     * Returns siteAccess.
     *
     * @return SiteAccess
     */
    public function getSiteAccess()
    {
        return $this->data['siteAccess'];
    }
}
