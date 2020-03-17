<?php

/**
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace eZ\Publish\API\Repository\Values\Content\Query\Criterion\Value;

use eZ\Publish\API\Repository\Values\Content\Query\Criterion\Value;

/**
 * Struct that stores extra value information for a MapLocationDistance Criterion object.
 */
class MapLocationValue extends Value
{
    /**
     * Latitude of a geographical location.
     *
     * @var float
     */
    public $latitude;

    /**
     * Longitude of a geographical location.
     *
     * @var float
     */
    public $longitude;

    /**
     * @param float $latitude
     * @param float $longitude
     */
    public function __construct(float $latitude, float $longitude)
    {
        $this->latitude = $latitude;
        $this->longitude = $longitude;
    }
}
