<?php
/**
 * File containing the ContentTreeLocationRemoteIdAware trait.
 *
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
namespace eZ\Publish\Core\MVC\Symfony\Routing;

use eZ\Publish\API\Repository\LocationService;
use eZ\Publish\Core\MVC\ConfigResolverInterface;

class RootLocationIdCalculator
{
    /**
     * @var int|string
     */
    protected $rootLocationId;

    /**
     * @var array
     */
    protected $rootLocationIdsBySiteaccess = [];

    /**
     * @var ConfigResolverInterface
     */
    protected $configResolver;

    /**
     * @var LocationService
     */
    protected $locationService;

    public function __construct(ConfigResolverInterface $configResolver, LocationService $locationService)
    {
        $this->configResolver = $configResolver;
        $this->locationService = $locationService;
    }

    /**
     * @return int|string
     */
    public function getRootLocationId()
    {
        if (null === $this->rootLocationId) {
            $this->rootLocationId = $this->getRootLocationIdFromConfig();
        }

        return $this->rootLocationId;
    }

    /**
     * @param $siteaccess
     * @return mixed
     */
    public function getRootLocationIdBySiteaccess($siteaccess)
    {
        if (!isset($this->rootLocationIdsBySiteaccess[$siteaccess])) {
            $this->rootLocationIdsBySiteaccess[$siteaccess] = $this->getRootLocationIdFromConfig($siteaccess);
        }

        return $this->rootLocationIdsBySiteaccess[$siteaccess];
    }

    /**
     * Returns content.tree_root.location_id for the current siteaccess or for the passed siteaccess.
     * In case content.tree_root.location_id is not present, it will calculate location id from the
     * content.tree_root.location_remote_id param.
     *
     * @param null $siteaccess
     * @return mixed
     */
    protected function getRootLocationIdFromConfig($siteaccess = null)
    {
        if ($this->configResolver->hasParameter('content.tree_root.location_id', null, $siteaccess)) {
            return $this->configResolver->getParameter('content.tree_root.location_id', null, $siteaccess);
        }

        $locationRemoteId = $this->configResolver->getParameter('content.tree_root.location_remote_id', null, $siteaccess);

        return $this->locationService->loadLocationByRemoteId($locationRemoteId)->id;
    }
}
