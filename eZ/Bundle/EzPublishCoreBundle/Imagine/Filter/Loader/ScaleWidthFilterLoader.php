<?php
/**
 * File containing the ScaleWidthFilterLoader class.
 *
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 * @version //autogentag//
 */

namespace eZ\Bundle\EzPublishCoreBundle\Imagine\Filter\Loader;

use Imagine\Image\ImageInterface;
use Imagine\Exception\InvalidArgumentException;
use Liip\ImagineBundle\Imagine\Filter\Loader\LoaderInterface;

/**
 * Filter loader for geometry/scalewidth filter.
 * Proxy to RelativeResizeFilterLoader
 */
class ScaleWidthFilterLoader implements LoaderInterface
{
    /**
     * @var LoaderInterface
     */
    private $relativeResizeLoader;

    public function __construct( LoaderInterface $relativeResizeLoader )
    {
        $this->relativeResizeLoader = $relativeResizeLoader;
    }

    public function load( ImageInterface $image, array $options = array() )
    {
        if ( empty( $options ) )
        {
            throw new InvalidArgumentException( 'Missing width option' );
        }

        return $this->relativeResizeLoader->load( $image, array( 'widen' => $options[0] ) );
    }
}
