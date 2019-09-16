<?php

namespace eZ\Publish\Core\LocationReference\ConfigResolver;

use eZ\Publish\Core\LocationReference\LocationReference;
use eZ\Publish\API\Repository\Values\Content\Location;

interface LocationConfigResolverInterface
{
    public function getLocation(string $name, ?string $namespace = null, ?string $scope = null): Location;

    public function getLocationReference(string $name, ?string $namespace = null, ?string $scope = null): LocationReference;
}
