<?php
/**
 * File containing Wrapped Service Container class
 *
 * @copyright Copyright (C) 1999-2014 eZ Systems AS. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License v2
 * @version //autogentag//
 */

namespace eZ\Publish\Core\Base;

use eZ\Publish\API\Container;
use Symfony\Component\DependencyInjection\ContainerBuilder;

/**
 *
 */
class WrappedServiceContainer implements Container
{
    /**
     * @var \Symfony\Component\DependencyInjection\ContainerBuilder
     */
    protected $innerContainer;

    public function __construct( ContainerBuilder $innerContainer )
    {
        $this->innerContainer = $innerContainer;
    }

    /**
     * Get Repository object
     *
     * Public API for
     *
     * @return \eZ\Publish\API\Repository\Repository
     */
    public function getRepository()
    {
        $this->checkCompile();
        return $this->innerContainer->get( "ezpublish.api.repository" );
    }

    /**
     * @return \Symfony\Component\DependencyInjection\ContainerBuilder
     */
    public function getInnerContainer()
    {
        return $this->innerContainer;
    }

    /**
     * @param $id
     *
     * @return object
     */
    public function get( $id )
    {
        $this->checkCompile();
        return $this->innerContainer->get( $id );
    }

    /**
     * @param $name
     *
     * @return mixed
     */
    public function getParameter( $name )
    {
        $this->checkCompile();
        return $this->innerContainer->getParameter( $name );
    }

    public function checkCompile()
    {
        if ( !$this->innerContainer->isFrozen() )
        {
            $this->innerContainer->compile();
        }
    }
}
