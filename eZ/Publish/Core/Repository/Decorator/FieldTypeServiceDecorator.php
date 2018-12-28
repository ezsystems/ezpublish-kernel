<?php

/**
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace eZ\Publish\Core\Repository\Decorator;

use eZ\Publish\API\Repository\FieldTypeService;

abstract class FieldTypeServiceDecorator implements FieldTypeService
{
    /**
     * @var \eZ\Publish\API\Repository\FieldTypeService
     */
    protected $service;

    /**
     * @param \eZ\Publish\API\Repository\FieldTypeService $service
     */
    public function __construct(FieldTypeService $service)
    {
        $this->service = $service;
    }

    /**
     * {@inheritdoc}
     */
    public function getFieldTypes()
    {
        return $this->service->getFieldTypes();
    }

    /**
     * {@inheritdoc}
     */
    public function getFieldType($identifier)
    {
        return $this->service->getFieldType($identifier);
    }

    /**
     * {@inheritdoc}
     */
    public function hasFieldType($identifier)
    {
        return $this->service->hasFieldType($identifier);
    }
}
