<?php

/**
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
namespace eZ\Publish\Core\MVC\Symfony\FieldType\BinaryBase;

use eZ\Publish\SPI\FieldType\BinaryBase\PathGenerator;
use eZ\Publish\SPI\FieldType\BinaryBase\RouteAwarePathGenerator;
use eZ\Publish\SPI\Persistence\Content\Field;
use eZ\Publish\SPI\Persistence\Content\VersionInfo;
use Symfony\Component\Routing\RouterInterface;

class ContentDownloadUrlGenerator extends PathGenerator implements RouteAwarePathGenerator
{
    /** @var \Symfony\Component\Routing\RouterInterface */
    private $router;

    /** @var string */
    private $route = 'ez_content_download_field_id';

    public function __construct(RouterInterface $router)
    {
        $this->router = $router;
    }

    public function getStoragePathForField(Field $field, VersionInfo $versionInfo)
    {
        return $this->generate($this->route, $this->getParameters($field, $versionInfo));
    }

    public function generate(string $route, ?array $parameters = []): string
    {
        return $this->router->generate($route, $parameters ?? []);
    }

    public function getRoute(Field $field, VersionInfo $versionInfo): string
    {
        return $this->route;
    }

    public function getParameters(Field $field, VersionInfo $versionInfo): array
    {
        return [
            'contentId' => $versionInfo->contentInfo->id,
            'fieldId' => $field->id,
            'version' => $versionInfo->versionNo,
        ];
    }
}
