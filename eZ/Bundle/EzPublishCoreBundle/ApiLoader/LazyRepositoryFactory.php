<?php
/**
 * File containing the LazyRepositoryFactory class.
 *
 * @copyright Copyright (C) 1999-2013 eZ Systems AS. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License v2
 * @version //autogentag//
 */

namespace eZ\Bundle\EzPublishCoreBundle\ApiLoader;

use eZ\Publish\API\Repository\Repository;
use Symfony\Component\DependencyInjection\ContainerInterface;

class LazyRepositoryFactory
{
    /**
     * @var \Symfony\Component\DependencyInjection\ContainerInterface
     */
    protected $container;

    public function __construct( ContainerInterface $container )
    {
        $this->container = $container;
    }

    /**
     * Returns a closure which returns ezpublish.api.repository when called.
     *
     * To be used when lazy loading is needed.
     *
     * @return \Closure
     */
    public function buildRepository()
    {
        $container = $this->container;
        return function () use ( $container )
        {
            static $repository;
            if ( !$repository instanceof Repository )
            {
                $repository = $container->get( 'ezpublish.api.repository' );
            }

            return $repository;
        };
    }
}
