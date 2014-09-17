<?php
/**
 * File containing the FilterConfiguration class.
 *
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 * @version //autogentag//
 */

namespace eZ\Bundle\EzPublishCoreBundle\Imagine\Filter;

use eZ\Publish\API\Repository\Exceptions\InvalidVariationException;
use eZ\Publish\Core\MVC\ConfigResolverInterface;
use Liip\ImagineBundle\Imagine\Filter\FilterConfiguration as BaseFilterConfiguration;

class FilterConfiguration extends BaseFilterConfiguration
{
    /**
     * @var ConfigResolverInterface
     */
    private $configResolver;

    /**
     * @param ConfigResolverInterface $configResolver
     */
    public function setConfigResolver( ConfigResolverInterface $configResolver )
    {
        $this->configResolver = $configResolver;
    }

    public function get( $filter )
    {
        $configuredVariations = $this->configResolver->getParameter( 'image_variations' );
        if ( !isset( $configuredVariations[$filter] ) )
        {
            return parent::get( $filter );
        }

        $filterConfig = array(
            'cache' => 'ezpublish',
            'data_loader' => 'ezpublish',
            'filters' => $this->getVariationFilters( $filter, $configuredVariations )
        );
        return $filterConfig;
    }

    /**
     * Returns filters to be used for $variationName.
     *
     * Both variations configured in eZ (SiteAccess context) and LiipImagineBundle are used.
     * eZ variations always have precedence.
     * An eZ variation can have a "reference". In that case, reference's filters are prepended to the one set of $variationName.
     * Reference must be a valid variation name, configured in eZ or in LiipImagineBundle.
     *
     * @param string $variationName
     * @param array $configuredVariations Variations set in eZ.
     *
     * @throws InvalidVariationException
     *
     * @return array
     */
    private function getVariationFilters( $variationName, array $configuredVariations )
    {
        if ( !isset( $configuredVariations[$variationName]['filters'] ) && !isset( $this->filters[$variationName]['filters'] ) )
        {
            throw new InvalidVariationException( $variationName, 'image' );
        }

        // Check variations configured in eZ config first.
        if ( isset( $configuredVariations[$variationName] ) )
        {
            $filters = $configuredVariations[$variationName]['filters'];
            // If the variation has a reference, we recursively call this method to retrieve reference's filters
            // and add them on the top.
            if ( isset( $configuredVariations[$variationName]['reference'] ) && $configuredVariations[$variationName]['reference'] !== 'original' )
            {
                array_unshift(
                    $filters,
                    $this->getVariationFilters(
                        $configuredVariations[$variationName]['reference'],
                        $configuredVariations
                    )
                );
            }
        }
        // Falback to variations configured in LiipImagineBundle.
        else
        {
            $filters = $this->filters[$variationName]['filters'];
        }

        return $filters;
    }
}
