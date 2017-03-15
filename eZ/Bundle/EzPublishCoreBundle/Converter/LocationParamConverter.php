<?php

/**
 * This file is part of the eZ Publish Kernel package.
 *
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
namespace eZ\Bundle\EzPublishCoreBundle\Converter;

use eZ\Publish\API\Repository\LocationService;

class LocationParamConverter extends RepositoryParamConverter
{
    /** @var \eZ\Publish\API\Repository\LocationService */
    private $locationService;

    public function __construct(LocationService $locationService)
    {
        $this->locationService = $locationService;
    }

    protected function getSupportedClass()
    {
        return 'eZ\Publish\API\Repository\Values\Content\Location';
    }

    protected function getPropertyName()
    {
        return 'locationId';
    }

    protected function loadValueObject($id)
    {
        return $this->locationService->loadLocation($id);
    }
}
