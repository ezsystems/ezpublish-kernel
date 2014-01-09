<?php
/**
 * File containing the REST security Factory class.
 *
 * @copyright Copyright (C) 1999-2013 eZ Systems AS. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License v2
 * @version //autogentag//
 */

namespace eZ\Bundle\EzPublishCoreBundle\DependencyInjection\Security;

use Symfony\Bundle\SecurityBundle\DependencyInjection\Security\Factory\HttpBasicFactory as BaseHttpBasicFactory;

/**
 * Basic auth based authentication provider, working with eZ Publish repository
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
