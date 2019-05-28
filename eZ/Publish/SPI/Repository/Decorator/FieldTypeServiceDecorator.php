<?php

declare(strict_types=1);

namespace eZ\Publish\SPI\Repository\Decorator;

use eZ\Publish\API\Repository\FieldTypeService;

abstract class FieldTypeServiceDecorator implements FieldTypeService
{
    /** @var eZ\Publish\API\Repository\FieldTypeService */
    protected $innerService;

    /**
     * @param eZ\Publish\API\Repository\FieldTypeService
     */
    public function __construct(FieldTypeService $innerService)
    {
        $this->innerService = $innerService;
    }

    public function getFieldTypes()
    {
        $this->innerService->getFieldTypes();
    }

    public function getFieldType($identifier)
    {
        $this->innerService->getFieldType($identifier);
    }

    public function hasFieldType($identifier)
    {
        $this->innerService->hasFieldType($identifier);
    }
}
