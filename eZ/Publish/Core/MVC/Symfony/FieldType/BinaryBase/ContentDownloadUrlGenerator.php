<?php

/**
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
namespace eZ\Publish\Core\MVC\Symfony\FieldType\BinaryBase;

use eZ\Publish\SPI\FieldType\BinaryBase\PathGenerator;
use eZ\Publish\SPI\Persistence\Content\Field;
use eZ\Publish\SPI\Persistence\Content\VersionInfo;
use Symfony\Component\Routing\RouterInterface;

class ContentDownloadUrlGenerator extends PathGenerator
{
    /** @var \Symfony\Component\Routing\RouterInterface */
    private $router;

    public function __construct(RouterInterface $router)
    {
        $this->router = $router;
    }

    public function getStoragePathForField(Field $field, VersionInfo $versionInfo)
    {
        return $this->router->generate(
            'ez_content_download_field_id',
            [
                'contentId' => $versionInfo->contentInfo->id,
                'fieldId' => $field->id,
            ]
        );
    }
}
