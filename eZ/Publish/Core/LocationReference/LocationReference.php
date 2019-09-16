<?php

declare(strict_types=1);

namespace eZ\Publish\Core\LocationReference;

use eZ\Publish\API\Repository\Exceptions\NotFoundException;
use eZ\Publish\API\Repository\Exceptions\UnauthorizedException;
use eZ\Publish\API\Repository\Values\Content\Location;

final class LocationReference
{
    /** @var \eZ\Publish\Core\LocationReference\LocationReferenceResolverInterface */
    private $resolver;

    /** @var string */
    private $reference;

    public function __construct(LocationReferenceResolverInterface $resolver, string $reference)
    {
        $this->reference = $reference;
        $this->resolver = $resolver;
    }

    public function getLocation(): Location
    {
        return $this->resolver->resolve($this->reference);
    }

    public function getLocationOrNull(): ?Location
    {
        try {
            return $this->resolver->resolve($this->reference);
        } catch (NotFoundException | UnauthorizedException $e) {
            return null;
        }
    }

    public function getLocationOrDefault(Location $default): Location
    {
        try {
            return $this->resolver->resolve($this->reference);
        } catch (NotFoundException | UnauthorizedException $e) {
            return $default;
        }
    }

    public function __invoke(): Location
    {
        return $this->getLocation();
    }

    public function __toString()
    {
        return $this->reference;
    }
}
