<?php
/**
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
namespace eZ\Bundle\EzPublishCoreBundle\SiteAccess;

/**
 * Allows to filter SiteAccess configuration before it gets processed.
 */
interface SiteAccessConfigurationFilter
{
    /**
     * Receives the siteaccess configuration array and returns it.
     *
     * @param array $siteAccessConfiguration
     *        The SiteAccess configuration array before it gets normalized and processed.
     *        Keys: groups, list, default_siteaccess.
     *        Example:
     *        ```
     *        [
     *            'list' => ['site'],
     *            'groups' => ['site_group' => ['site']],
     *            'default_siteaccess' => 'site',
     *            'match' => ['URIElement' => 1]
     *        ]
     *        ```
     *
     * @return array The modified siteaccess configuration array
     */
    public function filter(array $siteAccessConfiguration);
}
