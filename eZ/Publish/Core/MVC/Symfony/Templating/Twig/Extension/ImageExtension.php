<?php

/**
 * This file is part of the eZ Publish Kernel package.
 *
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
namespace eZ\Publish\Core\MVC\Symfony\Templating\Twig\Extension;

use eZ\Publish\API\Repository\Exceptions\InvalidVariationException;
use eZ\Publish\API\Repository\Values\Content\Field;
use eZ\Publish\API\Repository\Values\Content\VersionInfo;
use eZ\Publish\Core\FieldType\ImageAsset\AssetMapper;
use eZ\Publish\Core\MVC\Exception\SourceImageNotFoundException;
use eZ\Publish\SPI\Variation\VariationHandler;
use InvalidArgumentException;
use Twig_Extension;
use Twig_SimpleFunction;

class ImageExtension extends Twig_Extension
{
    /** @var VariationHandler */
    private $imageVariationService;

    /** @var \eZ\Publish\Core\FieldType\ImageAsset\AssetMapper */
    protected $assetMapper;

    public function __construct(VariationHandler $imageVariationService, AssetMapper $assetMapper)
    {
        $this->imageVariationService = $imageVariationService;
        $this->assetMapper = $assetMapper;
    }

    public function getName()
    {
        return 'ezpublish.image';
    }

    public function getFunctions()
    {
        return [
            new Twig_SimpleFunction(
                'ez_image_alias',
                [$this, 'getImageVariation'],
                ['is_safe' => ['html']]
            ),
            new Twig_SimpleFunction(
                'ez_image_asset_content_field_identifier',
                [$this, 'getImageAssetContentFieldIdentifier'],
                ['is_safe' => ['html']]
            ),
        ];
    }

    /**
     * Returns the image variation object for $field/$versionInfo.
     *
     * @param \eZ\Publish\API\Repository\Values\Content\Field $field
     * @param \eZ\Publish\API\Repository\Values\Content\VersionInfo $versionInfo
     * @param string $variationName
     *
     * @return \eZ\Publish\SPI\Variation\Values\Variation|null
     */
    public function getImageVariation(Field $field, VersionInfo $versionInfo, $variationName)
    {
        try {
            return $this->imageVariationService->getVariation($field, $versionInfo, $variationName);
        } catch (InvalidVariationException $e) {
            if (isset($this->logger)) {
                $this->logger->error("Couldn't get variation '{$variationName}' for image with id {$field->value->id}");
            }
        } catch (SourceImageNotFoundException $e) {
            if (isset($this->logger)) {
                $this->logger->error(
                    "Couldn't create variation '{$variationName}' for image with id {$field->value->id} because source image can't be found"
                );
            }
        } catch (InvalidArgumentException $e) {
            if (isset($this->logger)) {
                $this->logger->error(
                    "Couldn't create variation '{$variationName}' for image with id {$field->value->id} because an image could not be created from the given input"
                );
            }
        }
    }

    /**
     * Return identifier of the field used to store Image Asset value.
     *
     * Typically used to create generic view of the Image Asset field.
     *
     * @return string
     */
    public function getImageAssetContentFieldIdentifier(): string
    {
        return $this->assetMapper->getContentFieldIdentifier();
    }
}
