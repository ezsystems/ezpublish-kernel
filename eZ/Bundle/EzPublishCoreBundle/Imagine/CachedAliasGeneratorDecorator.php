<?php

namespace eZ\Bundle\EzPublishCoreBundle\Imagine;

use eZ\Publish\API\Repository\Values\Content\Field;
use eZ\Publish\API\Repository\Values\Content\VersionInfo;
use eZ\Publish\SPI\Variation\VariationHandler;
use Stash\Interfaces\PoolInterface;

class CachedAliasGeneratorDecorator implements VariationHandler
{
    /**
     * @var VariationHandler
     */
    private $aliasGenerator;

    /**
     * @var PoolInterface
     */
    private $cache;

    /**
     * AliasGeneratorDecorator constructor.
     * @param VariationHandler $aliasGenerator
     * @param PoolInterface $cache
     */
    public function __construct(VariationHandler $aliasGenerator, PoolInterface $cache)
    {
        $this->aliasGenerator = $aliasGenerator;
        $this->cache = $cache;
    }

    /**
     * @param Field $field
     * @param VersionInfo $versionInfo
     * @param string $variationName
     * @param array $parameters
     * @return \eZ\Publish\SPI\Variation\Values\Variation
     */
    public function getVariation(Field $field, VersionInfo $versionInfo, $variationName, array $parameters = array())
    {
        $item = $this->cache->getItem($this->getCacheKey($field, $versionInfo, $variationName));
        $image = $item->get();
        if ($item->isMiss()) {
            $item->set($image = $this->aliasGenerator->getVariation($field, $versionInfo, $variationName, $parameters))->save();
        }
        return $image;
    }

    /**
     * @param Field $field
     * @param VersionInfo $versionInfo
     * @param $variationName
     * @return string
     */
    private function getCacheKey(Field $field, VersionInfo $versionInfo, $variationName)
    {
        return 'variation/' . $versionInfo->getContentInfo()->id . '/' . $versionInfo->id . '/' . $field->id . '/' . $variationName;
    }
}