<?php

/**
 * File containing the ImagineAliasGenerator class.
 *
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
namespace eZ\Bundle\EzPublishCoreBundle\Imagine;

use eZ\Publish\API\Repository\Exceptions\InvalidVariationException;
use eZ\Publish\Core\MVC\Exception\SourceImageNotFoundException;
use eZ\Publish\API\Repository\Values\Content\Field;
use eZ\Publish\API\Repository\Values\Content\VersionInfo;
use eZ\Publish\SPI\Variation\Values\ImageVariation;
use eZ\Publish\SPI\Variation\VariationHandler;
use eZ\Publish\SPI\FieldType\Value;
use eZ\Publish\Core\FieldType\Image\Value as ImageValue;
use Imagine\Exception\RuntimeException;
use Liip\ImagineBundle\Binary\BinaryInterface;
use Liip\ImagineBundle\Binary\Loader\LoaderInterface;
use Liip\ImagineBundle\Exception\Binary\Loader\NotLoadableException;
use Liip\ImagineBundle\Exception\Imagine\Cache\Resolver\NotResolvableException;
use Liip\ImagineBundle\Imagine\Cache\Resolver\ResolverInterface;
use Liip\ImagineBundle\Imagine\Filter\FilterConfiguration;
use Liip\ImagineBundle\Imagine\Filter\FilterManager;
use Psr\Log\LoggerInterface;
use InvalidArgumentException;
use Psr\Log\NullLogger;
use SplFileInfo;

/**
 * Image alias generator using LiipImagineBundle API.
 * Doesn't use DataManager/CacheManager as it's directly bound to IO Repository for convenience.
 */
class AliasGenerator implements VariationHandler
{
    const ALIAS_ORIGINAL = 'original';

    /** @var \Psr\Log\LoggerInterface */
    private $logger;

    /**
     * Loader used to retrieve the original image.
     * DataManager is not used to remain independent from ImagineBundle configuration.
     *
     * @var \Liip\ImagineBundle\Binary\Loader\LoaderInterface
     */
    private $dataLoader;

    /** @var \Liip\ImagineBundle\Imagine\Filter\FilterManager */
    private $filterManager;

    /** @var FilterConfiguration */
    private $filterConfiguration;

    /** @var \Liip\ImagineBundle\Imagine\Cache\Resolver\ResolverInterface */
    private $ioResolver;

    public function __construct(
        LoaderInterface $dataLoader,
        FilterManager $filterManager,
        ResolverInterface $ioResolver,
        FilterConfiguration $filterConfiguration,
        LoggerInterface $logger = null
    ) {
        $this->dataLoader = $dataLoader;
        $this->filterManager = $filterManager;
        $this->ioResolver = $ioResolver;
        $this->filterConfiguration = $filterConfiguration;
        $this->logger = null !== $logger ? $logger : new NullLogger();
    }

    /**
     * {@inheritdoc}
     *
     * @throws \InvalidArgumentException If field value is not an instance of \eZ\Publish\Core\FieldType\Image\Value.
     * @throws \eZ\Publish\Core\MVC\Exception\SourceImageNotFoundException If source image cannot be found.
     * @throws \eZ\Publish\API\Repository\Exceptions\InvalidVariationException If a problem occurs with generated variation.
     */
    public function getVariation(Field $field, VersionInfo $versionInfo, $variationName, array $parameters = [])
    {
        /** @var \eZ\Publish\Core\FieldType\Image\Value $imageValue */
        $imageValue = $field->value;
        $fieldId = $field->id;
        $fieldDefIdentifier = $field->fieldDefIdentifier;
        if (!$this->supportsValue($imageValue)) {
            throw new InvalidArgumentException("Value for field #$fieldId ($fieldDefIdentifier) cannot be used for image alias generation.");
        }

        $originalPath = $imageValue->id;

        $variationWidth = $variationHeight = null;
        // Create the image alias only if it does not already exist.
        if ($variationName !== IORepositoryResolver::VARIATION_ORIGINAL && !$this->ioResolver->isStored($originalPath, $variationName)) {
            try {
                $originalBinary = $this->dataLoader->find($originalPath);
            } catch (NotLoadableException $e) {
                throw new SourceImageNotFoundException($originalPath, 0, $e);
            }

            $this->logger->debug("Generating '$variationName' variation on $originalPath, field #$fieldId ($fieldDefIdentifier)");

            $this->ioResolver->store(
                $this->applyFilter($originalBinary, $variationName),
                $originalPath,
                $variationName
            );
        } else {
            if ($variationName === IORepositoryResolver::VARIATION_ORIGINAL) {
                $variationWidth = $imageValue->width;
                $variationHeight = $imageValue->height;
            }
            $this->logger->debug("'$variationName' variation on $originalPath is already generated. Loading from cache.");
        }

        try {
            $aliasInfo = new SplFileInfo(
                $this->ioResolver->resolve($originalPath, $variationName)
            );
        } catch (NotResolvableException $e) {
            // If for some reason image alias cannot be resolved, throw the appropriate exception.
            throw new InvalidVariationException($variationName, 'image', 0, $e);
        } catch (RuntimeException $e) {
            throw new InvalidVariationException($variationName, 'image', 0, $e);
        }

        return new ImageVariation(
            [
                'name' => $variationName,
                'fileName' => $aliasInfo->getFilename(),
                'dirPath' => $aliasInfo->getPath(),
                'uri' => $aliasInfo->getPathname(),
                'imageId' => $imageValue->imageId,
                'width' => $variationWidth,
                'height' => $variationHeight,
            ]
        );
    }

    /**
     * Applies $variationName filters on $image.
     *
     * Both variations configured in eZ (SiteAccess context) and LiipImagineBundle are used.
     * An eZ variation may have a "reference".
     * In that case, reference's filters are applied first, recursively (a reference may also have another reference).
     * Reference must be a valid variation name, configured in eZ or in LiipImagineBundle.
     *
     * @param BinaryInterface $image
     * @param string $variationName
     *
     * @return \Liip\ImagineBundle\Binary\BinaryInterface
     */
    private function applyFilter(BinaryInterface $image, $variationName)
    {
        $filterConfig = $this->filterConfiguration->get($variationName);
        // If the variation has a reference, we recursively call this method to apply reference's filters.
        if (isset($filterConfig['reference']) && $filterConfig['reference'] !== IORepositoryResolver::VARIATION_ORIGINAL) {
            $image = $this->applyFilter($image, $filterConfig['reference']);
        }

        return $this->filterManager->applyFilter($image, $variationName);
    }

    public function supportsValue(Value $value)
    {
        return $value instanceof ImageValue;
    }
}
