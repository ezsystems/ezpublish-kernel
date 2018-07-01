<?php

/**
 * File containing the REST security Factory class.
 *
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
namespace eZ\Bundle\EzPublishCoreBundle\DependencyInjection\Security;

use Symfony\Bundle\SecurityBundle\DependencyInjection\Security\Factory\HttpBasicFactory as BaseHttpBasicFactory;

/**
 * Basic auth based authentication provider, working with eZ Publish repository.
 *
 * @deprecated Use http_basic in security.yml instead of ezpublish_http_basic
 */
class HttpBasicFactory extends BaseHttpBasicFactory
{
    public function getKey()
    {
        return 'ezpublish_http_basic';
    }
}
