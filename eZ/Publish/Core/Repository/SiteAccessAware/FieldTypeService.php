<?php

/**
 * FieldTypeService class.
 *
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
namespace eZ\Publish\Core\Repository\SiteAccessAware;

use eZ\Publish\API\Repository\FieldTypeService as FieldTypeServiceInterface;

/**
 * FieldTypeService for SiteAccessAware layer.
 *
 * Currently does nothing but hand over calls to aggregated service.
 */
class FieldTypeService implements FieldTypeServiceInterface
{
    /** @var \eZ\Publish\API\Repository\FieldTypeService */
    protected $service;

    /**
     * Construct service object from aggregated service.
     *
     * @param \eZ\Publish\API\Repository\FieldTypeService $service
     */
    public function __construct(
        FieldTypeServiceInterface $service
    ) {
        $this->service = $service;
    }

    public function getFieldTypes()
    {
        return $this->service->getFieldTypes();
    }

    public function getFieldType($identifier)
    {
        return $this->service->getFieldType($identifier);
    }

    public function hasFieldType($identifier)
    {
        return $this->service->hasFieldType($identifier);
    }
}
