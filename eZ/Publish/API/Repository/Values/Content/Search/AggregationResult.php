<?php

/**
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace eZ\Publish\API\Repository\Values\Content\Search;

use eZ\Publish\API\Repository\Values\ValueObject;

abstract class AggregationResult extends ValueObject
{
    /**
     * The name of the aggregation.
     *
     * @var string
     */
    private $name;

    public function __construct(string $name)
    {
        parent::__construct();

        $this->name = $name;
    }

    public function getName(): string
    {
        return $this->name;
    }
}
