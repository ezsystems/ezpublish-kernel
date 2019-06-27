<?php

/**
 * File containing the FilterConfiguration class.
 *
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
namespace eZ\Bundle\EzPublishCoreBundle\Imagine\Filter;

use eZ\Publish\API\Repository\Exceptions\InvalidVariationException;
use eZ\Publish\Core\MVC\ConfigResolverInterface;
use Liip\ImagineBundle\Imagine\Filter\FilterConfiguration as BaseFilterConfiguration;

class FilterConfiguration extends BaseFilterConfiguration
{
    /** @var ConfigResolverInterface */
    private $configResolver;

    /**
     * @param ConfigResolverInterface $configResolver
     */
    public function setConfigResolver(ConfigResolverInterface $configResolver)
    {
        $this->configResolver = $configResolver;
    }

    public function get($filter)
    {
        $configuredVariations = $this->configResolver->getParameter('image_variations');
        if (!isset($configuredVariations[$filter])) {
            return parent::get($filter);
        }

        $filterConfig = isset($this->filters[$filter]) ? parent::get($filter) : [];

        return [
            'cache' => 'ezpublish',
            'data_loader' => 'ezpublish',
            'reference' => isset($configuredVariations[$filter]['reference']) ? $configuredVariations[$filter]['reference'] : null,
            'filters' => $this->getVariationFilters($filter, $configuredVariations),
            'post_processors' => $this->getVariationPostProcessors($filter, $configuredVariations),
        ] + $filterConfig;
    }

    public function all()
    {
        return $this->configResolver->getParameter('image_variations') + parent::all();
    }

    /**
     * Returns filters to be used for $variationName.
     *
     * Both variations configured in eZ (SiteAccess context) and LiipImagineBundle are used.
     * eZ variations always have precedence.
     *
     * @param string $variationName
     * @param array $configuredVariations Variations set in eZ.
     *
     * @throws InvalidVariationException
     *
     * @return array
     */
    private function getVariationFilters($variationName, array $configuredVariations)
    {
        if (!isset($configuredVariations[$variationName]['filters']) && !isset($this->filters[$variationName]['filters'])) {
            throw new InvalidVariationException($variationName, 'image');
        }

        // Check variations configured in eZ config first.
        if (isset($configuredVariations[$variationName]['filters'])) {
            $filters = $configuredVariations[$variationName]['filters'];
        } else {
            // Falback to variations configured in LiipImagineBundle.
            $filters = $this->filters[$variationName]['filters'];
        }

        return $filters;
    }

    /**
     * Returns post processors to be used for $variationName.
     *
     * Both variations configured in eZ and LiipImagineBundle are used.
     * eZ variations always have precedence.
     *
     * @param string $variationName
     * @param array $configuredVariations Variations set in eZ.
     *
     * @return array
     */
    private function getVariationPostProcessors($variationName, array $configuredVariations)
    {
        if (isset($configuredVariations[$variationName]['post_processors'])) {
            return $configuredVariations[$variationName]['post_processors'];
        } elseif (isset($this->filters[$variationName]['post_processors'])) {
            return $this->filters[$variationName]['post_processors'];
        }

        return [];
    }
}
