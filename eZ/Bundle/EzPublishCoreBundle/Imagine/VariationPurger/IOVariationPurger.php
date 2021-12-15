<?php

/**
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
namespace eZ\Bundle\EzPublishCoreBundle\Imagine\VariationPurger;

use eZ\Bundle\EzPublishCoreBundle\Imagine\Cache\AliasGeneratorDecorator;
use eZ\Publish\Core\IO\IOServiceInterface;
use eZ\Publish\SPI\Variation\VariationPurger;
use Ibexa\Core\Persistence\Cache\Identifier\CacheIdentifierGeneratorInterface;
use Symfony\Component\Cache\Adapter\TagAwareAdapterInterface;

/**
 * Purges image variations using the IOService.
 *
 * Depends on aliases being stored in their own folder, with each alias folder mirroring the original files structure.
 */
class IOVariationPurger implements VariationPurger
{
    /** @var \eZ\Publish\Core\IO\IOServiceInterface */
    private $io;

    /** @var \Symfony\Component\Cache\Adapter\TagAwareAdapterInterface */
    private $cache;

    /** @var \Ibexa\Core\Persistence\Cache\Identifier\CacheIdentifierGeneratorInterface */
    private $cacheIdentifierGenerator;

    /** @var \eZ\Bundle\EzPublishCoreBundle\Imagine\Cache\AliasGeneratorDecorator */
    private $aliasGeneratorDecorator;

    /** @var \Psr\Log\LoggerInterface */
    private $logger;

    public function __construct(
        IOServiceInterface $io,
        TagAwareAdapterInterface $cache,
        CacheIdentifierGeneratorInterface $cacheIdentifierGenerator,
        AliasGeneratorDecorator $aliasGeneratorDecorator
    ) {
        $this->io = $io;
        $this->cache = $cache;
        $this->cacheIdentifierGenerator = $cacheIdentifierGenerator;
        $this->aliasGeneratorDecorator = $aliasGeneratorDecorator;
    }

    /**
     * @param \Psr\Log\LoggerInterface $logger
     */
    public function setLogger($logger)
    {
        $this->logger = $logger;
    }

    public function purge(array $aliasNames)
    {
        $variationNameTag = $this->aliasGeneratorDecorator->getVariationNameTag();

        foreach ($aliasNames as $aliasName) {
            $directory = "_aliases/$aliasName";
            $this->io->deleteDirectory($directory);

            $variationTag = $this->cacheIdentifierGenerator->generateTag($variationNameTag, [$aliasName]);
            $this->cache->invalidateTags([$variationTag]);

            if (isset($this->logger)) {
                $this->logger->info("Purging alias directory $directory");
            }
        }
    }
}
