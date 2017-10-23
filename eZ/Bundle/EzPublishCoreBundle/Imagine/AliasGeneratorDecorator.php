<?php

namespace eZ\Bundle\EzPublishCoreBundle\Imagine;

use eZ\Publish\API\Repository\Values\Content\Field;
use eZ\Publish\API\Repository\Values\Content\VersionInfo;
use eZ\Publish\SPI\Variation\VariationHandler;
use Stash\Interfaces\PoolInterface;

class AliasGeneratorDecorator implements VariationHandler
{
    /**
     * @var VariationHandler
     */
    private $aliasGenerator;

    /**
     * @var PoolInterface
     */
    private $cache;

    public function __construct(VariationHandler $aliasGenerator, PoolInterface $cache)
    {
        $this->aliasGenerator = $aliasGenerator;
        $this->cache = $cache;
    }

    public function getVariation(Field $field, VersionInfo $versionInfo, $variationName, array $parameters = array())
    {
        $item = $this->cache->getItem($this->getCacheKey($field, $versionInfo, $variationName));
        if ($item->isMiss()) {
            $image = $this->aliasGenerator->getVariation($field, $versionInfo, $variationName, $parameters);
            $item->save($image);
        }
        return $item->get();
    }

    private function getCacheKey(Field $field, VersionInfo $versionInfo, $variationName)
    {
        return 'variation/' . $field->value . '/' . $field->id . '/' . $field->fieldDefIdentifier . '/' . $versionInfo->id . '/' . $variationName;
    }
}