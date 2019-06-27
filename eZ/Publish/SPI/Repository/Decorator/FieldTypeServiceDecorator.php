<?php

/**
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace eZ\Publish\SPI\Repository\Decorator;

use eZ\Publish\API\Repository\FieldTypeService;

abstract class FieldTypeServiceDecorator implements FieldTypeService
{
    /** @var \eZ\Publish\API\Repository\FieldTypeService */
    protected $innerService;

    public function __construct(FieldTypeService $innerService)
    {
        $this->innerService = $innerService;
    }

    public function getFieldTypes()
    {
        return $this->innerService->getFieldTypes();
    }

    public function getFieldType($identifier)
    {
        return $this->innerService->getFieldType($identifier);
    }

    public function hasFieldType($identifier)
    {
        return $this->innerService->hasFieldType($identifier);
    }
}
