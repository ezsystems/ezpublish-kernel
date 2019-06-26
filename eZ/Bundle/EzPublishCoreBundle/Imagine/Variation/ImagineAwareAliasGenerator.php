<?php

/**
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
namespace eZ\Bundle\EzPublishCoreBundle\Imagine\Variation;

use eZ\Bundle\EzPublishCoreBundle\Imagine\IORepositoryResolver;
use eZ\Bundle\EzPublishCoreBundle\Imagine\VariationPathGenerator;
use eZ\Publish\API\Repository\Values\Content\Field;
use eZ\Publish\API\Repository\Values\Content\VersionInfo;
use eZ\Publish\Core\IO\IOServiceInterface;
use eZ\Publish\SPI\Variation\Values\ImageVariation;
use eZ\Publish\SPI\Variation\VariationHandler;
use Imagine\Image\ImagineInterface;

/**
 * Alias Generator Decorator which ensures (using Imagine if needed) that ImageVariation has proper
 * dimensions.
 */
class ImagineAwareAliasGenerator implements VariationHandler
{
    /** @var \eZ\Publish\SPI\Variation\VariationHandler */
    private $aliasGenerator;

    /** @var \eZ\Bundle\EzPublishCoreBundle\Imagine\VariationPathGenerator */
    private $variationPathGenerator;

    /** @var \eZ\Publish\Core\IO\IOServiceInterface */
    private $ioService;

    /** @var \eZ\Publish\Core\MVC\ConfigResolverInterface */
    private $configResolver;

    /** @var \Imagine\Image\ImagineInterface */
    private $imagine;

    public function __construct(
        VariationHandler $aliasGenerator,
        VariationPathGenerator $variationPathGenerator,
        IOServiceInterface $ioService,
        ImagineInterface $imagine
    ) {
        $this->aliasGenerator = $aliasGenerator;
        $this->variationPathGenerator = $variationPathGenerator;
        $this->ioService = $ioService;
        $this->imagine = $imagine;
    }

    /**
     * Returns a Variation object, ensuring proper image dimensions.
     *
     * {@inheritdoc}
     *
     * @throws \eZ\Publish\API\Repository\Exceptions\InvalidArgumentException
     * @throws \eZ\Publish\API\Repository\Exceptions\NotFoundException
     */
    public function getVariation(
        Field $field,
        VersionInfo $versionInfo,
        $variationName,
        array $parameters = []
    ) {
        /** @var \eZ\Publish\SPI\Variation\Values\ImageVariation $variation */
        $variation = $this->aliasGenerator->getVariation(
            $field,
            $versionInfo,
            $variationName,
            $parameters
        );

        if (null === $variation->width || null === $variation->height) {
            $variationBinaryFile = $this->getVariationBinaryFile($field->value->id, $variationName);
            $image = $this->imagine->load($this->ioService->getFileContents($variationBinaryFile));
            $dimensions = $image->getSize();

            return new ImageVariation(
                [
                    'name' => $variation->name,
                    'fileName' => $variation->fileName,
                    'dirPath' => $variation->dirPath,
                    'uri' => $variation->uri,
                    'imageId' => $variation->imageId,
                    'width' => $dimensions->getWidth(),
                    'height' => $dimensions->getHeight(),
                    'fileSize' => $variationBinaryFile->size,
                    'mimeType' => $this->ioService->getMimeType($variationBinaryFile->id),
                    'lastModified' => $variationBinaryFile->mtime,
                ]
            );
        }

        return $variation;
    }

    /**
     * Get image variation filesystem path.
     *
     * @param string $originalPath
     * @param string $variationName
     *
     * @return \eZ\Publish\Core\IO\Values\BinaryFile
     *
     * @throws \eZ\Publish\API\Repository\Exceptions\InvalidArgumentException
     * @throws \eZ\Publish\API\Repository\Exceptions\NotFoundException
     */
    private function getVariationBinaryFile($originalPath, $variationName)
    {
        if ($variationName !== IORepositoryResolver::VARIATION_ORIGINAL) {
            $variationPath = $this->variationPathGenerator->getVariationPath(
                $originalPath,
                $variationName
            );
        } else {
            $variationPath = $originalPath;
        }

        return $this->ioService->loadBinaryFile($variationPath);
    }
}
