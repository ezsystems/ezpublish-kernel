<?php
/**
 * File containing the ImagineAliasGenerator class.
 *
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 * @version //autogentag//
 */

namespace eZ\Bundle\EzPublishCoreBundle\Imagine;

use eZ\Publish\API\Repository\Values\Content\Field;
use eZ\Publish\API\Repository\Values\Content\VersionInfo;
use eZ\Publish\SPI\Variation\Values\ImageVariation;
use eZ\Publish\SPI\Variation\VariationHandler;
use eZ\Publish\Core\FieldType\Value;
use eZ\Publish\Core\FieldType\Image\Value as ImageValue;
use Liip\ImagineBundle\Binary\Loader\LoaderInterface;
use Liip\ImagineBundle\Imagine\Cache\Resolver\ResolverInterface;
use Liip\ImagineBundle\Imagine\Filter\FilterManager;
use Psr\Log\LoggerInterface;
use InvalidArgumentException;
use SplFileInfo;

/**
 * Image alias generator using LiipImagineBundle API.
 * Doesn't use DataManager/CacheManager as it's directly bound to IO Repository for convenience.
 */
class AliasGenerator implements VariationHandler
{
    const ALIAS_ORIGINAL = 'original';

    /**
     * @var \Psr\Log\LoggerInterface
     */
    private $logger;

    /**
     * Loader used to retrieve the original image.
     * DataManager is not used to remain independent from ImagineBundle configuration.
     *
     * @var \Liip\ImagineBundle\Binary\Loader\LoaderInterface
     */
    private $dataLoader;

    /**
     * @var \Liip\ImagineBundle\Imagine\Filter\FilterManager
     */
    private $filterManager;

    /**
     * @var \Liip\ImagineBundle\Imagine\Cache\Resolver\ResolverInterface
     */
    private $ioResolver;

    public function __construct(
        LoaderInterface $dataLoader,
        FilterManager $filterManager,
        ResolverInterface $ioResolver,
        LoggerInterface $logger = null
    )
    {
        $this->dataLoader = $dataLoader;
        $this->filterManager = $filterManager;
        $this->ioResolver = $ioResolver;
        $this->logger = $logger;
    }

    /**
     * {@inheritDoc}
     *
     * @throws \InvalidArgumentException
     */
    public function getVariation( Field $field, VersionInfo $versionInfo, $variationName, array $parameters = array() )
    {
        /** @var \eZ\Publish\Core\FieldType\Image\Value $imageValue */
        $imageValue = $field->value;
        $fieldId = $field->id;
        $fieldDefIdentifier = $field->fieldDefIdentifier;
        if ( !$this->supportsValue( $imageValue ) )
        {
            throw new InvalidArgumentException( "Value for field #$fieldId ($fieldDefIdentifier) cannot be used for image alias generation." );
        }

        $originalPath = $imageValue->id;
        // Create the image alias only if it does not already exist.
        if ( !$this->ioResolver->isStored( $originalPath, $variationName ) )
        {
            if ( $this->logger )
                $this->logger->debug( "Generating '$variationName' variation on $originalPath, field #$fieldId ($fieldDefIdentifier)" );

            $this->ioResolver->store(
                $this->filterManager->applyFilter( $this->dataLoader->find( $originalPath ), $variationName ),
                $originalPath,
                $variationName
            );
        }
        else if ( $this->logger )
        {
            $this->logger->debug( "'$variationName' variation on $originalPath is already generated. Loading from cache." );
        }

        $aliasInfo = new SplFileInfo( $this->ioResolver->resolve( $originalPath, $variationName ) );
        return new ImageVariation(
            array(
                'name'         => $variationName,
                'fileName'     => $aliasInfo->getFilename(),
                'dirPath'      => $aliasInfo->getPath(),
                'uri'          => $aliasInfo->getPathname(),
                'imageId'      => $imageValue->imageId
            )
        );
    }

    public function supportsValue( Value $value )
    {
        return $value instanceof ImageValue;
    }
}
