<?php
/**
 * File containing the Configured class.
 *
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 * @version //autogentag//
 */

namespace eZ\Publish\Core\MVC\Symfony\View\Provider;

use eZ\Publish\Core\MVC\Symfony\Matcher\MatcherFactoryInterface;
use eZ\Publish\Core\MVC\Symfony\View\ContentView;
use InvalidArgumentException;

/**
 * Base for View Providers.
 */
abstract class Configured
{
    /**
     * @var \eZ\Publish\Core\MVC\Symfony\Matcher\MatcherFactoryInterface
     */
    protected $matcherFactory;

    /**
     * @param \eZ\Publish\Core\MVC\Symfony\Matcher\MatcherFactoryInterface $matcherFactory
     */
    public function __construct( MatcherFactoryInterface $matcherFactory )
    {
        $this->matcherFactory = $matcherFactory;
    }

    /**
     * Builds a ContentView object from $viewConfig.
     *
     * @param array $viewConfig
     *
     * @throws \InvalidArgumentException
     *
     * @return ContentView
     */
    protected function buildContentView( array $viewConfig )
    {
        if ( !isset( $viewConfig['template'] ) )
        {
            throw new InvalidArgumentException( '$viewConfig must contain the template identifier in order to correctly generate the ContentView object' );
        }

        $view = new ContentView( $viewConfig['template'] );
        $view->setConfigHash( $viewConfig );
        return $view;
    }
}
