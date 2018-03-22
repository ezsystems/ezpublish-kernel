<?php

/**
 * This file is part of the eZ Publish Kernel package.
 *
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
namespace eZ\Publish\Core\MVC\Symfony\FieldType\Relation;

use eZ\Publish\API\Repository\Values\Content\Field;
use eZ\Publish\Core\MVC\Symfony\FieldType\View\ParameterProviderInterface;

class ParameterProvider implements ParameterProviderInterface
{
    /** @var \eZ\Publish\Core\MVC\Symfony\FieldType\Relation\RelationService */
    private $relationService;

    /**
     * ParameterProvider constructor.
     *
     * @param \eZ\Publish\Core\MVC\Symfony\FieldType\Relation\RelationService $relationService
     */
    public function __construct(RelationService $relationService)
    {
        $this->relationService = $relationService;
    }

    public function getViewParameters(Field $field)
    {
        return [
            'relation_service' => $this->relationService,
        ];
    }
}
