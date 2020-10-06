<?php

/**
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace eZ\Publish\API\Repository\Values\Content\Query\Aggregation\Field;

final class CountryTermAggregation extends AbstractFieldTermAggregation
{
    public const TYPE_NAME = 1;
    public const TYPE_IDC = 2;
    public const TYPE_ALPHA_2 = 4;
    public const TYPE_ALPHA_3 = 8;

    /** @var int */
    private $type;

    public function __construct(
        string $name,
        string $contentTypeIdentifier,
        string $fieldDefinitionIdentifier,
        int $type = self::TYPE_ALPHA_3
    ) {
        parent::__construct($name, $contentTypeIdentifier, $fieldDefinitionIdentifier);

        $this->type = $type;
    }

    public function getType(): int
    {
        return $this->type;
    }
}
