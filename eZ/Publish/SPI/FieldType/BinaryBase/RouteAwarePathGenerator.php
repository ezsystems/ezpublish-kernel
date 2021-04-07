<?php
declare(strict_types=1);

namespace eZ\Publish\SPI\FieldType\BinaryBase;

use eZ\Publish\SPI\Persistence\Content\Field;
use eZ\Publish\SPI\Persistence\Content\VersionInfo;

/**
 * A variant of PathGenerator that uses Symfony routes for generating URIs.
 */
interface RouteAwarePathGenerator extends PathGeneratorInterface
{
    public function getRoute(Field $field, VersionInfo $versionInfo): string;

    public function getParameters(Field $field, VersionInfo $versionInfo): array;

    public function generate(string $route, array $parameters = []): string;
}
