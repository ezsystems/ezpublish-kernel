<?php

/**
 * This file is part of the eZ Publish Kernel package.
 *
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
namespace eZ\Bundle\EzPublishCoreBundle\Imagine;

use eZ\Publish\API\Repository\Values\Content\Field;
use eZ\Publish\API\Repository\Values\Content\VersionInfo;
use eZ\Publish\Core\FieldType\Image\Value as ImageValue;
use eZ\Publish\Core\FieldType\Value;
use eZ\Publish\Core\IO\IOServiceInterface;
use eZ\Publish\SPI\Variation\VariationHandler;
use InvalidArgumentException;
use Liip\ImagineBundle\Exception\Imagine\Cache\Resolver\NotResolvableException;
use Liip\ImagineBundle\Imagine\Cache\Resolver\ResolverInterface;

class PlaceholderAliasGenerator implements VariationHandler
{
    /** @var \eZ\Publish\SPI\Variation\VariationHandler */
    private $aliasGenerator;

    /** @var \Liip\ImagineBundle\Imagine\Cache\Resolver\ResolverInterface */
    private $ioResolver;

    /** @var \eZ\Publish\Core\IO\IOServiceInterface */
    private $ioService;

    /** @var \eZ\Bundle\EzPublishCoreBundle\Imagine\PlaceholderProvider|null */
    private $placeholderProvider;

    /** @var array */
    private $placeholderOptions = [];

    /**
     * PlaceholderAliasGenerator constructor.
     *
     * @param \eZ\Publish\SPI\Variation\VariationHandler $aliasGenerator
     * @param \Liip\ImagineBundle\Imagine\Cache\Resolver\ResolverInterface $ioResolver
     * @param \eZ\Publish\Core\IO\IOServiceInterface $ioService
     */
    public function __construct(
        VariationHandler $aliasGenerator,
        ResolverInterface $ioResolver,
        IOServiceInterface $ioService)
    {
        $this->aliasGenerator = $aliasGenerator;
        $this->ioResolver = $ioResolver;
        $this->ioService = $ioService;
    }

    /**
     * {@inheritdoc}
     */
    public function getVariation(Field $field, VersionInfo $versionInfo, $variationName, array $parameters = [])
    {
        if ($this->placeholderProvider !== null) {
            /** @var \eZ\Publish\Core\FieldType\Image\Value $imageValue */
            $imageValue = $field->value;
            if (!$this->supportsValue($imageValue)) {
                throw new InvalidArgumentException("Value for field #{$field->id} ($field->fieldDefIdentifier) cannot be used for image placeholder generation.");
            }

            try {
                $this->ioResolver->resolve($imageValue->id, IORepositoryResolver::VARIATION_ORIGINAL);
            } catch (NotResolvableException $e) {
                // Generate placeholder for original image
                $binary = $this->ioService->newBinaryCreateStructFromLocalFile(
                    $this->placeholderProvider->getPlaceholder($imageValue, $this->placeholderOptions)
                );
                $binary->id = $imageValue->id;

                $this->ioService->createBinaryFile($binary);
            }
        }

        return $this->aliasGenerator->getVariation($field, $versionInfo, $variationName, $parameters);
    }

    public function setPlaceholderProvider(PlaceholderProvider $provider, array $options = [])
    {
        $this->placeholderProvider = $provider;
        $this->placeholderOptions = $options;
    }

    public function supportsValue(Value $value): bool
    {
        return $value instanceof ImageValue;
    }
}
