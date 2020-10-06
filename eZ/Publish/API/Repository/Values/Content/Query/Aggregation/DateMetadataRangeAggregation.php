<?php

/**
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace eZ\Publish\API\Repository\Values\Content\Query\Aggregation;

final class DateMetadataRangeAggregation extends AbstractRangeAggregation
{
    public const MODIFIED = 'modified';
    public const CREATED = 'created';
    public const PUBLISHED = 'published';

    /** @var string */
    private $type;

    public function __construct(string $name, string $type, array $ranges = [])
    {
        parent::__construct($name, $ranges);
        $this->type = $type;
    }

    public function getType(): string
    {
        return $this->type;
    }
}
